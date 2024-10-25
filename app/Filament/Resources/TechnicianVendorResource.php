<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TechnicianVendorResource\Pages;
use App\Models\Master\Service;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use DB;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TechnicianVendorResource extends Resource
{
    protected static ?string $model          = TechnicianVendor::class;
    protected static ?string $modelLabel     = 'Technician';
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->description('Enter the basic details of the Technician.')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('first_name')
                            ->required()
                            ->minLength(2)
                            ->maxLength(50)
                            ->placeholder('Enter the first name')
                            ->string()
                            ->live(onBlur: true)
                            ->disabledOn('edit'),

                        TextInput::make('last_name')
                            ->nullable()
                            ->maxLength(50)
                            ->placeholder('Enter the last name')
                            ->string()
                            ->live(onBlur: true)
                            ->disabledOn('edit'),

                        TextInput::make('email')
                            ->required()
                            ->placeholder('user@example.com')
                            ->email()
                            ->when(fn($record) => !$record, function ($component) {
                                $component->unique('users', 'email')
                                    ->rules(['regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/']);
                            })
                            ->live(onBlur: true)
                            ->disabledOn('edit'),
                        TextInput::make('phone')
                            ->tel()
                            ->live(onBlur: true)
                            ->required()
                            ->placeholder('5XXXXXXXX')
                            ->when(fn($record) => !$record, function ($component) {
                                $component->unique('users', 'phone')
                                    ->rules(['regex:/^(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/']);
                            })
                            ->prefix('+971')
                            ->disabledOn('edit'),
                    ]),
                Hidden::make('technician_number'),

                Section::make('Services')
                    ->description('Select the Services provided by the Technician')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Select::make('vendor_id')
                            ->label('Facility Manager')
                            ->native(false)
                            ->preload()
                            ->live()
                            ->reactive() // Ensure this is reactive
                            ->afterStateUpdated(fn(Set $set) => $set('service_id', null)) // Reset service_id after selecting vendor
                            ->placeholder('Select Facility Manager')
                            ->options(Vendor::where('owner_association_id', auth()->user()->owner_association_id)
                                    ->pluck('name', 'id')->toArray())
                            ->required(),

                        Select::make('service_id')
                            ->placeholder('Select Service')
                            ->multiple()
                            ->options(function (callable $get) {
                                $vendorId = $get('vendor_id');
                                if ($vendorId) {
                                    $services = DB::table('service_vendor')
                                        ->where('vendor_id', $vendorId)
                                        ->pluck('service_id')->toArray();

                                    return Service::whereIn('id', $services)
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                                return [];
                            })
                            ->preload()
                            ->label('Service')
                            ->searchable()
                            ->live()
                            ->reactive()
                            ->required(),

                    ]),
                Toggle::make('active')
                    ->default(true)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Technician')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Facility Manager')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index'  => Pages\ListTechnicianVendors::route('/'),
            'create' => Pages\CreateTechnicianVendor::route('/create'),
            'edit'   => Pages\EditTechnicianVendor::route('/{record}/edit'),
        ];
    }
}
