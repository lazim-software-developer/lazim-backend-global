<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

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
                FileUpload::make('profile_photo')
                    ->disk('s3')
                    ->directory('dev')
                    ->image()
                    ->maxSize(2048)
                    ->label('Profile Photo'),
                //$this->getNameFormComponent(),
                //$this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
