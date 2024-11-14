<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuildingsRelationManagerResource\RelationManagers\BuildingsRelationManager;
use App\Filament\Resources\FacilityManagerResource\Pages;
use App\Filament\Resources\FacilityManagerResource\RelationManagers\ComplianceDocumentsRelationManager;
use App\Filament\Resources\FacilityManagerResource\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\FacilityManagerResource\RelationManagers\EscalationMatrixRelationManager;
use App\Jobs\ApprovedFMJob;
use App\Jobs\RejectedFMJob;
use App\Models\Accounting\SubCategory;
use App\Models\Master\Service;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Hash;
use Illuminate\Database\Eloquent\Builder;
use Str;

class FacilityManagerResource extends Resource
{
    protected static ?string $model          = Vendor::class;
    protected static ?string $modelLabel     = 'Facility Manager';
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort    = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)
                    ->schema([
                        Section::make('Basic Information')
                            ->description('Enter the primary details for the facility manager.')
                            ->icon('heroicon-o-identification')
                            ->collapsible()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('owner_association_id')
                                            ->label('Property Manager')
                                            ->hidden()
                                            ->default(function () {
                                                $pmId = auth()->user()->owner_association_id;
                                                return OwnerAssociation::where('id', $pmId)->pluck('name', 'id')->first();
                                            })
                                            ->disabled(),
                                        TextInput::make('name')
                                            ->label('Company Name')
                                            ->required()
                                            ->placeholder('Enter company name')
                                            ->maxLength(100),
                                        TextInput::make('user.email')
                                            ->label('Email Address')
                                            ->email()
                                            ->required()
                                            ->unique(
                                                table: User::class,
                                                column: 'email',
                                                ignorable: fn($record) => $record?->user
                                            )
                                            ->disabledOn('edit')
                                            ->placeholder('company@example.com'),
                                        TextInput::make('user.phone')
                                            ->label('Phone Number')
                                            ->tel()
                                            ->required()
                                            ->prefix('+971')
                                            ->unique(
                                                table: User::class,
                                                column: 'phone',
                                                ignorable: fn($record) => $record?->user
                                            )
                                            ->disabledOn('edit')
                                            ->placeholder('5XXXXXXXX'),
                                    ]),
                            ])->columnSpan(2),

                        Section::make('Company Details')
                            ->description('Provide detailed information about the company.')
                            ->icon('heroicon-o-building-office')
                            ->collapsible()
                            ->schema([

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('landline_number')
                                            ->label('Landline Number')
                                            ->required()
                                            ->tel(),
                                        TextInput::make('tl_number')
                                            ->label('Trade License Number')
                                            ->required()
                                            ->numeric()
                                            ->unique(Vendor::class, 'tl_number', ignoreRecord: true),
                                        // TextInput::make('fax')
                                        //     ->label('Fax Number'),
                                    ]),
                                TextInput::make('address_line_1')
                                    ->label('Company Address')
                                    ->required()
                                    ->placeholder('Enter complete address'),
                                TextInput::make('website')
                                    ->label('Website')
                                    ->url()
                                    ->placeholder('https://example.com'),

                                Grid::make(2)
                                    ->schema([
                                        DatePicker::make('tl_expiry')
                                            ->label('Trade License Expiry')
                                            ->required(),
                                        DatePicker::make('risk_policy_expiry')
                                            ->label('Risk Policy Expiry')
                                            ->required(),
                                    ]),
                            ])->columnSpan(1),
                    ]),

                Section::make('Services')
                    ->description('Services provided by the Facility Manager.')
                    ->icon('heroicon-o-list-bullet')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('subcategory_id')
                                    ->options(SubCategory::all()->pluck('name', 'id'))
                                    ->live()
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Select Sub-Category')
                                    ->label('Sub Category')
                                    ->preload()
                                    ->afterStateUpdated(fn(Set $set) => $set('service_id', null)),
                                Select::make('service_id')
                                    ->label('Service')
                                    ->live()
                                    ->preload()
                                    ->required()
                                    ->searchable()
                                    ->options(function (callable $get) {
                                        return Service::where('type', 'vendor_service')
                                            ->where('subcategory_id', $get('subcategory_id'))->pluck('name', 'id');
                                    })
                                    ->placeholder('Select Service'),

                            ]),

                    ]),

                Section::make('Manager Information')
                    ->description('Details of the authorized manager.')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('managers.0.name')
                                    ->label('Manager Name')
                                    ->placeholder('Full name')
                                    ->live()
                                    ->required(function ($get) {
                                        return !empty($get('managers.0.email')) ||
                                        !empty($get('managers.0.phone'));
                                    }),
                                TextInput::make('managers.0.email')
                                    ->label('Manager Email')
                                    ->email()
                                    ->placeholder('manager@company.com')
                                    ->live()
                                    ->required(function ($get) {
                                        return !empty($get('managers.0.name')) ||
                                        !empty($get('managers.0.phone'));
                                    }),
                                TextInput::make('managers.0.phone')
                                    ->label('Manager Phone')
                                    ->tel()
                                    ->placeholder('5XXXXXXXX')
                                    ->live()
                                    ->required(function ($get) {
                                        return !empty($get('managers.0.name')) ||
                                        !empty($get('managers.0.email'));
                                    }),
                            ]),

                    ]),

                Section::make('Approval Status')
                    ->description('Update the approval status of the facility manager.')
                    ->icon('heroicon-o-check-circle')
                    ->collapsible()
                    ->schema([
                        Select::make('status')
                            ->label('Current Status')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->live()
                            ->visibleOn('edit'),

                        Textarea::make('remarks')
                            ->maxLength(250)
                            ->rows(5)
                            ->required()
                            ->visible(function (callable $get) {
                                return $get('status') === 'rejected';
                            }),
                    ])
                    ->visibleOn('edit')
                    ->afterStateUpdated(function ($state, $livewire) {
                        $user     = $livewire->record->user;
                        $email    = $user->email;
                        $password = Str::random(12);

                        if ($state['status'] === 'rejected' && !empty($state['remarks'])) {
                            // Log the remarks before dispatching
                            \Log::info('Remarks before dispatch:', ['remarks' => $state['remarks']]);
                            RejectedFMJob::dispatch($user, $password, $email, $state['remarks']);
                        } elseif ($state['status'] === 'approved') {
                            // Update user password
                            $user->password = Hash::make($password);
                            $user->save();

                            // Dispatch approved email job
                            ApprovedFMJob::dispatch($user, $password, $email);
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->where('owner_association_id', auth()->user()->ownerAssociation[0]->id);
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Company Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tl_number')
                    ->label('Trade License')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tl_expiry')
                    ->label('License Expiry')
                    ->date(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->icons([
                        'heroicon-o-x-circle'     => 'rejected',
                        'heroicon-o-clock'        => fn($state)        => $state === null || $state === 'NA',
                        'heroicon-o-check-circle' => 'approved',
                    ])
                    ->colors([
                        'success' => 'approved',
                        'danger'  => 'rejected',
                        'warning' => fn($state) => $state === null || $state === 'NA',
                    ])
                    ->formatStateUsing(fn($state) => $state === null || $state === 'NA' ? 'Pending' : ucfirst($state))
                    ->default('NA'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'approved' => 'Approved',
                        'pending'  => 'Pending',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                // Tables\Actions\DeleteAction::make()
                //     ->iconButton(),
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
            DocumentsRelationManager::class,
            BuildingsRelationManager::class,
            ComplianceDocumentsRelationManager::class,
            // ServicesRelationManager::class,
            EscalationMatrixRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFacilityManagers::route('/'),
            'create' => Pages\CreateFacilityManager::route('/create'),
            'edit'   => Pages\EditFacilityManager::route('/{record}/edit'),
        ];
    }
}
