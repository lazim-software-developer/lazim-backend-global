<?php

namespace App\Filament\Pages\Auth;

use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Support\Exceptions\Halt;

class AppEditProfile extends BaseEditProfile
{
    protected ?string $heading = 'Edit Profile';
    public ?array $data        = [];

    protected ?string $maxWidth = 'full';

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function hasLogo(): bool
    {
        return true;
    }
    protected function getLayoutData(): array
    {
        return [
            'hasTopbar' => $this->hasTopbar(),
            'maxWidth'  => $this->getMaxWidth(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        $formState = $this->form->getState();

        if (isset($formState['password']) && !empty($formState['password'])) {
            return env('APP_URL');
        }
        return env('APP_URL') . '/app/login';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->columns(1)
                    ->columnSpan('full')
                    ->schema([
                        Section::make('Personal Information')
                            ->description('Update your personal details')
                            ->icon('heroicon-o-user-circle')
                            ->collapsed()
                            ->collapsible()
                            ->schema([
                                TextInput::make('first_name')
                                    ->rules(['max:100', 'string'])
                                    ->required()
                                    ->label('First Name'),

                                TextInput::make('last_name')
                                    ->rules(['max:100', 'string'])
                                    ->label('Last Name'),

                                TextInput::make('email')
                                    ->disabled()
                                    ->placeholder('Email'),

                                TextInput::make('phone')
                                    ->disabled()
                                    ->prefix('971')
                                    ->placeholder('Phone'),
                            ])->columns(1),

                        Section::make('Company Information')
                            ->description('Update your company details.')
                            ->collapsed()
                            ->visible(function () {
                                if (auth()->user()->role->name == 'Property Manager') {
                                    return true;
                                }
                            })
                            ->collapsible()
                            ->icon('heroicon-o-building-library')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Company Name')
                                    ->disabled()
                                    ->rules(['regex:/^[a-zA-Z0-9\s]*$/'])
                                    ->placeholder('Enter company name'),

                                TextInput::make('trn_number')
                                    ->label('TRN Number')
                                    ->disabled()
                                    ->placeholder('Enter TRN number'),

                                TextInput::make('trade_license_number')
                                    ->label('Trade License Number')
                                    ->disabled()
                                    ->placeholder('Enter trade license number'),

                                TextInput::make('address')
                                    ->label('Address')
                                    ->required()
                                    ->placeholder('Enter complete address'),

                                TextInput::make('bank_account_number')
                                    ->label('Bank Account Number')
                                    ->disabled()
                                    ->placeholder('Enter account number'),

                                TextInput::make('bank_account_holder_name')
                                    ->label('Bank Account Holder Name')
                                    ->placeholder('Enter bank account holder name'),
                            ])->columns(1),

                        Section::make('Documents')
                            ->description('Upload and manage your documents')
                            ->collapsible()
                            ->collapsed()
                            ->visible(function () {
                                if (auth()->user()->role->name == 'Property Manager') {
                                    return true;
                                }
                            })
                            ->icon('heroicon-o-document')
                            ->schema([
                                FileUpload::make('profile_photo')
                                    ->disk('s3')
                                    ->directory('dev')
                                    ->image()
                                    ->openable(true)
                                    ->downloadable(true)
                                    ->maxSize(2048)
                                    ->rules('file|mimes:jpeg,jpg,png|max:2048')
                                    ->label('Profile Photo')
                                    ->columnSpanFull(),

                                FileUpload::make('trn_certificate')
                                    ->disk('s3')
                                    ->directory('dev')
                                    ->openable(true)
                                    ->downloadable(true)
                                    ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                                    ->maxSize(2048)
                                    ->label('TRN Certificate'),

                                FileUpload::make('trade_license')
                                    ->disk('s3')
                                    ->directory('dev')
                                    ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                                    ->openable(true)
                                    ->downloadable(true)
                                    ->required()
                                    ->disabledOn('edit')
                                    ->maxSize(2048)
                                    ->label('Trade License'),

                                FileUpload::make('dubai_chamber_document')
                                    ->disk('s3')
                                    ->directory('dev')
                                    ->openable(true)
                                    ->downloadable(true)
                                    ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                                    ->maxSize(2048)
                                    ->label('Other Document'),

                                FileUpload::make('memorandum_of_association')
                                    ->disk('s3')
                                    ->required()
                                    ->directory('dev')
                                    ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                                    ->maxSize(2048)
                                    ->label('Memorandum of Association'),

                                FileUpload::make('documents')
                                    ->disk('s3')
                                    ->directory('dev')
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->openable(true)
                                    ->downloadable(true)
                                    ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                                    ->maxSize(2048)
                                    ->label('Additional Documents')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Security')
                            ->description('Update your password')
                            ->collapsed()
                            ->collapsible()
                            ->icon('heroicon-o-lock-closed')
                            ->schema([
                                TextInput::make('password')
                                    ->rules([function (Get $get) {
                                        return function (string $attribute, $value, Closure $fail) use ($get) {
                                            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])[a-zA-Z\d\W]*$/', $value)) {
                                                $fail('The field must contain at least one lowercase letter, one uppercase letter, one digit and one special character.');
                                            }
                                        };
                                    }])
                                    ->live()
                                    ->revealable()
                                    ->password()
                                    ->label('New password'),

                                TextInput::make('password_confirmation')
                                    ->password()
                                    ->revealable()
                                    ->rules([function (callable $get) {
                                        return function (string $attribute, $value, Closure $fail) use ($get) {
                                            if ($value != $get('password')) {
                                                $fail('The Confirm password field must match new password.');
                                            }
                                        };
                                    }])
                                    ->reactive()
                                    ->required(function (callable $get) {
                                        if($get('password') != null) {
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    })
                                    ->label('Confirm new password'),
                            ])->columns(1),
                    ]),
            ]);
    }

    public function mount(): void
    {
        parent::mount();

        $user             = User::find(auth()->user()->id);
        $ownerAssociation = OwnerAssociation::find($user->owner_association_id);

        $this->form->fill([
            'first_name'                => $user->first_name,
            'last_name'                 => $user->last_name,
            'email'                     => $user->email,
            'phone'                     => $user->phone,
            'name'                      => $ownerAssociation->name,
            'trn_number'                => $ownerAssociation->trn_number,
            'trade_license_number'      => $ownerAssociation->trade_license_number,
            'address'                   => $ownerAssociation->address,
            'bank_account_number'       => $ownerAssociation->bank_account_number,
            'bank_account_holder_name'  => $ownerAssociation->bank_account_holder_name,
            'profile_photo'             => $ownerAssociation->profile_photo,
            'trn_certificate'           => $ownerAssociation->trn_certificate,
            'trade_license'             => $ownerAssociation->trade_license,
            'dubai_chamber_document'    => $ownerAssociation->dubai_chamber_document,
            'memorandum_of_association' => $ownerAssociation->memorandum_of_association,
        ]);
    }

    protected function getFormData(): array
    {
        $user             = User::find(auth()->user()->id);
        $ownerAssociation = OwnerAssociation::find($user->owner_association_id);

        return [
            'first_name'                => $user->first_name,
            'last_name'                 => $user->last_name,
            'email'                     => $user->email,
            'phone'                     => $user->phone,
            'name'                      => $ownerAssociation->name,
            'trn_number'                => $ownerAssociation->trn_number,
            'trade_license_number'      => $ownerAssociation->trade_license_number,
            'address'                   => $ownerAssociation->address,
            'bank_account_number'       => $ownerAssociation->bank_account_number,
            'bank_account_holder_name'  => $ownerAssociation->bank_account_holder_name,
            'profile_photo'             => $ownerAssociation->profile_photo,
            'trn_certificate'           => $ownerAssociation->trn_certificate,
            'trade_license'             => $ownerAssociation->trade_license,
            'dubai_chamber_document'    => $ownerAssociation->dubai_chamber_document,
            'memorandum_of_association' => $ownerAssociation->memorandum_of_association,
        ];
    }

    public function save(): void
    {
        try {
            $data     = $this->form->getState();
            $roleName = Role::where('id', auth()->user()->role_id)->first()->name;

            if (in_array($roleName, ['Admin', 'Property Manager'])) {
                $user = User::find(auth()->user()->id);

                if ($data['password'] ?? null) {
                    $user->update([
                        'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                    ]);
                }
                $user->update([
                    'first_name' => $data['first_name'],
                    'last_name'  => $data['last_name'] ?? null,
                ]);

                $ownerAssociation = OwnerAssociation::find($user->owner_association_id);
                if (auth()->user()->role->name == 'Property Manager') {
                    $ownerAssociation->update([
                        'address'                  => $data['address'],
                        'bank_account_holder_name' => $data['bank_account_holder_name'] ?? null,
                    ]);

                    if (isset($data['trn_certificate'])) {
                        $ownerAssociation->update([
                            'trn_certificate' => $data['trn_certificate'],
                        ]);
                    }

                    if (isset($data['profile_photo'])) {
                        $ownerAssociation->update([
                            'profile_photo' => $data['profile_photo'],
                        ]);
                    }

                    if (isset($data['trade_license'])) {
                        $ownerAssociation->update([
                            'trade_license' => $data['trade_license'],
                        ]);
                    }

                    if (isset($data['dubai_chamber_document'])) {
                        $ownerAssociation->update([
                            'dubai_chamber_document' => $data['dubai_chamber_document'],
                        ]);
                    }

                    if (isset($data['memorandum_of_association'])) {
                        $ownerAssociation->update([
                            'memorandum_of_association' => $data['memorandum_of_association'],
                        ]);
                    }

                }
            }

            $this->redirect($this->getRedirectUrl());
        } catch (Halt $exception) {
            return;
        }
    }
}
