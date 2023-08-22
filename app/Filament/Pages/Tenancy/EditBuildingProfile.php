<?php

namespace App\Filament\Pages\Tenancy;
 
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;
use Illuminate\Database\Eloquent\Model;
 
class EditBuildingProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Building profile';
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->maxLength(50),
                TextInput::make('unit_number')->unique()->maxLength(50),
                TextInput::make('address_line1'),
                TextInput::make('address_line2')->nullable(),
                TextInput::make('area')->maxLength(50),
                TextInput::make('city_id'),
                TextInput::make('lat')->nullable()->maxLength(50),
                TextInput::make('lng')->nullable()->maxLength(50),
                TextInput::make('description')->nullable(),
                TextInput::make('floors'),
            ]);
    }
}