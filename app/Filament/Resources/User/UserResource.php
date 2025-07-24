<?php

namespace App\Filament\Resources\User;

use App\Models\Scopes\Searchable;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use BezhanSalleh\FilamentShield\Support\Utils;
use App\Filament\Resources\Shield\RoleResource;
use App\Filament\Resources\User\UserResource\Pages;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Forms\Components\Actions\Action as FormAction;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon       = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel      = 'Owner';
    protected static ?string $navigationGroup      = 'Flat Management';
    protected static ?string $modelLabel = 'Users';
    protected static bool $shouldRegisterNavigation = false;

    protected static $permissionsCollection;
    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 1,
                'lg' => 2
            ])
                ->schema([
                    TextInput::make('first_name')
                        ->rules(['max:50', 'string'])
                        ->required()->disabledOn('edit')
                        ->placeholder('First Name'),

                    TextInput::make('last_name')
                        ->rules(['max:50', 'string'])
                        ->nullable()->disabledOn('edit')
                        ->placeholder('Last Name'),

                    TextInput::make('email')
                        ->rules(['min:6', 'max:30', 'regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'])
                        ->required()->disabledOn('edit')
                        ->unique(
                            'users',
                            'email',
                            fn(?Model $record) => $record
                        )
                        ->email()
                        ->placeholder('Email'),

                    TextInput::make('phone')
                        ->rules(['regex:/^(50|51|52|55|56|58|02|03|04|06|07|09)\d{7}$/'])
                        // ->required()
                        ->disabledOn('edit')
                        ->prefix('971')
                        ->unique(
                            'users',
                            'phone',
                            fn(?Model $record) => $record
                        )
                        ->placeholder('Phone'),

                    // TextInput::make('lazim_id')
                    //     ->rules(['max:50', 'string'])
                    //     ->required()
                    //     ->unique(
                    //         'users',
                    //         'lazim_id',
                    //         fn (?Model $record) => $record
                    //     )
                    //     ->placeholder('Lazim Id'),
                    Select::make('roles')
                        ->relationship('roles', 'name')
                        // ->multiple()
                        ->options(function () {
                            if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                return Role::where('name', 'Admin')->pluck('name', 'id');
                            } else {
                                $oaId = auth()->user()?->owner_association_id;
                                return Role::whereNotIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'OA', 'Owner', 'Managing Director', 'Vendor'])
                                    ->where('owner_association_id', $oaId)
                                    ->pluck('name', 'id');
                            }
                        })
                        ->preload()->required()
                        ->searchable(),
                    // Select::make('role_id')
                    // ->label('Role')
                    //     ->rules(['exists:roles,id'])
                    //     ->required()->disabledOn('edit')
                    //     ->options(function () {
                    //         $oaId = auth()->user()?->owner_association_id;
                    //         return Role::whereNotIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'OA', 'Owner', 'Managing Director', 'Vendor'])
                    //             ->pluck('name', 'id');
                    //     })->searchable()->preload()
                    //     ->placeholder('Role'),
                    // Toggle::make('phone_verified')
                    //     ->rules(['boolean'])
                    //     ->hidden()
                    //     ->nullable(),
                    Toggle::make('active')
                        // ->rules(['boolean'])
                        // ->default(true)
                        ->nullable(),
                    Hidden::make('guard_name')
                        ->label(__('filament-shield::filament-shield.field.guard_name'))
                        ->default(is_null(Auth::guard()->getName()) ? 'web' : 'web') //Todo 
                        ->nullable(),
                    Tabs::make('Permissions')
                        ->columnSpanFull()
                        ->tabs([
                            Tab::make(__('filament-shield::filament-shield.resources'))
                                ->visible(fn(): bool => (bool) Utils::isResourceEntityEnabled())
                                ->badge(static::getResourceTabBadgeCount()) // 🔴 You must define this method below

                                ->schema([
                                    Grid::make()
                                        ->schema(fn(Get $get) => static::getResourceEntitiesSchema($get)) // 🔴 Define this too
                                        ->columns(FilamentShieldPlugin::get()->getGridColumns())

                                ]),

                            Tabs\Tab::make(__('filament-shield::filament-shield.pages'))
                                ->visible(fn(): bool => (bool) Utils::isPageEntityEnabled() && (count(FilamentShield::getPages()) > 0 ? true : false))
                                ->badge(count(static::getPageOptions()))
                                ->schema([
                                    CheckboxList::make('pages_tab')
                                        ->label('')
                                        ->options(fn(): array => static::getPageOptions())
                                        ->searchable()
                                        ->live()
                                        ->afterStateHydrated(function (Component $component, $livewire, string $operation, ?Model $record, Set $set) {
                                            static::setPermissionStateForRecordPermissions(
                                                component: $component,
                                                operation: $operation,
                                                permissions: static::getPageOptions(),
                                                record: $record
                                            );
                                            static::toggleSelectAllViaEntities($livewire, $set);
                                        })
                                        ->afterStateUpdated(fn($livewire, Set $set) => static::toggleSelectAllViaEntities($livewire, $set))
                                        ->selectAllAction(fn(FormAction $action, Component $component, $livewire, Set $set) => static::bulkToggleableAction(
                                            action: $action,
                                            component: $component,
                                            livewire: $livewire,
                                            set: $set
                                        ))
                                        ->deselectAllAction(fn(FormAction $action, Component $component, $livewire, Set $set) => static::bulkToggleableAction(
                                            action: $action,
                                            component: $component,
                                            livewire: $livewire,
                                            set: $set,
                                            resetState: true
                                        ))
                                        ->dehydrated(fn($state) => blank($state) ? false : true)
                                        ->bulkToggleable()
                                        ->gridDirection('row')
                                        ->columns(FilamentShieldPlugin::get()->getCheckboxListColumns())
                                        ->columnSpan(FilamentShieldPlugin::get()->getCheckboxListColumnSpan()),
                                ]),



                            Tab::make(__('filament-shield::filament-shield.widgets'))
                                ->visible(fn(): bool => (bool) Utils::isWidgetEntityEnabled() && (count(FilamentShield::getWidgets()) > 0))
                                ->badge(count(static::getWidgetOptions()))
                                ->schema([
                                    CheckboxList::make('widgets_tab')
                                        ->label('')
                                        ->options(fn(): array => static::getWidgetOptions())
                                        ->searchable()
                                        ->live()
                                        ->afterStateHydrated(function (Component $component, $livewire, string $operation, ?Model $record, Set $set) {
                                            static::setPermissionStateForRecordPermissions(
                                                component: $component,
                                                operation: $operation,
                                                permissions: static::getWidgetOptions(),
                                                record: $record
                                            );
                                            static::toggleSelectAllViaEntities($livewire,  $set);
                                        })
                                        ->afterStateUpdated(fn($livewire, Set $set) => static::toggleSelectAllViaEntities($livewire, $set))
                                        ->selectAllAction(fn(FormAction $action, Component $component, $livewire, Set $set) => static::bulkToggleableAction(
                                            action: $action,
                                            component: $component,
                                            livewire: $livewire,
                                            set: $set
                                        ))
                                        ->deselectAllAction(fn(FormAction $action, Component $component, $livewire, Set $set) => static::bulkToggleableAction(
                                            action: $action,
                                            component: $component,
                                            livewire: $livewire,
                                            set: $set,
                                            resetState: true
                                        ))
                                        ->dehydrated(fn($state) => blank($state) ? false : true)
                                        ->bulkToggleable()
                                        ->gridDirection('row')
                                        ->columns(FilamentShieldPlugin::get()->getCheckboxListColumns())
                                        ->columnSpan(FilamentShieldPlugin::get()->getCheckboxListColumnSpan()),
                                ]),

                        ])

                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            $roles = Role::where('name', 'Admin')->pluck('id');
        } else {
            $roles = Role::whereNotIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'OA', 'Owner', 'Managing Director', 'Vendor'])->pluck('id');
        }
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('owner_association_id', auth()->user()?->owner_association_id)->whereIn('role_id', $roles)->where('id', '!=', auth()->user()->id))
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable()
                    ->limit(15),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->default('NA'),
                Tables\Columns\ToggleColumn::make('active'),
                // Tables\Columns\TextColumn::make('lazim_id')
                //     ->toggleable()
                //     ->searchable()
                //     ->limit(50),
                Tables\Columns\TextColumn::make('role.name')
                    ->sortable()
                    ->toggleable()->searchable()
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role_id')
                    ->options(function () {
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                            return Role::all()->pluck('name', 'id');
                        } else {
                            return Role::where('owner_association_id', auth()->user()?->owner_association_id)
                                ->pluck('name', 'id');
                        }
                    })
                    ->label('Role')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // UserResource\RelationManagers\AttendancesRelationManager::class,
            // UserResource\RelationManagers\VendorsRelationManager::class,

            // UserResource\RelationManagers\BuildingPocsRelationManager::class,
            // UserResource\RelationManagers\DocumentsRelationManager::class,
            // UserResource\RelationManagers\ComplaintsRelationManager::class,
            // UserResource\RelationManagers\FacilityBookingsRelationManager::class,
            // UserResource\RelationManagers\FlatTenantsRelationManager::class,
            // UserResource\RelationManagers\FlatVisitorsRelationManager::class,
            // UserResource\RelationManagers\FlatsRelationManager::class,
        ];
    }
    public static function getResourcePermissionOptionsList(): array
    {
        return collect(FilamentShield::getResources())
            ->flatMap(fn($entity) => static::getResourcePermissionOptions($entity))
            ->toArray();
    }

    public static function getResourcePermissionOptions(array $entity): array
    {
        $resourcesWithoutCreate = config('role_resource_permission.resourcesWithoutCreate');
        $resourcesWithoutEdit = config('role_resource_permission.resourcesWithoutEdit');
        $resourcesWithoutView = config('role_resource_permission.resourcesWithoutView');

        $permissionPrefixes = collect(Utils::getResourcePermissionPrefixes($entity['fqcn']));

        return $permissionPrefixes->flatMap(function ($permission) use ($entity, $resourcesWithoutCreate, $resourcesWithoutEdit, $resourcesWithoutView) {

            if ($permission === 'create' && in_array(class_basename($entity['fqcn']), $resourcesWithoutCreate)) {
                return [];
            }

            if (($permission === 'edit' || $permission === 'update') && in_array(class_basename($entity['fqcn']), $resourcesWithoutEdit)) {
                return [];
            }

            if ($permission === 'view' && in_array(class_basename($entity['fqcn']), $resourcesWithoutView)) {
                return [];
            }

            if ($permission === 'view_any') {
                return [
                    $permission . '_' . $entity['resource'] => 'Show Resource',
                ];
            }

            return [
                $permission . '_' . $entity['resource'] => FilamentShield::getLocalizedResourcePermissionLabel($permission),
            ];
        })->toArray();
    }

    public static function getCheckBoxListComponentForResource(array $entity): Component
    {
        $permissionsArray = static::getResourcePermissionOptions($entity);

        return CheckboxList::make($entity['resource'])
            ->label('')
            ->options(fn(): array => $permissionsArray)
            ->live()
            ->afterStateHydrated(function (Component $component, $livewire, string $operation, ?Model $record, Set $set) use ($permissionsArray) {
                static::setPermissionStateForRecordPermissions(
                    component: $component,
                    operation: $operation,
                    permissions: $permissionsArray,
                    record: $record
                );

                static::toggleSelectAllViaEntities($livewire, $set);
            })
            ->afterStateUpdated(fn($livewire, Set $set) => static::toggleSelectAllViaEntities($livewire, $set))
            ->selectAllAction(fn(FormAction $action, Component $component, $livewire, Set $set) => static::bulkToggleableAction($action, $component, $livewire, $set))
            ->deselectAllAction(fn(FormAction $action, Component $component, $livewire, Set $set) => static::bulkToggleableAction($action, $component, $livewire, $set, true))
            ->dehydrated(fn($state) => ! blank($state))
            ->bulkToggleable()
            ->gridDirection('row')
            ->columns(FilamentShieldPlugin::get()->getResourceCheckboxListColumns());
    }

    public static function getResourceTabBadgeCount(): ?int
    {
        return collect(FilamentShield::getResources())
            ->map(fn($resource) => count(static::getResourcePermissionOptions($resource)))
            ->sum();
    }

    public static function getWidgetOptions(): array
    {
        return collect(FilamentShield::getWidgets())
            ->flatMap(fn($widget) => [
                $widget['permission'] => FilamentShield::getLocalizedWidgetLabel($widget['class']),
            ])
            ->toArray();
    }

    public static function getPageOptions(): array
    {
        return collect(FilamentShield::getPages())
            ->flatMap(fn($page) => [
                $page['permission'] => FilamentShield::getLocalizedPageLabel($page['class']),
            ])
            ->toArray();
    }
    public static function setPermissionStateForRecordPermissions(
        Component $component,
        string $operation,
        array $permissions,
        ?Model $record
    ): void {
        if (in_array($operation, ['edit', 'view']) && $record) {
            $component->state(
                collect($permissions)
                    ->filter(fn($label, $key) => $record->hasPermissionTo($key))
                    ->keys()
                    ->toArray()
            );
        }
    }
    // public static function toggleSelectAllViaEntities($livewire, Set $set): void
    // {
    //     // For example, select all page permissions:
    //     $set('pages_tab', array_keys(static::getPageOptions()));
    // }



    // public static function bulkToggleableAction(
    //     FormAction $action,
    //     Component $component,
    //     $livewire,
    //     Set $set,
    //     bool $resetState = false
    // ): FormAction {
    //     return $action->action(function () use ($component, $set, $resetState) {
    //         $state = $component->getState();

    //         $newState = $resetState
    //             ? []
    //             : array_keys($component->getOptions() ?? []);

    //         $component->state($newState);
    //         $set($component->getName(), $newState);
    //     });
    // }

    public static function toggleSelectAllViaEntities($livewire, Set $set): void
    {
        $entitiesStates = collect($livewire->form->getFlatComponents())
            ->reduce(function ($counts, $component) {
                if ($component instanceof CheckboxList) {
                    $counts[$component->getName()] = count(array_keys($component->getOptions())) == count(collect($component->getState())->values()->unique()->toArray());
                }

                return $counts;
            }, collect())
            ->values();
        if ($entitiesStates->containsStrict(false)) {
            $set('select_all', false);
        } else {
            $set('select_all', true);
        }
    }

    public static function bulkToggleableAction(FormAction $action, Component $component, $livewire, Set $set, bool $resetState = false): void
    {
        $action
            ->livewireClickHandlerEnabled(true)
            ->action(function () use ($component, $livewire, $set, $resetState) {
                /** @phpstan-ignore-next-line */
                $component->state($resetState ? [] : array_keys($component->getOptions()));
                static::toggleSelectAllViaEntities($livewire, $set);
            });
    }

    public static function getUserPageOptions(): array
    {
        return collect(FilamentShield::getPages())
            ->flatMap(fn($page) => [
                $page['permission'] => FilamentShield::getLocalizedPageLabel($page['class']),
            ])
            ->toArray();
    }

    public static function getUserWidgetOptions(): array
    {
        return collect(FilamentShield::getWidgets())
            ->flatMap(fn($widget) => [
                $widget['permission'] => FilamentShield::getLocalizedWidgetLabel($widget['class']),
            ])
            ->toArray();
    }
    public static function getResourceEntitiesSchema(callable $get): ?array
    {
        static::$permissionsCollection = static::$permissionsCollection ?: Utils::getPermissionModel()::all();
        $searchTerm = str_replace(' ', '', strtolower($get('search', '')));
        $resources = collect(FilamentShield::getResources());
        // dd($resources);
        if ($searchTerm) {
            $resources = $resources->sortBy(function ($entity) use ($searchTerm) {
                $fqcnValue = str_replace(' ', '', strtolower(FilamentShield::getLocalizedResourceLabel($entity['fqcn'])));
                return str_contains($fqcnValue, $searchTerm) ? 0 : 1;
            });
        }

        return $resources->map(function ($entity) {
            // dd($entity, FilamentShield::getLocalizedResourceLabel($entity['fqcn']));
            return Section::make(FilamentShield::getLocalizedResourceLabel($entity['fqcn']))
                ->description(fn() => new HtmlString('<span style="word-break: break-word;">'))
                ->compact()
                ->schema([
                    static::getCheckBoxListComponentForResource($entity),
                ])
                ->columnSpan(FilamentShieldPlugin::get()->getSectionColumnSpan());
        })->toArray();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
