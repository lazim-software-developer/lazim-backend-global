<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Accounting\Budget;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BudgetResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BudgetResource\RelationManagers;
use App\Filament\Resources\BudgetResource\RelationManagers\BudgetitemsRelationManager;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

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
                Select::make('building_id')
                    ->relationship('building', 'name')
                    ->preload()
                    ->searchable()
                    ->label('Building Name'),
                TextInput::make('budget_period'),
                DatePicker::make('budget_from')
                    ->rules(['date'])
                    ->required()
                    ->placeholder('Budget From'),
                DatePicker::make('budget_to')
                    ->rules(['date'])
                    ->required()
                    ->placeholder('Budget To'),
                Repeater::make('tenders')
                    ->relationship()
                    ->schema([
                        Select::make('building_id')
                            ->relationship('building', 'name')
                            ->preload()
                            ->searchable()
                            ->label('Building Name'),
                        FileUpload::make('document')
                            ->disk('s3')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->label('Document'),
                        DatePicker::make('date')
                            ->rules(['date'])
                            ->required()
                            ->placeholder('Date'),
                        DatePicker::make('end_date')
                            ->rules(['date'])
                            ->required()
                            ->placeholder('End Date'),
                    ])
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 2,
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
                TextColumn::make('budget_period')
                    ->label('Budget Period')
                    ->default('NA'),
                TextColumn::make('budget_from')
                    ->date(),
                TextColumn::make('budget_to')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Action::make('create tender')
                    ->label('Create Tender')
                    ->url(function (Budget $records) {
                        return route('tender.create', ['budget' => $records->id]);
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            BudgetitemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgets::route('/'),
            //'create' => Pages\CreateBudget::route('/create'),
            'view' => Pages\ViewBudget::route('/{record}'),
            //'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
