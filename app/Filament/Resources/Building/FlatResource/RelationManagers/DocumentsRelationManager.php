<?php

namespace App\Filament\Resources\Building\FlatResource\RelationManagers;

use App\Models\Building\Flat;
use App\Models\Master\DocumentLibrary;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(50),
                Select::make('document_library_id')
                    ->required()
                    ->options(function () {
                        return DocumentLibrary::where('label', 'property_manager')->pluck('name', 'id');
                    })
                    ->searchable(),
                Hidden::make('owner_association_id')
                    ->default(auth()->user()->owner_association_id),
                Hidden::make('status')
                    ->default('done'),
                Hidden::make('documentable_id')
                    ->default($this->ownerRecord->id),
                Hidden::make('documentable_type')
                    ->default(Flat::class),
                Hidden::make('flat_id')
                    ->default($this->ownerRecord->id),
                FileUpload::make('url')
                    ->disk('s3')
                    ->rules('file|mimes:jpeg,jpg,png,mp4,avi,mov,mkv|max:2048')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->maxSize(2048)
                    ->required()
                    ->label('File')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('documentLibrary.name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
