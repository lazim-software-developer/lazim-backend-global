<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Building\Building;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Database\Eloquent\Model;

class RegisterBuilding extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register Building';
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
                //TextInput::make('city_id'),
                TextInput::make('lat')->nullable()->maxLength(50),
                TextInput::make('lng')->nullable()->maxLength(50),
                TextInput::make('description')->nullable(),
                TextInput::make('floors'),
            ]);
    }

    protected function handleRegistration(array $data): Building
    {
        $building = Building::create($data);

        $building->members()->attach(auth()->user());

        return $building;
    }
}
