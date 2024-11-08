<?php

namespace App\Filament\Resources\FacilityManagerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title                       = 'Documents';
    protected static ?string $modelLabel                  = 'Document';
    protected static ?string $pluralModelLabel            = 'Documents';
    protected static bool $shouldRenderTableHeaderActions = true;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->formatStateUsing(function ($state) {
                        return ucwords(str_replace('_', ' ', $state));
                    })
                    ->disabled(),
                Forms\Components\DatePicker::make('expiry_date')
                        ->label('Expiry Date')
                        // ->hiddenOn('view')
                        ->disabled(),
                FileUpload::make('url')
                    ->disk('s3')
                    ->directory('dev')
                    ->disabled()
                    ->openable(true)
                    ->downloadable(true)
                    ->label('Document')
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 2,
                    ]),
                Select::make('status')
                    ->hiddenOn('view')
                    ->options([
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])

                    ->searchable()
                    ->live(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('documentLibrary.name'),
                // Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('expiry_date')
                ->default('NA'),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
