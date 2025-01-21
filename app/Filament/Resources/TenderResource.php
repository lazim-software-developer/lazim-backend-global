<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Resources\Resource;
use App\Models\Accounting\Tender;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TenderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\TenderResource\RelationManagers;
use App\Filament\Resources\TenderResource\RelationManagers\ProposalsRelationManager;

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
                    ->label('Building'),
                Select::make('budget_id')
                    ->relationship('budget', 'budget_period')
                    ->preload()
                    ->searchable()
                    ->disabled()
                    ->label('Budget period'),
                Select::make('service_id')
                    ->relationship('service','name')
                    ->preload()
                    ->searchable()
                    ->disabled()
                    ->label('Service'),
                TextInput::make('tender_type')
                    ->placeholder('NA')
                    ->disabled()
                    ->label('Contract type'),
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
                    ->label('Building'),
                TextColumn::make('budget.budget_period')
                    ->searchable()
                    ->default('NA')
                    ->label('Budget period'),
                TextColumn::make('service.name')
                    ->searchable()
                    ->default('NA')
                    ->label('Service'),
                TextColumn::make('tender_type')
                    ->searchable()
                    ->default('NA')
                    ->label('Contract type'),
                TextColumn::make('date')
                    ->date(),
                TextColumn::make('end_date')
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

                SelectFilter::make('service_id')
                ->label('Service')
                ->relationship('service','name')
                ->searchable()
                ->preload()
                ->native(false),

                Filter::make('budget period')
                ->form([
                    Select::make('year')
                    ->label('Budget period')
                    ->options(function () {
                        $currentYear = Carbon::now()->year; // Get the current year
                        $years = [];
                    
                        // Generate past 10 years including the current year
                        for ($i = 0; $i < 10; $i++) {
                            $years[$currentYear - $i] = $currentYear - $i;
                        }
                    
                        return $years; 
                    })
                    ->placeholder('Select Year')
                    ->required(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    // Check if a year is selected
                    if (!empty($data['year'])) {
                        $selectedYear = $data['year'];
            
                        // Filter tenders based on the associated budget's period
                        $query->whereHas('budget', function ($query) use ($selectedYear) {
                            $query->whereYear('budget_from', '<=', $selectedYear)  // Budget starts before or in the selected year
                                  ->whereYear('budget_to', '>=', $selectedYear);    // Budget ends after or in the selected year
                        });
                    }
                    
                    return $query;
                })
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
