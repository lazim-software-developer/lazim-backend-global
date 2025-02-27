<?php

namespace App\Filament\Resources\FacilityManagerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ComplianceDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'ComplianceDocuments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('doc_name')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('url')
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->openable(true)
                    ->downloadable(true)
                    ->label('Document'),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('doc_name')
            ->columns([
                Tables\Columns\TextColumn::make('doc_name')->label('Document Name'),
                Tables\Columns\TextColumn::make('expiry_date')->label('Expiry Date'),
                // Tables\Columns\TextColumn::make('url')->label('URL'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
