<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\OwnerAssociation;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Facades\DB;

class RegisterBuilding extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register Owner Association';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 2,
                ])->schema([
                    TextInput::make('name')
                        ->required()

                        ->placeholder('User'),
                    TextInput::make('oa_id')->label('Oa Number')
                        ->required()
                    //->disabled()
                        ->placeholder('OA Number'),
                    TextInput::make('trn')->label('TRN Number')
                        ->required()
                    //->disabled()
                        ->placeholder('TRN Number'),
                    TextInput::make('phone')
                        ->rules(['max:20', 'string'])
                        ->required()
                        ->placeholder('Contact Number'),
                    TextInput::make('address')

                        ->required()
                        ->placeholder('Address'),
                    TextInput::make('email')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Email'),
                    Toggle::make('verified')
                        ->rules(['boolean']),

                ]),

            ]);
    }

    protected function handleRegistration(array $data): OwnerAssociation
    {
        $team = OwnerAssociation::create($data);

        $team->members()->attach(auth()->user());

        return $team;
    }
}
