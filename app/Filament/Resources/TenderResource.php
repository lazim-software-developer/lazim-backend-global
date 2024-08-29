<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Accounting\Tender;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TenderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TenderResource\RelationManagers;
use App\Filament\Resources\TenderResource\RelationManagers\ProposalsRelationManager;
use Filament\Forms\Components\TextInput;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class TenderResource extends Resource
{
    protected static ?string $model = Tender::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Oam';
    protected static ?string $modelLabel = 'Tenders';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('created_by')
                    ->default(auth()->user()->id),
                Hidden::make('owner_association_id')
                    ->default(auth()->user()?->owner_association_id),
                Select::make('building_id')
                    ->relationship('building', 'name')
                    ->preload()
                    ->searchable()
                    ->disabled()
                    ->label('Building Name'),
                Select::make('budget_id')
                    ->relationship('budget', 'budget_period')
                    ->preload()
                    ->searchable()
                    ->disabled()
                    ->label('Budget Period'),
                Select::make('service_id')
                    ->relationship('service','name')
                    ->preload()
                    ->searchable()
                    ->disabled()
                    ->label('Service'),
                TextInput::make('tender_type')
                    ->placeholder('NA')
                    ->disabled()
                    ->label('Contract Type'),
                DatePicker::make('date')
                    ->rules(['date'])
                    ->required()
                    ->disabled()
                    ->placeholder('Date'),
                DatePicker::make('end_date')
                    ->rules(['date'])
                    ->required()
                    ->disabled()
                    ->placeholder('End Date'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->label('Building Name'),
                TextColumn::make('budget.budget_period')
                    ->searchable()
                    ->default('NA')
                    ->label('Budget Period'),
                TextColumn::make('service.name')
                    ->searchable()
                    ->default('NA')
                    ->label('Service'),
                TextColumn::make('tender_type')
                    ->searchable()
                    ->default('NA')
                    ->label('Contract Type'),
                TextColumn::make('date')
                    ->date(),
                TextColumn::make('end_date')
                    ->date(),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
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
            ProposalsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenders::route('/'),
            'view' => Pages\ViewTender::route('/{record}'),
            'edit' => Pages\EditTender::route('/{record}/edit'),
        ];
    }
}
