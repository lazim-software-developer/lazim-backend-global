<?php

namespace App\Filament\Resources\ComplaintscomplaintResource\RelationManagers;

use App\Models\User\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('commentable_type')
                    ->default('App\Models\Building\Complaint'),
                Hidden::make('user_id')
                ->default(auth()->user()->id),

            TextInput::make('name')
                ->visibleOn('view')
                ->formatStateUsing(function(?Model $record){
                    if ($record && $record->user_id) {
                        return User::where('id', $record->user_id)->value('first_name');
                    }
                    return 'N/A'; // or any other default value
                })
                ->label('Commented by'),
            
            TextInput::make('role')
                ->visibleOn('view')
                ->formatStateUsing(function(?Model $record){
                    if ($record && $record->user_id) {
                        return User::where('id', $record->user_id)->first()->role->name;
                    }
                    return 'N/A'; // or any other default value
                }),
            
            TextInput::make('created_at')
                ->visibleOn('view')
                ->formatStateUsing(function(?Model $record){
                    if ($record) {
                        return \Carbon\Carbon::parse($record->created_at)->diffForHumans();
                    }
                    return 'N/A'; // or any other default value
                })
                ->label('Commented on'),

            Textarea::make('body')
                    ->label('Comment')
                    ->required()
                    ->minLength(3)
                    ->maxLength(100)
                    ->columnSpanFull(),
            
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at','desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')->label('Commented by'),
                Tables\Columns\TextColumn::make('user.role.name'),
                Tables\Columns\TextColumn::make('body')->limit(35)->label('Comment'),
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
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
