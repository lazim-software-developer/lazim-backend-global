<?php

namespace App\Filament\Pages\Auth;

use App\Models\User\User;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Forms\Get; // Correct namespace for Get
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Support\Exceptions\Halt;
use Closure;
use Illuminate\Database\Eloquent\Model;

class EditProfile extends BaseEditProfile
{
    protected ?string $heading = 'Edit Profile';
    public ?array $data = [];

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
            'maxWidth' => $this->getMaxWidth(),
        ];
    }

    public function getMaxWidth(): MaxWidth
    {
        return MaxWidth::ThreeExtraLarge;
    }

    protected function getRedirectUrl(): string
    {
        $formState = $this->form->getState();

        $panel = filament()->getId();

        if ($panel === 'app' && !empty($formState['password'])) {
            request()->session()->invalidate();
            return env('APP_URL') . '/app/login';
        }
        return env('APP_URL') . '/admin/login';
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFormSchema());
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make()
                ->columns(['default' => 1, 'sm' => 2, 'md' => 3, 'lg' => 4, 'xl' => 6, '2xl' => 8])
                ->extraAttributes(['class' => 'filament-forms-grid'])
                ->columnSpan('small')
                ->schema([
                    $this->getPersonalInformationSection(),
                    $this->getCompanyInformationSection(),
                    $this->getDocumentsSection(),
                    $this->getSecuritySection(),
                ]),
        ];
    }

    protected function getOwnerAssociation(): OwnerAssociation
    {
        return auth()->user()->ownerAssociation->first() ?? null;
    }

    protected function getPersonalInformationSection(): Section
    {
        return Section::make('Personal Information')
            ->description('Update your personal details')
            ->icon('heroicon-o-user-circle')
            ->collapsible()
            ->collapsed()
            ->schema([
                TextInput::make('first_name')
                    ->rules(['max:100', 'string'])
                    ->required()
                    ->label('First Name'),
                TextInput::make('last_name')
                    ->rules(['max:100', 'string'])
                    ->label('Last Name'),
                TextInput::make('email')
                    ->rules(['min:6', 'max:30', 'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'])
                    ->required()
                    ->disabled()
                    ->unique('users', 'email', fn(?Model $record) => $record)
                    ->email()
                    ->placeholder('Email'),
                TextInput::make('phone')
                    ->length(9)
                    ->placeholder('XXXXXXXXX')
                    ->rules($this->getPhoneValidationRules())
                    ->formatStateUsing(fn(?string $state): string => substr($state, 3))
                    ->required()
                    ->prefix('971')
                    ->placeholder('Phone'),
                FileUpload::make('profile_photo')
                    ->disk('s3')
                    ->directory('dev')
                    ->image()
                    ->openable(true)
                    ->downloadable(true)
                    ->maxSize(2048)
                    ->rules('file|mimes:jpeg,jpg,png|max:2048')
                    ->helperText('Accepted file types: jpg, jpeg, png / Max file size: 2MB')
                    ->label('Profile Photo')
                    ->columnSpanFull(),
            ])->columns(2);
    }

    protected function getCompanyInformationSection(): Section
    {
        return Section::make('Company Information')
            ->description('Update your company details.')
            ->collapsed()
            ->visible(fn() => auth()->user()->role->name === 'OA')
            ->collapsible()
            ->icon('heroicon-o-building-library')
            ->schema([
                FileUpload::make('ownerAssociation.profile_photo')
                    ->disk('s3')
                    ->directory('dev')
                    ->image()
                    ->openable(true)
                    ->downloadable(true)
                    ->maxSize(2048)
                    ->rules('file|mimes:jpeg,jpg,png|max:2048')
                    ->helperText('Accepted file types: jpg, jpeg, png / Max file size: 2MB')
                    ->label('Company Logo')
                    ->formatStateUsing(function(){
                        if (!$this->getOwnerAssociation()) {
                            return null;
                        }
                        return [$this->getOwnerAssociation()->profile_photo];
                    })
                    ->columnSpanFull(),
                TextInput::make('ownerAssociation.company_name')
                    ->label('Company Name')
                    ->disabled()
                    ->rules(['regex:/^[a-zA-Z0-9\s]*$/'])
                    ->placeholder('Enter company name')
                    ->formatStateUsing(fn() => $this->getOwnerAssociation()?->name ? $this->getOwnerAssociation()->name : null),
                TextInput::make('ownerAssociation.trn_number')
                    ->label('TRN Number')
                    ->formatStateUsing(fn() => $this->getOwnerAssociation()?->trn_number ? $this->getOwnerAssociation()->trn_number : null)
                    ->placeholder('Enter TRN number'),
                TextInput::make('ownerAssociation.trade_license_number')
                    ->label('Trade License Number')
                    ->placeholder('Enter trade license number')
                    ->formatStateUsing(fn() => $this->getOwnerAssociation()?->trade_license_number ? $this->getOwnerAssociation()->trade_license_number : null),
                TextInput::make('ownerAssociation.address')
                    ->label('Address')
                    ->required()
                    ->placeholder('Enter complete address')
                    ->formatStateUsing(fn() => $this->getOwnerAssociation()?->address ? $this->getOwnerAssociation()->address : null),
                TextInput::make('ownerAssociation.bank_account_number')
                    ->label('Bank Account Number')
                    ->disabled()
                    ->placeholder('Enter account number')
                    ->formatStateUsing(fn() => $this->getOwnerAssociation()?->bank_account_number ? $this->getOwnerAssociation()->bank_account_number : null),
                TextInput::make('ownerAssociation.bank_account_holder_name')
                    ->label('Bank Account Holder Name')
                    ->placeholder('Enter bank account holder name')
                    ->formatStateUsing(fn() => $this->getOwnerAssociation()?->bank_account_holder_name ? $this->getOwnerAssociation()->bank_account_holder_name : null),
            ])->columns(2);
    }

    protected function getDocumentsSection(): Section
    {
        return Section::make('Documents')
            ->description('Upload and manage your documents')
            ->collapsible()
            ->collapsed()
            ->visible(fn() => auth()->user()->role->name === 'OA')
            ->icon('heroicon-o-document')
            ->schema([
                FileUpload::make('ownerAssociation.trn_certificate')
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                    ->maxSize(2048)
                    ->label('TRN Certificate')
                    ->formatStateUsing(fn() => $this->getOwnerAssociation()?->trn_certificate ? [$this->getOwnerAssociation()->trn_certificate] : null),
                FileUpload::make('ownerAssociation.trade_license')
                    ->disk('s3')
                    ->directory('dev')
                    ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                    ->openable(true)
                    ->downloadable(true)
                    ->required()
                    // ->disabledOn('edit')
                    ->maxSize(2048)
                    ->label('Trade License')
                    ->formatStateUsing(fn() => $this->getOwnerAssociation()?->trade_license ? [$this->getOwnerAssociation()->trade_license] : null),
                FileUpload::make('ownerAssociation.dubai_chamber_document')
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true)
                    ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                    ->maxSize(2048)
                    ->label('Other Document')
                    ->formatStateUsing(fn() => $this->getOwnerAssociation()?->dubai_chamber_document ? [$this->getOwnerAssociation()->dubai_chamber_document] : null),
                FileUpload::make('ownerAssociation.memorandum_of_association')
                    ->disk('s3')
                    ->required()
                    ->directory('dev')
                    ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                    ->maxSize(2048)
                    ->label('Memorandum of Association')
                    ->formatStateUsing(fn() => $this->getOwnerAssociation()?->memorandum_of_association ? [$this->getOwnerAssociation()->memorandum_of_association] : null),
                FileUpload::make('ownerAssociation.documents')
                    ->disk('s3')
                    ->directory('dev')
                    ->multiple()
                    ->maxFiles(5)
                    ->openable(true)
                    ->downloadable(true)
                    ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                    ->maxSize(2048)
                    ->label('Additional Documents')
                    ->columnSpanFull()
                    ->formatStateUsing(fn() => $this->getOwnerAssociation()?->documents ? [$this->getOwnerAssociation()->documents] : null),
            ])->columns(2);
    }

    protected function getSecuritySection(): Section
    {
        return Section::make('Security')
            ->description('Update your password')
            ->collapsed()
            ->collapsible()
            ->icon('heroicon-o-lock-closed')
            ->schema([
                TextInput::make('password')
                    ->rules($this->getPasswordValidationRules())
                    ->live()
                    ->revealable()
                    ->password()
                    ->label('New password'),
                TextInput::make('password_confirmation')
                    ->password()
                    ->revealable()
                    ->rules($this->getPasswordConfirmationRules())
                    ->reactive()
                    ->required(fn(Get $get) => !empty($get('password')))
                    ->label('Confirm new password'),
            ])->columns(2);
    }

    protected function getPhoneValidationRules(): array
    {
        return [
            function (Model $record) {
                return function (string $attribute, $value, Closure $fail) use ($record) {
                    if (DB::table('users')->whereNot('id', $record->id)->where('phone', '971' . $value)->count() > 0) {
                        $fail('The phone is already taken by a User.');
                    }
                };
            },
        ];
    }

    protected function getPasswordValidationRules(): array
    {
        return [
            function (Get $get) {
                return function (string $attribute, $value, Closure $fail) use ($get) {
                    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])[a-zA-Z\d\W]*$/', $value)) {
                        $fail('The field must contain at least one lowercase letter, one uppercase letter, one digit and one special character.');
                    }
                };
            },
        ];
    }

    protected function getPasswordConfirmationRules(): array
    {
        return [
            function (Get $get) {
                return function (string $attribute, $value, Closure $fail) use ($get) {
                    if ($value !== $get('password')) {
                        $fail('The Confirm password field must match new password.');
                    }
                };
            },
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $user = User::where("id",auth()->user()->id)->first();
            $roleName = $this->getRoleName($user);

            $this->updateUserData($user, $data);
            $this->updateOwnerAssociation($user, $data, $roleName);

            $this->getSavedNotification()->send();
            $this->redirectBasedOnUrl();
        } catch (Halt $exception) {
            return;
        }
    }

    protected function getRoleName(User $user): string
    {
        $roleName = Role::where('id', $user->role_id)->first()->name;
        return $roleName;
    }

    protected function updateUserData(User $user, array $data): void
    {
        $updateData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => '971' . $data['phone'],
            'profile_photo' => $data['profile_photo'] ?? $user->profile_photo,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        User::find($user->id)->update($updateData);
    }

    protected function updateOwnerAssociation(User $user, array $data, string $roleName): void
    {
        $ownerAssociation = $user->load('ownerAssociation')->first();
        if ($roleName === 'OA') {
            OwnerAssociation::find($user->owner_association_id)->update([
                'phone' => '971' . $data['phone'],
                'profile_photo' => $data['ownerAssociation']['profile_photo'] ?? $ownerAssociation->profile_photo,
                'trn_number' => $data['ownerAssociation']['trn_number'] ?? $ownerAssociation->trn_number,
                'trade_license_number' => $data['ownerAssociation']['trade_license_number'] ?? $ownerAssociation->trade_license_number,
                'address' => $data['ownerAssociation']['address'] ?? $ownerAssociation->address,
                'bank_account_number' => $data['ownerAssociation']['bank_account_number'] ?? $ownerAssociation->bank_account_number,
                'bank_account_holder_name' => $data['ownerAssociation']['bank_account_holder_name'] ?? $ownerAssociation->bank_account_holder_name,
            ]);
        }
    }

    protected function redirectBasedOnUrl(): void
    {
        $requestedUrl = request()->url();
        redirect(strpos($requestedUrl, 'admin') !== false ? '/app' : '/admin');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Profile updated successfully');
    }
}
