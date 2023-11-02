<?php

namespace App\Filament\Resources\User\OwnerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'userDocuments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Select::make('document_library_id')
                    ->rules(['exists:document_libraries,id'])
                    ->required()
                    ->preload()
                    ->relationship('documentLibrary', 'name')
                    ->searchable()
                    ->placeholder('Document Library'),

                FileUpload::make('url')
                        ->disk('s3')
                        ->directory('dev')
                        ->label('Document')
                        ->required(),

                Select::make('status')
                        ->options([
                            'submitted' => 'Submitted',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->searchable()
                        ->required()
                        ->placeholder('Status'),

                TextInput::make('comments'),
                Hidden::make('documentable_type')
                    ->default('App\Models\User\User'),
                DatePicker::make('expiry_date')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Expiry Date'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Document Name')
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('documentLibrary.name')
                    ->searchable()
                    ->default('NA')
                    ->label('Document Library Name')
                    ->limit(50),
                TextColumn::make('status')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('expiry_date')
                    ->date(),
                TextColumn::make('documentUsers.first_name')
                    ->searchable()
                    ->label('Tenant Name')
                    ->default('NA')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
