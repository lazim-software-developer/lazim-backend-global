<?php
declare(strict_types=1);
namespace App\Filament\Resources;

use Closure;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use App\Services\GenericHttpService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use App\Services\SessionCryptoService;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Password;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\OwnerAssociationResource\Pages;
use App\Filament\Resources\OwnerAssociationResource\RelationManagers\AccountcredentialsRelationManager;

class OwnerAssociationResource extends Resource
{
    protected static ?string $model                 = OwnerAssociation::class;
    protected static ?string $modelLabel            = 'Owner Association';
    protected static ?string $navigationIcon        = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = true;
    protected static bool $isScopedToTenant = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Information')
                    ->schema([
                        Grid::make([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ])->schema([
                            TextInput::make('name')
                                ->rules(['regex:/^[a-zA-Z0-9\s]*$/'])
                                ->required()
                                ->disabled(function (callable $get) {
                                    return Role::where('id', auth()->user()->role_id)
                                        ->first()->name != 'Admin' && DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                })
                                ->placeholder('User'),
                            TextInput::make('slug')
                                ->label('Slug')
                                ->required()
                                ->rules([
                                    'required',
                                    'string',
                                    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                                    'min:4',
                                    'max:30'
                                ])
                                ->validationMessages([
                                    'regex' => 'Slug format is Invalid. It can only accept Lowercase letters, Numbers and hyphen'
                                ])
                                ->unique('owner_associations', 'slug', ignoreRecord: true)
                                ->disabled(function (callable $get) {
                                    // Get the current operation (create or edit)
                                    $isCreate = !$get('id'); // if id exists, it's edit operation
                                
                                    // If it's create operation, return false (not disabled)
                                    if ($isCreate) {
                                        return false;
                                    }
                                
                                    // For edit operation, apply your existing logic
                                    return DB::table('owner_associations')
                                        ->where('slug', $get('slug'))
                                        ->exists();
                                }),
                            TextInput::make('trn_number')->label('VAT Number')
                                ->required()
                                ->unique(table: 'owner_associations', ignoreRecord: true)
                                // ->disabled()
                                ->placeholder('VAT Number'),
                            TextInput::make('phone')
                                ->rules([
                                    'regex:/^\+?[1-9]\d{1,14}$/',
                                    function (?Model $record) { // ?Model ka use karke $record nullable banaya
                                        return function (string $attribute, $value, Closure $fail) use ($record) {

                                            // Jab $record null ho (create mode me validation)
                                            if (!$record) {
                                                // Owner association me phone number ki uniqueness check karna
                                                if (DB::table('owner_associations')->where('phone', $value)->exists()) {
                                                    $fail('The phone is already taken by an OA.');
                                                }

                                                // Users table me phone number ki uniqueness check karna
                                                if (DB::table('users')->where('phone', $value)->exists()) {
                                                    $fail('The phone is already taken by a user.');
                                                }

                                                return; // Null record ke case me aage ki validation yahin ruk jaye
                                            }

                                            // Existing record ke liye validation
                                            if (DB::table('owner_associations')->whereNot('id', $record->id)->where('phone', $value)->count() > 0) {
                                                $fail('The phone is already taken by an OA.');
                                            }

                                            if (DB::table('owner_associations')->where('id', $record->id)->where('verified', 1)->count() > 0) {
                                                $role_id = Role::where('owner_association_id', $record->id)->where('name', 'OA')->first();

                                                $getuserecord = User::where('owner_association_id', $record->id)
                                                    ->where('role_id', $role_id?->id)
                                                    ->first()?->id;

                                                if (DB::table('users')->whereNot('id', $getuserecord)->where('phone', $value)->exists()) {
                                                    $fail('The phone is already taken by a user.');
                                                }
                                            } else {
                                                if (DB::table('users')->where('phone', $value)->exists()) {
                                                    $fail('The phone is already taken by a user.');
                                                }
                                            }
                                        };
                                    }
                                ])
                                ->required()
                                // ->live()
                                ->disabled(function (callable $get) {
                                    return Role::where('id', auth()->user()->role_id)
                                        ->first()->name != 'Admin' && DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                })
                                ->placeholder('Contact Number'),
                            TextInput::make('address')
                                ->required()
                                ->placeholder('Address'),
                            TextInput::make('email')
                                ->rules([
                                    'min:6',
                                    'max:30',
                                    'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                                    function (?Model $record) { // ?Model ka matlab $record nullable ho sakta hai
                                        return function (string $attribute, $value, Closure $fail) use ($record) {

                                            // Create mode ke liye validation (jab $record null ho)
                                            if (!$record) {
                                                // Owner association me email ki uniqueness check karna
                                                if (DB::table('owner_associations')->where('email', $value)->exists()) {
                                                    $fail('The email is already taken by an OA.');
                                                }

                                                // Users table me email ki uniqueness check karna
                                                if (DB::table('users')->where('email', $value)->exists()) {
                                                    $fail('The email is already taken by a USER.');
                                                }

                                                return; // Create mode ke liye validation yahin ruk jaye
                                            }

                                            // Update mode ke liye validation (jab $record null nahi hai)
                                            if (DB::table('owner_associations')->whereNot('id', $record->id)->where('email', $value)->count() > 0) {
                                                $fail('The email is already taken by an OA.');
                                            }

                                            // Verified owner association ke liye validation
                                            if (DB::table('owner_associations')->where('id', $record->id)->where('verified', 1)->count() > 0) {
                                                $role_id = Role::where('owner_association_id', $record->id)->where('name', 'OA')->first();

                                                $getuserecord = User::where('owner_association_id', $record->id)
                                                    ->where('role_id', $role_id?->id)
                                                    ->first()?->id;

                                                if (DB::table('users')->whereNot('id', $getuserecord)->where('email', $value)->exists()) {
                                                    $fail('The email is already taken by a USER.');
                                                }
                                            } else {
                                                // Non-verified owner association ke liye validation
                                                if (DB::table('users')->where('email', $value)->exists()) {
                                                    $fail('The email is already taken by a USER.');
                                                }
                                            }
                                        };
                                    }
                                ])
                                ->required()
                                // ->live()
                                ->disabled(function (callable $get) {
                                    return DB::table('owner_associations')
                                        ->where('phone', $get('phone'))
                                        ->where('verified', 1)
                                        ->exists();
                                })
                                ->placeholder('Email'),
                                TextInput::make('password')
                                ->password()
                                ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                ->dehydrated(fn ($state) => filled($state))
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->rule(Password::min(8)
                                    ->letters()
                                    ->mixedCase()
                                    ->numbers()
                                    ->symbols()
                                    ->uncompromised()
                                )
                                ->visible(fn (string $operation): bool => $operation === 'create'),
                            TextInput::make('bank_account_number')
                                ->label('Bank Account Number')
                                ->numeric()
                                ->disabled(function (callable $get) {
                                    if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                        return DB::table('owner_associations')
                                            ->where('email', $get('email'))
                                            ->where('verified', 1)
                                            ->exists();
                                    }
                                })
                                ->placeholder('Account Number'),
                                Hidden::make('verified_by')
                                ->default(auth()->user()?->id),
                                Hidden::make('created_by')
                                ->default(auth()->user()?->id),
                                Hidden::make('updated_by')
                                ->default(auth()->user()?->id),
                            Toggle::make('verified')
                                ->rules(['boolean'])
                                ->default(true)
                                ->hidden()
                                ->dehydrated(true),
                            Toggle::make('active')
                                ->label('Status')
                                ->rules(['boolean']),
                        ]),
                    ]),

                Section::make('Documents')
                    ->columns(3)
                    ->schema([
                        FileUpload::make('trn_certificate')
                            ->disk('s3')
                            ->directory('owner_associations/trn_certificate')
                            ->previewable(true)
                            ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                            ->maxSize(2048)
                            ->label('VAT Certificate')
                            ->required()
                            ->disabled(function (callable $get) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                }
                            }),
                        FileUpload::make('trade_license')
                            ->disk('s3')
                            ->directory('owner_associations/trade_license')
                            ->previewable(true)
                            ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                            ->maxSize(2048)
                            ->label('Trade License')
                            ->required()
                            ->disabled(function (callable $get) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                }
                            }),
                        FileUpload::make('dubai_chamber_document')
                            ->disk('s3')
                            ->directory('owner_associations/chamber_document')
                            ->previewable(true)
                            ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                            ->maxSize(2048)
                            ->label('Permit')
                            ->required()
                            ->disabled(function (callable $get) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                }
                            }),
                        FileUpload::make('memorandum_of_association')
                            ->disk('s3')
                            ->directory('owner_associations/memorandum_of_association')
                            ->previewable(true)
                            ->maxSize(2048)
                            ->rules('file|mimes:jpeg,jpg,png,pdf|max:2048')
                            ->label('Memorandum of Association')
                            ->required()
                            ->disabled(function (callable $get) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                }
                            }),
                        FileUpload::make('profile_photo')
                            ->disk('s3')
                            ->directory('owner_associations/logo')
                            ->previewable(true)
                            ->image()
                            ->maxSize(2048)
                            ->rules('file|mimes:jpeg,jpg,png|max:2048')
                            ->label('Logo')
                            ->required()
                            ->disabled(function (callable $get) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return DB::table('owner_associations')
                                        ->where('email', $get('email'))
                                        ->where('verified', 1)
                                        ->exists();
                                }
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->poll('60s')
            // ->query(OwnerAssociation::query())
            ->columns([
                // Tables\Columns\ImageColumn::make('profile_photo')->width(50)->height(50)
                //     ->searchable()
                //     ->default('NA')
                //     ->limit(50),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('trn_number')
                    ->label('VAT Certificate')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('address')
                    ->default('NA')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_account_number')
                    ->default('NA')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            // ->data(function () {
            //     return static::fetchApiData();
            // })
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->hasRole('Admin')),
                Action::make('delete')
                    ->button()
                    ->visible(fn () => auth()->user()->hasRole('Admin'))
                    ->action(function ($record,) {
                        $record->delete();

                        Notification::make()
                            ->title('Owner Association Deleted Successfully')
                            ->success()
                            ->send()
                            ->duration('4000');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure you want to delete this ?')
                    ->modalButton('Delete'),
            ])

            ->bulkActions([
                ExportBulkAction::make()
                ->exports([
                    ExcelExport::make()
                        ->withColumns([
                            Column::make('created_by')
                            ->heading('Created By')
                            ->formatStateUsing(fn ($record) => 
                                $record->CreatedBy->first_name.' '.$record->CreatedBy->last_name ?? 'N/A'
                            ), 
                            Column::make('name')
                                ->heading('Name'),
                            Column::make('phone')
                                ->heading('Phone Number'),
                            Column::make('email')
                                ->heading('Email'),
                            Column::make('trn_number')
                                ->heading('TRN Number'),
                            Column::make('address')
                            ->heading('Address'),
                            Column::make('bank_account_number')
                                ->heading('Bank Account Number'),      
                            // Formatted date with custom accessor
                            Column::make('created_at')
                                ->heading('Created Date')
                                ->formatStateUsing(fn ($state) => 
                                    $state ? $state->format('d/m/Y') : ''
                                ),
                                Column::make('active')
                                ->heading('Status')
                                ->formatStateUsing(fn ($record) => 
                                    $record->active == 1
                                        ? 'Active' 
                                        : 'Inactive'
                                ),
                                
                            // Created by user info
                            // Column::make('created_by_name')
                            //     ->heading('Created By')
                            //     ->formatStateUsing(fn ($record) => 
                            //         $record->createdBy->name ?? 'System'
                            //     ),
                        ])
                        ->withFilename(date('Y-m-d') . '-owner-association-report')
                        ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                ]),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->hasRole('Admin')),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            AccountcredentialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOwnerAssociations::route('/'),
            'create' => Pages\CreateOwnerAssociation::route('/create'),
            'edit'   => Pages\EditOwnerAssociation::route('/{record}/edit'),
        ];
    }
    
}
