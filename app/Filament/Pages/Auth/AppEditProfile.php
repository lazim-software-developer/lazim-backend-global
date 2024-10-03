<?php

namespace App\Filament\Pages\Auth;

use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;


class AppEditProfile extends BaseEditProfile
{
    protected ?string $heading = 'Edit Profile';
    public ?array $data        = [];

    protected function getRedirectUrl(): string
    {
        return env('APP_URL') . '/app/login';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')
                    ->rules(['max:100', 'string'])
                    ->required()
                    ->label('Name')
                    ->maxLength(255),

                TextInput::make('email')
                    ->rules(['min:6', 'max:30', 'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'])
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
                    ->disabled()
                    ->prefix('971')
                    ->placeholder('Phone'),
                TextInput::make('password')
                    ->rules([function (Get $get) {
                        return function (string $attribute, $value, Closure $fail) use ($get) {
                            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])[a-zA-Z\d\W]*$/', $value)) {
                                $fail('The field must contain at least one lowercase letter, one uppercase letter, one digit and one special character.');
                            }
                        };
                    }])
                    ->live()
                    ->label('New password'),
                TextInput::make('Confirm password')
                    ->rules([function (Get $get) {
                        return function (string $attribute, $value, Closure $fail) use ($get) {
                            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])[a-zA-Z\d\W]*$/', $value)) {
                                $fail('The field must contain at least one lowercase letter, one uppercase letter, one digit and one special character.');
                            }
                            if ($value != $get('password')) {
                                $fail('The Confirm password field must match new password.');
                            }
                        };
                    }])
                    ->required(function (Get $get) {
                        if ($get('password') != null) {
                            return true;
                        }
                        return false;
                    })
                    ->label('Confirm new password'),
            ]);
    }

    public function save(): void
    {
        try {
            $data     = $this->form->getState();
            $roleName = Role::where('id', auth()->user()->role_id)->first()->name;
            if (in_array($roleName, ['Admin', 'Property Manager'])) {
                $user = User::find(auth()->user()->id);
                if ($data['password'] != null) {
                    $user->Update([
                        'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                    ]);
                }
                $user->Update([
                    'first_name' => $data['first_name'],
                ]);
            } else {
                $ownerassociation = OwnerAssociation::find(auth()->user()?->owner_association_id);
                $ownerassociation->Update([
                    'name' => $data['first_name'],
                ]);
                $user = User::find(auth()->user()->id);
                if ($data['password'] != null) {
                    $user->Update([
                        'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                    ]);
                }
                $user->Update([
                    'first_name' => $data['first_name'],
                ]);
            }

            $this->redirect($this->getRedirectUrl());

        } catch (Halt $exception) {
            return;
        }
    }
}
