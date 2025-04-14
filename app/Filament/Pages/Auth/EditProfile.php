<?php

namespace App\Filament\Pages\Auth;

use Closure;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\DB;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    protected ?string $heading = 'Edit Profile';
    public ?array $data = [];
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
                    ->rules(['regex:/^(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/', function (Model $record) {
                        return function (string $attribute, $value, Closure $fail) use ($record) {
                            if (DB::table('users')->whereNot('id', $record->id)->where('phone', '971' . $value)->count() > 0) {
                                $fail('The phone is already taken by a User.');
                            }
                        };
                    },])
                    ->formatStateUsing(fn(?string $state): string => substr($state, 3))
                    ->required()
                    ->prefix('971')
                    ->placeholder('Phone'),
                TextInput::make('password')
                    ->rules([function (Get $get) {
                        return function (string $attribute, $value, Closure $fail) use ($get) {
                            // Check if the value satisfies the regex
                            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])[a-zA-Z\d\W]*$/', $value)) {
                                $fail('The field must contain at least one lowercase letter, one uppercase letter, one digit and one special character.');
                            }
                        };
                    },])
                    ->live()
                    ->label('New password'),
                TextInput::make('Confirm password')
                    ->rules([function (Get $get) {
                        return function (string $attribute, $value, Closure $fail) use ($get) {
                            // Check if the value satisfies the regex
                            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])[a-zA-Z\d\W]*$/', $value)) {
                                $fail('The field must contain at least one lowercase letter, one uppercase letter, one digit and one special character.');
                            }
                            if ($value != $get('password')) {
                                $fail('The Confirm password field must match new password.');
                            }
                        };
                    },])
                    ->required(function (Get $get) {
                        if ($get('password') != null) {
                            return true;
                        }
                        return false;
                    })
                    ->label('Confirm new password'),
                // FileUpload::make('profile_photo')
                //     ->disk('s3')
                //     ->directory('dev')
                //     ->label('Profile Picture')
            ]);
    }
    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $roleName = Role::where('id', auth()->user()->role_id)->first()->name;
            if (in_array($roleName, ['Admin', 'Building Engineer', 'Accounts Manager', 'MD', 'Complaint Officer', 'Legal Officer'])) {
                $user = User::find(auth()->user()->id);
                if ($data['password'] != null) {
                    $user->Update([
                        'password'   => password_hash($data['password'], PASSWORD_DEFAULT),
                    ]);
                }
                $user->Update([
                    'first_name'    => $data['first_name'],
                    'phone'   => '971' . $data['phone'],
                    // 'profile_photo'   => $data['profile_photo'],
                ]);
            } else {
                $roleName = Role::where('id', auth()->user()->role_id)->first()->name;
                if (in_array($roleName, ['OA'])) {
                    $ownerassociation = OwnerAssociation::find(auth()->user()?->owner_association_id);
                    $ownerassociation->Update([
                        'name'    => $data['first_name'],
                        'phone'   => '971' . $data['phone'],
                        // 'profile_photo'   => $data['profile_photo'],
                    ]);
                }
                $user = User::find(auth()->user()->id);
                if ($data['password'] != null) {
                    $user->Update([
                        'password'   => password_hash($data['password'], PASSWORD_DEFAULT),
                    ]);
                }
                $user->Update([
                    'first_name'    => $data['first_name'],
                    'phone'   => '971' . $data['phone'],
                    // 'profile_photo'   => $data['profile_photo'],
                ]);
            }
            redirect('/');
        } catch (Halt $exception) {
            return;
        }
    }
}
