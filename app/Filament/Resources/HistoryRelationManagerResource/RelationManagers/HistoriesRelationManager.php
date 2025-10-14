<?php

namespace App\Filament\Resources\HistoryRelationManagerResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class HistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';

    protected static ?string $recordTitleAttribute = 'action';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('action')
                    ->required()
                    ->maxLength(255),

                   Forms\Components\KeyValue::make('changes')
                    ->label('Changes')
                    ->formatStateUsing(fn($state) => collect($state)
                        ->mapWithKeys(fn($v, $k) => [$k => ($v['old'] ?? '') . ' → ' . ($v['new'] ?? '')])
                        ->toArray()
                    )->columnSpan(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->columns([
            TextColumn::make('action')->label('Action'),
            TextColumn::make('created_at')->label('At')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
             //   Tables\Actions\EditAction::make(),
             //   Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public function mount(): void
{
    Log::info('Parent record in relation manager:', [
        'id' => $this->getOwnerRecord()->id,
        'class' => get_class($this->getOwnerRecord()),
    ]);
}
}
