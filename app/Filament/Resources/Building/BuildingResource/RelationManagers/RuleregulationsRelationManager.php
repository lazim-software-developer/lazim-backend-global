<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\RuleRegulation;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class RuleregulationsRelationManager extends RelationManager
{
    protected static string $relationship = 'ruleregulations';
    protected static ?string $modelLabel = 'Rule and Regulations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Rule and Regulations';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('building_id')
                    ->default(function (RelationManager $livewire) {
                        return $livewire->ownerRecord->id;
                    }),
                Textarea::make('rule_regulation')
                    ->required()
                    ->autosize()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rule_regulation')->wrap(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('New Rule Regulation')
                    ->visible(fn(RelationManager $livewire) => RuleRegulation::where('building_id', $livewire->ownerRecord->id)->count() == 0)
                    ->button()
                    ->form([
                        Hidden::make('building_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                        Textarea::make('rule_regulation')
                            ->required()
                            ->autosize()
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data): void {
                        $security = RuleRegulation::create([
                            'building_id' => $data['building_id'],
                            'rule_regulation' => $data['rule_regulation'],

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
                        Textarea::make('rule_regulation')
                            ->required()
                            ->autosize()
                            ->columnSpanFull(),
                    ])
                    ->fillForm(fn(RuleRegulation $record): array => [
                        'rule_regulation' => $record->rule_regulation,
                    ])
                    ->action(function (RuleRegulation $record, array $data): void {
                        
                        $record->rule_regulation = $data['rule_regulation'];
                        $record->save();
                    })
                    ->slideOver()
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
}
