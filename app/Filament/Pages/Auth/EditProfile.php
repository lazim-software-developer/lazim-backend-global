<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Database\Eloquent\Model;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')
                    ->required()
                    ->label('Name')
                    ->maxLength(255),

                TextInput::make('email')
                    ->rules(['email'])
                    ->required()
                    ->unique(
                        'users',
                        'email',
                        fn(?Model $record) => $record
                    )
                    ->email()
                    ->placeholder('Email'),

                TextInput::make('phone')
                    ->rules(['regex:/^\+971-?4-?\d{7}$/', 'string'])
                    ->required()
                    ->unique(
                        'users',
                        'phone',
                        fn(?Model $record) => $record
                    )
                    ->placeholder('Phone'),
                // $this->getNameFormComponent(),
                // $this->getEmailFormComponent(),
                // $this->getPasswordFormComponent(),
                // $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
