<?php

namespace App\Filament\Resources\FlatTenantResource\RelationManagers;

use App\Models\FamilyMember;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FamilyMembersRelationManager extends RelationManager
{
    protected static string $relationship = 'user';

    protected static ?string $title = 'Family Members';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name'),
                TextInput::make('last_name'),
                TextInput::make('phone'),
                Grid::make()->columns(2)->schema([
                    TextInput::make('gender'),
                    TextInput::make('relation'),
                    TextInput::make('passport_number'),
                    DatePicker::make('passport_expiry_date'),
                    TextInput::make('emirates_id'),
                    DatePicker::make('emirates_expiry_date'),
                    TextInput::make('visa_number'),
                    DatePicker::make('visa_number_expiry_date'),
                ]),
                ViewField::make('documents')
                    ->view('filament.forms.components.document-preview')
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('relation')
            ->query(FamilyMember::where('user_id', $this->ownerRecord->tenant_id)->where('active', true))
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label('Name')
                    ->formatStateUsing(function ($record) {
                        return $record->first_name . ' ' . ($record->last_name ?? '');
                    }),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('relation'),
                Tables\Columns\TextColumn::make('phone')->default('NA'),
                Tables\Columns\TextColumn::make('passport_number'),
                Tables\Columns\TextColumn::make('passport_expiry_date'),
                Tables\Columns\TextColumn::make('emirates_id'),
                Tables\Columns\TextColumn::make('emirates_expiry_date'),
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
