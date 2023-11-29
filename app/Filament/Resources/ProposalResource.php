<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\BuildingVendor;
use Filament\Resources\Resource;
use App\Models\Accounting\Tender;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\Proposal;
use Filament\Tables\Actions\Action;
use App\Models\Vendor\ServiceVendor;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProposalResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProposalResource\RelationManagers;

class ProposalResource extends Resource
{
    protected static ?string $model = Proposal::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Oam';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('tender_id')
                    ->relationship('tender', 'created_by')
                    ->preload()
                    ->searchable()
                    ->label('Tender Created ID'),
                TextInput::make('amount')
                    ->label('Amount'),
                Hidden::make('submitted_by')
                    ->default(1),
                Hidden::make('submitted_on')
                    ->default(now()),
                FileUpload::make('document')
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->label('Document'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amount')->label('Amount'),
                TextColumn::make('submittedBy.name')->searchable()->label('Vendor Name'),
                TextColumn::make('submitted_on')->label('Submitted On'),
                TextColumn::make('status')->default('NA')->label('Status'),
            ])
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Action::make('Approve')
                    ->visible(fn($record) => $record->status == null)
                    ->button()
                    ->form([
                        TextInput::make('remarks')
                            ->rules(['max:255'])
                            ->required(),
                    ])
                    ->fillForm(fn(Proposal $record): array => [
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (Proposal $record, array $data): void {
                        $record->status = 'approved';
                        $record->remarks = $data['remarks'];
                        $record->status_updated_by = auth()->user()->id;
                        $record->status_updated_on = now();
                        $venderId = $record->submittedBy;
                        $tenderId = Proposal::where('submitted_by', $venderId->id)->where('status', null)->first()->tender_id;
                        $budgetId = Tender::where('id', $tenderId)->first()->budget_id;
                        $servicesIdArray = DB::table('budget_items')->where('budget_id', $budgetId)->pluck('service_id')->toArray();
                        $buildingId = DB::table('budgets')->where('id', $budgetId)->pluck('building_id');
                        $budget_from = DB::table('budgets')->where('id', $budgetId)->pluck('budget_from')[0];
                        $budget_to = DB::table('budgets')->where('id', $budgetId)->pluck('budget_to')[0];
                        foreach ($servicesIdArray as $serviceId) {
                            ServiceVendor::create([
                                'service_id' => $serviceId,
                                'vendor_id' => $venderId->id,
                                'active' => true,
                                'building_id' => $buildingId[0],

                            ]);
                        }
                        BuildingVendor::create([
                            'vendor_id' => $venderId->id,
                            'active' => true,
                            'building_id' => $buildingId[0],
                            'start_date' => $budget_from,
                            'end_date' => $budget_to,
                        ]);
                        $record->save();
                    })
                    ->slideOver(),
                Action::make('Reject')
                    ->visible(fn($record) => $record->status == null)
                    ->button()
                    ->form([
                        TextInput::make('remarks')
                            ->rules(['max:255'])
                            ->required(),
                    ])
                    ->fillForm(fn(Proposal $record): array => [
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (Proposal $record, array $data): void {
                        $record->status = 'rejected';
                        $record->remarks = $data['remarks'];
                        $record->status_updated_by = auth()->user()->id;
                        $record->status_updated_on = now();
                        $record->save();
                    })
                    ->slideOver()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProposals::route('/'),
            'create' => Pages\CreateProposal::route('/create'),
            //'edit' => Pages\EditProposal::route('/{record}/edit'),
        ];
    }
}
