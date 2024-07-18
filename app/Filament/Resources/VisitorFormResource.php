<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitorFormResource\Pages;
use App\Filament\Resources\VisitorFormResource\RelationManagers;
use App\Models\Visitor\FlatVisitor;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class VisitorFormResource extends Resource
{
    protected static ?string $model = FlatVisitor::class;
    protected static ?string $title = 'Flat visitor';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Visitors';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('flat_id')->label('Unit')
                ->relationship('flat', 'property_number')->disabled(),
                TextInput::make('name')->disabled(),
                TextInput::make('email')->disabled(),
                TextInput::make('start_time')->label('Date')->disabled(),
                TextInput::make('time_of_viewing')->label('Time')->disabled(),
                TextInput::make('number_of_visitors')->disabled(),
                Select::make('status')
                                ->options([
                                    'approved' => 'Approve',
                                    'rejected' => 'Reject',
                                ])
                                ->disabled(function(FlatVisitor $record){
                                    return $record->status != null;
                                })
                                ->required()
                                ->searchable()
                                ->live(),
                                Repeater::make('guestDocuments')->label('Documents')
                                    ->relationship('guestDocuments')->disabled()
                                    ->schema([
                                        TextInput::make('name')
                                            ->rules(['max:30', 'regex:/^[a-zA-Z\s]*$/'])
                                            ->required()
                                            ->placeholder('Name'),
                                        FileUpload::make('url')
                                            ->disk('s3')
                                            ->rules('file|mimes:jpeg,jpg,png|max:2048')
                                            ->directory('dev')
                                            ->openable(true)
                                            ->downloadable(true)
                                            ->image()
                                            ->maxSize(2048)
                                            ->required()
                                            ->label('File')

                                    ])
                                    ->columnSpan([
                                        'sm' => 1,
                                        'md' => 1,
                                        'lg' => 2,
                                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                ->searchable()
                ->default('NA')
                ->label('Ticket Number'),
                TextColumn::make('flat.property_number')
                ->label('Unit'),
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('start_time')
                    ->label('Date')
                    // ->date()
                    ->default('NA'),
                TextColumn::make('time_of_viewing')
                    ->label('time')
                    // ->time()
                    ->default('NA'),
                TextColumn::make('number_of_visitors')->default('NA'),
                TextColumn::make('status')->default('NA'),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitorForms::route('/'),
            // 'create' => Pages\CreateVisitorForm::route('/create'),
            'edit' => Pages\EditVisitorForm::route('/{record}/edit'),
        ];
    }
}
