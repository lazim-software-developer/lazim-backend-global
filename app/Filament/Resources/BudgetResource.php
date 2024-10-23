<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Resources\Resource;
use App\Models\Accounting\Budget;
use App\Models\Building\Building;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BudgetResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BudgetResource\RelationManagers;
use App\Filament\Resources\BudgetResource\RelationManagers\BudgetitemsRelationManager;
use Illuminate\Support\Facades\DB;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;
    protected static ?string $title = 'Budget';
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
                    ->label('Budget period')
                    ->default('NA'),
                TextColumn::make('budget_from')
                    ->date(),
                TextColumn::make('budget_to')
                    ->date(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                ->label('Building')
                ->options(function () {
                    if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                        return Building::all()->pluck('name', 'id');
                    } else {
                        $buildingId = DB::table('building_owner_association')->where('owner_association_id',auth()->user()?->owner_association_id)->where('active',true)->pluck('building_id');
                        return Building::whereIn('id',$buildingId)->pluck('name', 'id');
                    }
                })
                ->native(false)
                ->searchable(),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Action::make('create tender')
                    ->label('Create Tender')
                    ->url(function (Budget $records) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                        return route('tenders.create', ['budget' => $records->id]);
                            
                        }
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
