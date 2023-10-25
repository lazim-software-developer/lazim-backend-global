<?php

namespace App\Filament\Resources\Vendor;

use App\Filament\Resources\Vendor\VendorResource\Pages;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Vendor Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('name')
                        ->required()
                        ->placeholder('Name'),
                    TextInput::make('email')
                        ->required()
                        ->placeholder('Email'),
                    TextInput::make('phone')
                        ->required()
                        ->placeholder('Phone'),

                    Select::make('owner_id')->label('Manager Name')
                        ->rules(['exists:users,id'])
                        ->required()
                        ->preload()
                        ->relationship('user', 'first_name')
                        ->searchable()
                        ->getSearchResultsUsing(fn(string $search): array=> User::where('role_id', 1, "%{$search}%")->limit(50)->pluck('first_name', 'id')->toArray())
                        ->getOptionLabelUsing(fn($value): ?string => User::find($value)?->first_name)
                        ->placeholder('Manager Name'),
                    TextInput::make('manager_email')->label('Manager Email')
                        ->placeholder('Manager Email'),
                    TextInput::make('manager_phone')->label('Manager Phone')
                        ->placeholder('Manager Phone'),
                    TextInput::make('tl_number')->label('Trade Lisence Number')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->unique(
                            'vendors',
                            'tl_number',
                            fn(?Model $record) => $record
                        )
                        ->placeholder('Trade Lisence Number'),
                    DatePicker::make('tl_expiry')->label('Trade Licence Expiry')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Trade Lisence Expiry'),

                    Select::make('service')->label('Enter Service Details')
                        ->options([
                            'cleaning service'      => 'Cleaning Service',
                            'mep service'           => 'MEP Service',
                            'security'              => 'Security',
                            'life guard'            => 'Life Guard',
                            'concierge'             => 'concierge',
                            'technical services'    => 'Technical Services',
                            'swimming pool service' => 'Swimming Pool Service',
                            'pest control'          => 'Pest Control',
                            'gym'                   => 'GYM',
                            'chiller'               => 'Chiller',
                            'water tank cleaning'   => 'Water Tank Cleaning',
                            'fire system'           => 'Fire System',
                            'other'                 => 'Other',
                        ])
                        ->live()
                        ->required(),
                    TextInput::make('other')->label('Other service Details')
                        ->required()
                        ->hidden(fn(Get $get) => $get('service') != 'other'),
                    FileUpload::make('tl_document')->label('TL Document')
                        ->required()
                        ->preserveFilenames()
                        ->downloadable()
                        ->previewable()
                        ->disk('s3'),
                    FileUpload::make('trn_certificate')->label('TRN Certificate')
                        ->required()
                        ->preserveFilenames()
                        ->downloadable()
                        ->previewable()
                        ->disk('s3'),
                    FileUpload::make('third_party_certificate')->label('Third Party Liability Certificate')
                        ->required()
                        ->preserveFilenames()
                        ->downloadable()
                        ->previewable()
                        ->disk('s3'),
                    FileUpload::make('risk_assessment')->label('Risk Assessment')
                        ->required()
                        ->preserveFilenames()
                        ->downloadable()
                        ->previewable()
                        ->disk('s3'),
                    FileUpload::make('safety_policy')->label('Safety Policy')
                        ->preserveFilenames()
                        ->downloadable()
                        ->previewable()
                        ->disk('s3'),
                    FileUpload::make('bank_details')->label('Bank Details On Company Letter Head With Stamp')

                        ->disk('s3'),
                    FileUpload::make('authority_approval')->label('Authority Approval')

                        ->disk('s3'),

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
                Tables\Columns\TextColumn::make('user.first_name')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('tl_number')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('tl_expiry')
                    ->toggleable()
                    ->date(),

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
            VendorResource\RelationManagers\ServicesRelationManager::class,
            VendorResource\RelationManagers\UsersRelationManager::class,
            VendorResource\RelationManagers\ContactsRelationManager::class,
            VendorResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit'   => Pages\EditVendor::route('/{record}/edit'),
        ];
    }

}
