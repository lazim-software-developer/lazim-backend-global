<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Asset;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\BuildingVendor;
use App\Models\Master\Service;
use App\Models\Vendor\Contract;
use App\Models\TechnicianAssets;
use App\Models\TechnicianVendor;
use Filament\Resources\Resource;
use App\Models\Accounting\Budget;
use App\Models\Accounting\Tender;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\Proposal;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Log;
use App\Models\Vendor\ServiceVendor;
use function Laravel\Prompts\select;
use App\Models\Accounting\Budgetitem;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
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
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2,
            ])->schema([
                TextInput::make('amount')
                    ->label('Amount')
                    ->prefix('AED')
                    ->disabled(),
                Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->label('Vendor Name')
                    ->disabled(),
                TextInput::make('submitted_on')
                    ->disabled()
                    ->default(now()),
                ViewField::make('Budget amount')
                        ->view('forms.components.budgetamount'),
                FileUpload::make('document')
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->label('Document')
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 2,
                    ]),
                Select::make('status')
                    ->options([
                        'approved' => 'Approve',
                        'rejected' => 'Reject',
                    ])
                    ->disabled(function (Proposal $record) {
                        return $record->status != null;
                    })
                    ->required()
                    ->searchable()
                    ->live(),
                TextInput::make('remarks')
                    ->rules(['max:55'])
                    ->visible(function (callable $get) {
                        if ($get('status') == 'rejected') {
                            return true;
                        }
                        return false;
                    })
                    ->disabled(function (Proposal $record) {
                        return $record->status != null;
                    })
                    ->required(),
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amount')->searchable()->label('Amount'),
                ViewColumn::make('Budget amount')->view('tables.columns.budgetamount')->alignCenter(),
                TextColumn::make('submittedBy.name')->searchable()->label('Vendor Name'),
                TextColumn::make('submitted_on')->label('Submitted On'),
                TextColumn::make('status')->default('NA')->searchable()->label('Status'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('vendor_id')
                    ->relationship('vendor', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', auth()->user()->owner_association_id)->where('status', 'approved');
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->label('Vendor')
                    ->placeholder('Select Vendor'),
                Filter::make('Building')
                    ->form([
                        Select::make('building')
                            ->searchable()
                            ->options(function () {
                                $oaId = auth()->user()->owner_association_id;
                                return Building::where('owner_association_id', $oaId)
                                    ->pluck('name', 'id');
                            })
                            ->preload()
                            ->placeholder('Select Building'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['building']) && $data['building'], // Ensure 'building' is set and not null
                            function (Builder $query) use ($data) {
                                return $query->whereHas('tender', function ($query) use ($data) {
                                    // Adjust the relationship and field names according to your actual database structure
                                    $query->where('building_id', $data['building']);
                                });
                            }
                        );
                    }),
                Filter::make('Service')
                    ->form([
                        Select::make('service_id')
                            ->searchable()
                            ->options(Service::where('type', 'vendor_service')->pluck('name', 'id'))
                            ->preload()
                            ->placeholder('Select Service')
                            ->label('Service')
                            ->optionsLimit(300),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['service_id']) && $data['service_id'],
                            function (Builder $query) use ($data) {
                                $query->whereHas('tender', function ($query) use ($data) {
                                    $query->where('service_id', $data['service_id']);
                                });
                            }
                        );
                    }),
                Filter::make('Year')
                    ->form([
                        Select::make('year')
                            ->options(function () {
                                $years = range(date('Y'), date('Y') - 5);
                                return array_combine($years, $years);
                            })
                            ->searchable()
                            ->placeholder('Select Year')
                            ->label('Year'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['year']) && $data['year'],
                            function (Builder $query) use ($data) {
                                $query->whereYear('submitted_on', $data['year']);
                            }
                        );
                    }),
            ],layout: FiltersLayout::AboveContent)->filtersFormColumns(4)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
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
            'edit' => Pages\EditProposal::route('/{record}/edit'),
        ];
    }
}
