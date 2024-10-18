<?php

namespace App\Filament\Resources\UserApprovalResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'history';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('updated_by')
                    ->relationship('user', 'first_name')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255),
                Textarea::make('remarks')
                    ->maxLength(250)
                    ->rows(5)
                    ->required()
                    ->visible(function (Get $get) {
                        if ($get('status') == 'rejected') {
                            return true;
                        }
                        return false;
                    }),
                DateTimePicker::make('updated_at')
                    ->label('Status updated on')
                    ->disabled(),
                FileUpload::make('document')
                    ->label(function (Get $get) {
                        if ($get('document_type') == 'Ejari') {
                            return 'Tenancy Contract / Ejari';
                        }
                        return $get('document_type');
                    })
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->required()
                    ->disabled(),
                FileUpload::make('emirates_document')
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->required()
                    ->disabled(),
                FileUpload::make('passport')
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->required()
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('status'),
                TextColumn::make('remarks')->default('NA'),
                TextColumn::make('user.first_name')->default('NA')->limit(20),
                TextColumn::make('updated_at')->date()
                    ->formatStateUsing(fn(?string $state) => $state ? $state : 'NA')->label('Status updated on'),
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
                ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
