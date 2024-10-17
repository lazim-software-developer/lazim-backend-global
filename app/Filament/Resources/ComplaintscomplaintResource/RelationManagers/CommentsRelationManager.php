<?php

namespace App\Filament\Resources\ComplaintscomplaintResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('body')
                    ->label('Comment')
                    ->required()
                    ->minLength(3)
                    ->maxLength(100)
                    ->columnSpanFull(),
                Hidden::make('commentable_type')
                    ->default('App\Models\Building\Complaint'),
                Hidden::make('user_id')
                ->default(auth()->user()->id),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at','desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')->label('Name'),
                Tables\Columns\TextColumn::make('user.role.name'),
                Tables\Columns\TextColumn::make('body')->wrap()->label('Comment'),
                Tables\Columns\TextColumn::make('created_at')
                ->label('Commented On')
                ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->diffForHumans())
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->createAnother(false),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
