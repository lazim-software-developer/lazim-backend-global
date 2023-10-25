<?php

namespace App\Filament\Resources\Vendor;

use App\Filament\Resources\Vendor\ContactsResource\Pages;
use App\Models\Vendor\Contact;
use App\Models\Vendor\Vendor;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ContactsResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Vendor Management';
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->rules(['max:50', 'string'])
                            ->required()
                            ->placeholder('Name'),

                        TextInput::make('phone')
                            ->rules(['max:10', 'string'])
                            ->required()
                            ->unique(
                                'contacts',
                                'phone',
                                fn(?Model $record) => $record
                            )
                            ->placeholder('Phone'),

                        TextInput::make('email')
                            ->rules(['email'])
                            ->required()
                            ->unique(
                                'contacts',
                                'email',
                                fn(?Model $record) => $record
                            )
                            ->email()
                            ->placeholder('Email')
                        ,

                        TextInput::make('designation')->label('Designation')
                            ->placeholder('Designation'),

                        MorphToSelect::make('contactable')
                            ->types([
                                Type::make(Vendor::class)->titleAttribute('name'),
                            ])
                        ,
                        TextInput::make('contactable_id')
                            ->rules(['max:255'])
                            ->hidden()
                            ->required()
                            ->placeholder('Contactable Id'),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('designation')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('contactable_type')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                ViewColumn::make('name')->view('tables.columns.contact')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContacts::route('/create'),
            'edit'   => Pages\EditContacts::route('/{record}/edit'),
        ];
    }
}
