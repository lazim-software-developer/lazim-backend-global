<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\FlatOwners;
use Filament\Tables\Table;
use App\Models\ApartmentOwner;
use App\Models\DelinquentOwner;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use App\Jobs\OAM\InvoiceDueMailJob;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DelinquentOwnerResource\Pages;
use App\Filament\Resources\DelinquentOwnerResource\RelationManagers;

class DelinquentOwnerResource extends Resource
{
    protected static ?string $model = DelinquentOwner::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('flat.property_number')->label('Unit'),
                TextColumn::make('owner.name')->limit(25),
                TextColumn::make('last_payment_date')->default('NA'),
                TextColumn::make('last_payment_amount')->default('NA'),
                TextColumn::make('outstanding_balance'),
                TextColumn::make('quarter_1_balance'),
                TextColumn::make('quarter_2_balance'),
                TextColumn::make('quarter_3_balance')->default('NA'),
                TextColumn::make('quarter_4_balance')->default('NA'),
                TextColumn::make('invoice_pdf_link')->label('invoice_file')->formatStateUsing(fn ($state) => '<a href="'. $state .'" target="_blank">LINK</a>')
                ->html(),
                
            ])
            ->filters([
                Filter::make('invoice_date')
                    ->form([
                        Select::make('year')
                        ->searchable()
                        ->placeholder('Select Year')
                        ->options(array_combine(range(now()->year, 2018), range(now()->year, 2018))),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['year'])) {
                                return $query
                                    ->when(
                                        $data['year'],
                                        fn (Builder $query, $year) => $query->where('year', $year)
                                    );
                        }

                            return $query;
                        }),
                Filter::make('Building')
                    ->form([
                        Select::make('building')
                        ->searchable()
                        ->options(function () {
                            $oaId = auth()->user()->owner_association_id;
                            return Building::where('owner_association_id', $oaId)
                                ->pluck('name', 'id');
                        })
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['building'],
                                fn (Builder $query, $building_id): Builder => $query->where('building_id', $building_id),
                            );
                        }),
                    ],layout: FiltersLayout::AboveContent)->filtersFormColumns(3)
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('Remind')
                        ->form([
                            Textarea::make('content')
                                ->maxLength(1024)
                                ->rows(10)
                                ->label('Content of email'),
                        ])
                        ->fillForm(fn(DelinquentOwner $record): array => [
                            'content' => 'Your payment is Due, please make the payment ASAP.'
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                // Access the owner_id of each selected record
                                
                                $owner = ApartmentOwner::find($record->owner_id);
                                $content = $data['content'];
                                InvoiceDueMailJob::dispatch($owner, $content);
                            }
                        })
                        ->slideOver()
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
            'index' => Pages\ListDelinquentOwners::route('/'),
            // 'create' => Pages\CreateDelinquentOwner::route('/create'),
            // 'edit' => Pages\EditDelinquentOwner::route('/{record}/edit'),
        ];
    }    
}
