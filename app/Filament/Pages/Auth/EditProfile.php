<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Database\Eloquent\Model;

class EditProfile extends BaseEditProfile
{
    protected ?string $heading = 'Edit Profile';
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')
                    ->rules(['max:50', 'string'])
                    ->required()
                    ->label('Name')
                    ->maxLength(255),

                TextInput::make('email')
                    ->rules(['min:6', 'max:30', 'regex:/^[a-z0-9.]+@[a-z]+\.[a-z]{2,}$/'])
                    ->required()
                    ->disabled()
                    ->unique(
                        'users',
                        'email',
                        fn(?Model $record) => $record
                    )
                    ->email()
                    ->placeholder('Email'),

                TextInput::make('phone')
                    ->rules(['regex:/^(\+971)(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/'])
                    ->required()
                    ->unique(
                        'users',
                        'phone',
                        fn(?Model $record) => $record
                    )
                    ->placeholder('Phone'),
                //$this->getNameFormComponent(),
                //$this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
