<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\ApartmentSafety;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppartmentsafetyRelationManager extends RelationManager
{
    protected static string $relationship = 'appartmentsafety';

    protected static ?string $modelLabel = 'Apartment safety';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Apartment safety';
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('building_id')
                    ->default(function (RelationManager $livewire) {
                        return $livewire->ownerRecord->id;
                    }),
                RichEditor::make('apartment_safety')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('apartment_safety')->label('Content')->html()->wrap(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
               Action::make('New Apartment safety')
                    ->visible(fn(RelationManager $livewire) => ApartmentSafety::where('building_id', $livewire->ownerRecord->id)->count() == 0)
                    ->button()
                    ->form([
                        Hidden::make('building_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                        RichEditor::make('apartment_safety')
                            ->required()

                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data): void {
                        $security = ApartmentSafety::create([
                            'building_id' => $data['building_id'],
                            'apartment_safety' => $data['apartment_safety'],

                        ]);
                    })
                    ->slideOver()
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Action::make('Edit')
                    ->button()
                    ->form([
                        Hidden::make('building_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                        RichEditor::make('apartment_safety')
                            ->required()

                            ->columnSpanFull(),
                    ])
                    ->fillForm(fn(ApartmentSafety $record): array => [
                        'apartment_safety' => $record->apartment_safety,
                    ])
                    ->action(function (ApartmentSafety $record, array $data): void {

                        $record->apartment_safety = $data['apartment_safety'];
                        $record->save();
                    })
                    ->slideOver()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->role?->name === 'Property Manager';
    }
}
