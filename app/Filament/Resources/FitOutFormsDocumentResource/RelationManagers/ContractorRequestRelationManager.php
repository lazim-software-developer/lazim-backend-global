<?php

namespace App\Filament\Resources\FitOutFormsDocumentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractorRequestRelationManager extends RelationManager
{
    protected static string $relationship = 'contractorRequest';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('work_type'),
                Textarea::make('work_name'),
                TextInput::make('status'),
                Repeater::make('documents')
                    ->relationship()
                    ->schema([
                        FileUpload::make('url')
                            ->disk('s3')
                            ->directory('dev')
                            ->maxSize(2048)
                            ->openable(true)
                            ->downloadable(true)
                            ->label(function ($record) {
                                return $record->name;
                            }),
                    ])->disabled()
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 2
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('work_type')
            ->columns([
                TextColumn::make('work_type'),
                TextColumn::make('work_name'),
                TextColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }
}
