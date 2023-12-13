<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Complaintsenquiry;
use App\Models\Building\Complaint;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ComplaintsenquiryResource\Pages;
use App\Filament\Resources\ComplaintsenquiryResource\RelationManagers;

class ComplaintsenquiryResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Enquirie';

    protected static ?string $navigationGroup = 'Happiness center';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2
                ])
                    ->schema([
                        Hidden::make('complaintable_type')
                            ->default('App\Models\Building\FlatTenant'),
                        Hidden::make('complaintable_id')
                            ->default(1),
                        Hidden::make('owner_association_id')
                            ->default(auth()->user()->owner_association_id),
                        Select::make('building_id')
                            ->rules(['exists:buildings,id'])
                            ->relationship('building', 'name')
                            ->reactive()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('user_id')
                            ->relationship('user', 'id')
                            ->options(function () {
                                $tenants = DB::table('flat_tenants')->pluck('tenant_id');
                                // dd($tenants);
                                return DB::table('users')
                                    ->whereIn('users.id', $tenants)
                                    ->select('users.id', 'users.first_name')
                                    ->pluck('users.first_name', 'users.id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('User'),
                        TextInput::make('complaint')
                            ->placeholder('Enquiry'),
                        TextInput::make('complaint_details')
                            ->placeholder('Complaint Details'),
                        Hidden::make('status')
                            ->default('open'),
                        Hidden::make('complaint_type')
                            ->default('enquiries'),
                        Repeater::make('media')
                            ->relationship()
                            ->schema([
                                FileUpload::make('url')
                                    ->disk('s3')
                                    ->directory('dev')
                                    ->maxSize(2048)
                                    ->openable(true)
                                    ->downloadable(true)
                                    ->label('File'),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('complaint')
                    ->toggleable()
                    ->default('NA')
                    ->searchable()
                    ->label('Enquiry'),
                TextColumn::make('complaint_details')
                    ->toggleable()
                    ->default('NA')
                    ->searchable()
                    ->label('Complaint Details'),
                TextColumn::make('status')
                    ->toggleable()
                    ->searchable()
                    ->limit(50),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name')
                    ->searchable()
                    ->label('Building')
                    ->preload()
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Action::make('Update Status')
                    ->visible(fn ($record) => $record->status === 'open')
                    ->button()
                    ->form([
                        Select::make('status')
                            ->options([
                                'open'   => 'Open',
                                'closed' => 'Closed',
                            ])
                            ->searchable()
                            ->live(),
                        TextInput::make('remarks')
                            ->rules(['max:255'])
                            ->visible(function (callable $get) {
                                if ($get('status') == 'closed') {
                                    return true;
                                }
                                return false;
                            })
                            ->required(),
                    ])
                    ->fillForm(fn (Complaint $record): array => [
                        'status' => $record->status,
                        'remarks' => $record->remarks,
                    ])
                    ->action(function (Complaint $record, array $data): void {
                        if ($data['status'] == 'closed') {
                            $record->status = $data['status'];
                            $record->remarks = $data['remarks'];
                            $record->save();
                        } else {
                            $record->status = $data['status'];
                            $record->save();
                        }
                    })
                    ->slideOver()
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
            'index' => Pages\ListComplaintsenquiries::route('/'),
            'view' => Pages\ViewComplaintsenquiry::route('/{record}'),
        ];
    }
}
