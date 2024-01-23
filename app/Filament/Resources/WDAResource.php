<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\Accounting\WDA;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\WDAResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\WDAResource\RelationManagers;

class WDAResource extends Resource
{
    protected static ?string $model = WDA::class;
    protected static ?string $modelLabel = 'WDA';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        TextInput::make('wda_number')
                            ->disabled(),
                        DatePicker::make('date')
                            ->rules(['date'])
                            ->required()
                            ->disabled()
                            ->placeholder('Date'),
                        TextInput::make('job_description')
                            ->required()
                            ->disabled()
                            ->maxLength(255),
                        FileUpload::make('document')
                            ->disk('s3')
                            ->disabled()
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->label('Document'),
                        Select::make('building_id')
                            ->relationship('building', 'name')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Building Name'),
                        Select::make('contract_id')
                            ->relationship('contract', 'contract_type')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Contract Type'),
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Service'),
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->preload()
                            ->disabled()
                            ->disabled()
                            ->searchable()
                            ->label('vendor Name'),
                        Select::make('status')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->disabled(function (WDA $record) {
                                return $record->status != 'pending';
                            })
                            ->searchable()
                            ->live(),
                        TextInput::make('remarks')
                            ->rules(['max:255'])
                            ->visible(function (callable $get) {
                                if ($get('status') == 'rejected') {
                                    return true;
                                }
                                return false;
                            })
                            ->disabled(function (WDA $record) {
                                return $record->status != 'pending';
                            })
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->default('NA')
                    ->label('Date'),
                TextColumn::make('service.name')
                    ->default('NA')
                    ->label('Service'),
                TextColumn::make('status')
                    ->default('NA')
                    ->label('Status'),
                TextColumn::make('building.name')
                    ->label('Building'),
                TextColumn::make('contract.contract_type')
                    ->label('Contract Type'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'pending' => 'Pending',
                    ])
                    ->searchable(),
                SelectFilter::make('building_id')
                    ->relationship('building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', auth()->user()->owner_association_id);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListWDAS::route('/'),
            'create' => Pages\CreateWDA::route('/create'),
            'edit' => Pages\EditWDA::route('/{record}/edit'),
        ];
    }
}
