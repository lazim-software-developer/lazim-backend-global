<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditBuildingProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Owner Association profile';
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
                        // ->disabled(function () {
                        //     return DB::table('oa_user_registration')
                        //         ->where('verified',1)
                        //         ->exists();
                        // })
                        ->placeholder('Email'),
                    Toggle::make('verified')
                        ->rules(['boolean']),

                ]),
            ]);
    }
}
