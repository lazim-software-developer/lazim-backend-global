<?php

namespace App\Filament\Resources\ComplaintcomplaintResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class RemarksRelationManager extends RelationManager
{
    protected static string $relationship = 'remarks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('remarks')
                ->required()
                ->label('Remark'),

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

        //    Select::make('status')
        //         ->label('Status')
        //         ->options([
        //             'success' => 'Open',
        //             'warning' => 'In-Progress',
        //             'danger' => 'Closed',
        //         ])
        //         ->required()
        //         ->disabled(),// disable on view
        //        // ->default(fn(?Model $record) => $record?->complaint?->status ?? null),

                Repeater::make('media')
                ->relationship('media')
                ->label('Attached Media')
                ->schema([
                    FileUpload::make('url')
                        ->disk('s3')
                        ->directory('dev')
                        ->openable()
                        ->downloadable()
                        ->deletable(false)
                        ->disabled()
                        ->label('Media File'),
                ])
                ->visibleOn('view')
                ->columns(1),
               
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('remarks')
            ->columns([
                TextColumn::make('remarks')
                    ->label('Remark')
                    ->wrap(),

                TextColumn::make('user.first_name')->label('Remarked by'),

                TextColumn::make('user.role.name'),

                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'Open',
                        'warning' => 'In-Progress',
                        'danger' => 'Closed',
                    ])
                    ->label('Status'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Created At'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
               // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
