<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Jobs\BuildingSecurity;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use App\Models\Building\BuildingPoc;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use App\Jobs\VendorAccountCreationJob;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Resources\RelationManagers\RelationManager;

class BuildingPocsRelationManager extends RelationManager
{
    protected static string $relationship = 'buildingPocs';
    protected static ?string $modelLabel = 'Security';
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Security';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 1,
                ])->schema([

                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->relationship('user', 'first_name')
                        ->reactive()
                        ->unique(
                            'building_pocs',
                            'user_id',
                        )
                        ->options(function () {
                            return User::where('role_id', 12)
                                ->select('id', 'first_name')
                                ->pluck('first_name', 'id')
                                ->toArray();
                        })
                        ->createOptionForm([
                            TextInput::make('first_name')
                                ->required(),
                            TextInput::make('last_name')
                                ->label('Last Name'),
                            TextInput::make('email')
                                ->rules(['min:6', 'max:30', 'regex:/^[a-z0-9.]+@[a-z]+\.[a-z]{2,}$/'])
                                ->required()
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->rules(['regex:/^(\+971)(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/'])
                                ->required()
                                ->maxLength(255),
                            FileUpload::make('profile_photo')
                                ->disk('s3')
                                ->directory('dev')
                                ->image()
                                ->label('Profile Photo'),
                            Toggle::make('active')
                                ->rules(['boolean'])
                                ->default(true),
                            Hidden::make('role_id')
                                ->default(12),
                            Hidden::make('owner_association_id')
                                ->default(auth()->user()->owner_association_id),

                        ])
                        ->required()
                        ->preload()
                        ->searchable()
                        ->placeholder('User'),
                    Hidden::make('role_name')
                        ->default('security'),
                    Hidden::make('escalation_level')
                        ->default('1'),
                    Hidden::make('active')
                        ->default(true),
                    Hidden::make('building_id')
                        ->default(function (RelationManager $livewire) {
                            return $livewire->ownerRecord->id;
                        }),
                    Toggle::make('emergency_contact')
                        ->rules(['boolean'])
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                    ->limit(50)
                    ->label('Building Name'),
                Tables\Columns\TextColumn::make('user.first_name')->label('Name')
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.email')->label('Email')
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.phone')->label('Phone')
                    ->limit(50),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('New Security')
                    ->visible(fn (RelationManager $livewire) => BuildingPoc::where('building_id', $livewire->ownerRecord->id)->where('active', 1)->count() == 0)
                    ->button()
                    ->form([
                        TextInput::make('first_name')
                            ->required(),
                        TextInput::make('last_name')
                            ->label('Last Name'),
                        TextInput::make('email')
                            ->rules(['min:6', 'max:30', 'regex:/^[a-z0-9.]+@[a-z]+\.[a-z]{2,}$/', function () {
                                return function (string $attribute, $value, Closure $fail) {
                                    if (DB::table('users')->where('email', $value)->count() > 0) {
                                        $fail('The email is already taken by a User.');
                                    }
                                };
                            },])
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->rules(['regex:/^(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/', function () {
                                return function (string $attribute, $value, Closure $fail) {
                                    if (DB::table('users')->where('phone', '971' . $value)->count() > 0) {
                                        $fail('The phone is already taken by a User.');
                                    }
                                };
                            },])
                            ->prefix('971')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('profile_photo')
                            ->disk('s3')
                            ->directory('dev')
                            ->image()
                            ->label('Profile Photo'),
                        Toggle::make('active')
                            ->rules(['boolean'])
                            ->default(true),
                        //
                        Hidden::make('building_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                    ])
                    ->action(function (array $data): void {

                        $user = User::create([
                            'first_name' => $data['first_name'],
                            'last_name' => $data['last_name'],
                            'email' => $data['email'],
                            'phone' => '971'.$data['phone'],
                            'profile_photo' => $data['profile_photo'],
                            'active' => $data['active'],
                            'role_id' => 12,
                            'owner_association_id' => auth()->user()->owner_association_id,
                            'email_verified' => 1,
                            'phone_verified' => 1

                        ]);

                        $security = BuildingPoc::create([
                            'user_id' => $user->id,
                            'role_name' => 'security',
                            'escalation_level' => 1,
                            'active' => true,
                            'building_id' => $data['building_id'],
                            'emergency_contact' => true,

                        ]);
                        if ($user && $security) {
                            $password = Str::random(12);
                            $user->password = Hash::make($password);
                            $user->save();
                            BuildingSecurity::dispatch($user, $password);
                        }
                    })
                    ->slideOver()
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Action::make('Edit')
                    ->button()
                    ->form([
                        TextInput::make('first_name')
                            ->required(),
                        TextInput::make('last_name')
                            ->label('Last Name'),
                        TextInput::make('email')
                            ->rules(['min:6', 'max:30', 'regex:/^[a-z0-9.]+@[a-z]+\.[a-z]{2,}$/', function (Model $record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (DB::table('users')->whereNot('id', $record->user_id)->where('email', $value)->count() > 0) {
                                        $fail('The email is already taken by a User.');
                                    }
                                };
                            },])
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->rules(['regex:/^(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/', function (Model $record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (DB::table('users')->whereNot('id', $record->user_id)->where('phone', '971'.$value)->count() > 0) {
                                        $fail('The phone is already taken by a User.');
                                    }
                                };
                            },])
                            ->prefix('971')
                            ->required()
                            ->maxLength(255),
                        FileUpload::make('profile_photo')
                            ->disk('s3')
                            ->directory('dev')
                            ->image()
                            ->label('Profile Photo'),
                        Toggle::make('active')
                            ->rules(['boolean', function (Model $record) {
                                return function (string $attribute, $value, Closure $fail) use ($record) {
                                    if (!$record->active) {
                                        if (BuildingPoc::where('building_id', $record->building_id)->where('active', true)->exists()) {
                                            $fail('A Active Security already exists for this building.');
                                        }
                                    }
                                };
                            }])
                            ->default(true),
                        //
                        Hidden::make('building_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                    ])
                    ->fillForm(fn (BuildingPoc $userId): array => [
                        $record = User::where('id', $userId->user_id)->first(),
                        'first_name' => $record->first_name,
                        'last_name' => $record->last_name,
                        'email' => $record->email,
                        'phone' => substr($record->phone, 3),
                        'profile_photo' => $record->profile_photo,
                        'active' => $userId->active, //Active fiil from buildingPoc
                    ])
                    ->action(function (BuildingPoc $userId, array $data): void {
                        $record = User::where('id', $userId->user_id)->first();
                        if ($record->email != $data['email']) {
                            $password = Str::random(12);
                            $record->password = Hash::make($password);
                            $record->save();
                            BuildingSecurity::dispatch($record, $password);
                        }
                        $record->first_name = $data['first_name'];
                        $record->last_name = $data['last_name'];
                        $record->email = $data['email'];
                        $record->phone = '971'.$data['phone'];
                        $record->profile_photo = $data['profile_photo'];
                        $record->save();
                        //active of this BuildingPoc
                        if (BuildingPoc::where('building_id', $data['building_id'])->where('active', true)->exists()) {
                        }
                        $userId->active = $data['active'];
                        $userId->save();
                    })
                    ->slideOver()

                //Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }
}
