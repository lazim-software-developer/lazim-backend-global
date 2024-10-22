<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TechnicianVendorResource\Pages;
use App\Jobs\TechnicianAccountCreationJob;
use App\Models\Master\Role;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use DB;
use Exception;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Log;
use Str;

class TechnicianVendorResource extends Resource
{
    protected static ?string $model = TechnicianVendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('technician_id')
                    ->preload()
                    ->native(false)
                    ->required()
                    ->createOptionForm([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(50)
                                    ->placeholder('Enter the first name')
                                    ->string()
                                    ->live(onBlur: true)
                                    ->disabledOn('edit'),

                                TextInput::make('last_name')
                                    ->nullable()
                                    ->maxLength(50)
                                    ->placeholder('Enter the last name')
                                    ->string()
                                    ->live(onBlur: true)
                                    ->disabledOn('edit'),

                                TextInput::make('email')
                                    ->required()
                                    ->placeholder('user@example.com')
                                    ->email()
                                    ->unique('users', 'email')
                                    ->live(onBlur: true)
                                    ->rules([
                                        'required',
                                        'email',
                                        'min:6',
                                        'max:30',
                                        'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                                    ])
                                    ->disabledOn('edit'),

                                TextInput::make('phone')
                                    ->tel()
                                    ->live(onBlur: true)
                                    ->required()
                                    ->placeholder('5XXXXXXXX')
                                    ->unique('users', 'phone')
                                    ->prefix('+971')
                                    ->rules([
                                        'regex:/^(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/',
                                    ])
                                    ->disabledOn('edit'),
                            ]),
                    ])
                    ->createOptionModalHeading('Create Technician')
                    ->createOptionUsing(function (array $data) {
                        try {
                            Log::info('Create Technician Data:', $data);

                            if (empty($data['first_name']) || empty($data['email'])) {
                                throw new Exception('Required fields are missing');
                            }

                            $oaId = auth()->user()?->owner_association_id;
                            if (!$oaId) {
                                throw new Exception('Owner association ID not found');
                            }

                            // Use a different approach for role creation
                            $role = DB::transaction(function () use ($oaId) {
                                // First check if role exists
                                $existingRole = Role::where('name', 'Technician')
                                    ->where('owner_association_id', $oaId)
                                    ->first();

                                if ($existingRole) {
                                    return $existingRole;
                                }

                                // Create a temporary unique name for the role
                                $tempName = 'Technician_' . $oaId . '_' . Str::random(8);

                                // Create the role with temporary name
                                $newRole = Role::create([
                                    'name'                 => $tempName,
                                    'owner_association_id' => $oaId,
                                    'guard_name'           => 'web',
                                    'is_active'            => true,
                                ]);

                                // Update the name after creation
                                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                                $newRole->update(['name' => 'Technician']);
                                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                                return $newRole->fresh();
                            });

                            // Generate a random password for the user
                            $plainPassword = Str::random(12);

                            $userData = [
                                'first_name'           => $data['first_name'],
                                'last_name'            => $data['last_name'] ?? null,
                                'email'                => $data['email'],
                                'phone'                => $data['phone'] ?? null,
                                'email_verified'       => true,
                                'phone_verified'       => true,
                                'active'               => true,
                                'role_id'              => $role->id,
                                'owner_association_id' => $oaId,
                                'password'             => Hash::make($plainPassword),
                            ];

                            $user = User::create($userData);
                            TechnicianAccountCreationJob::dispatch($user, $plainPassword);

                            Log::info('Technician created successfully:', [
                                'user_id'              => $user->id,
                                'role_id'              => $role->id,
                                'owner_association_id' => $oaId,
                            ]);

                            return $user->id;

                        } catch (Exception $e) {
                            Log::error('Error creating technician:', [
                                'error'                => $e->getMessage(),
                                'trace'                => $e->getTraceAsString(),
                                'data'                 => $data,
                                'owner_association_id' => $oaId ?? null,
                            ]);

                            throw $e;
                        }
                    })
                    ->label('Technician')
                    ->placeholder('Select Technician')
                    ->options(function () {
                        return User::query()
                            ->whereHas('role', fn($query) =>
                                $query->where('name', 'Technician')
                            )
                            ->where('owner_association_id', auth()->user()->owner_association_id)
                            ->get()
                            ->mapWithKeys(fn($user) => [
                                $user->id => $user->first_name,
                            ])
                            ->toArray();
                    }),

                TextInput::make('technician_number')
                    ->placeholder('Enter Technician number')
                    ->unique('technician_vendors', 'technician_number'),

                Select::make('vendor_id')
                    ->label('Facility Manager')
                    ->native(false)
                    ->preload()
                    ->placeholder('Select Facility Manager')
                    ->options(Vendor::where('owner_association_id', auth()->user()->owner_association_id)
                            ->pluck('name', 'id')->toArray())
                    ->required(),

                TextInput::make('position')
                    ->maxLength(191),
                Toggle::make('active')
                    ->default(true)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('technician_number')
                    ->searchable()
                    ->default('NA'),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Technician')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Facility Manager')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('position')
                    ->default('NA')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTechnicianVendors::route('/'),
            'create' => Pages\CreateTechnicianVendor::route('/create'),
            'edit'   => Pages\EditTechnicianVendor::route('/{record}/edit'),
        ];
    }
}
