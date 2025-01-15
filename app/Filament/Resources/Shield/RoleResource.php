<?php

namespace App\Filament\Resources\Shield;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ToggleColumn;
use BezhanSalleh\FilamentShield\Support\Utils;
use App\Filament\Resources\Shield\RoleResource\Pages;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Forms\Components\Actions\Action as FormAction;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class RoleResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Role::class;
    protected static ?string $recordTitleAttribute = 'name';

    protected static $permissionsCollection;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('filament-shield::filament-shield.field.name'))
                                    ->rules([
                                        function (?Model $record) {
                                            return function (string $attribute, $value, Closure $fail) use($record) {
                                                if (DB::table('roles')->whereNot('id',$record?->id)->where('name', $value)
                                                ->where('owner_association_id', auth()->user()?->owner_association_id)
                                                ->count() > 0)
                                                {
                                                    $fail('This role is already exists.');
                                                }
                                            };
                                        }

                                    ])
                                    ->required()
                                    ->minLength(2)
                                    ->maxLength(100),
                                Hidden::make('guard_name')
                                    ->label(__('filament-shield::filament-shield.field.guard_name'))
                                    ->default(Utils::getFilamentAuthGuard())
                                    ->nullable(),
                                Forms\Components\Toggle::make('select_all')
                                    ->onIcon('heroicon-s-shield-check')
                                    ->offIcon('heroicon-s-shield-exclamation')
                                    ->label(__('filament-shield::filament-shield.field.select_all.name'))
                                    // ->helperText(fn (): HtmlString => new HtmlString(__('filament-shield::filament-shield.field.select_all.message')))
                                    ->helperText('Select this option to enable all permissions at once.')
                                    ->live()
                                    ->afterStateUpdated(function ($livewire, Forms\Set $set, $state) {
                                        static::toggleEntitiesViaSelectAll($livewire, $set, $state);
                                    })
                                    ->dehydrated(fn ($state): bool => $state),
                                    TextInput::make('search')->live()->reactive()->dehydrated(false),
                            ])
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ]),
                    ]),
                Forms\Components\Tabs::make('Permissions')
                    ->contained()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('filament-shield::filament-shield.resources'))
                            ->visible(fn (): bool => (bool) Utils::isResourceEntityEnabled())
                            ->badge(static::getResourceTabBadgeCount())
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema(fn (Get $get) => static::getResourceEntitiesSchema($get))
                                    ->columns(FilamentShieldPlugin::get()->getGridColumns()),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('filament-shield::filament-shield.pages'))
                            ->visible(fn (): bool => (bool) Utils::isPageEntityEnabled() && (count(FilamentShield::getPages()) > 0 ? true : false))
                            ->badge(count(static::getPageOptions()))
                            ->schema([
                                Forms\Components\CheckboxList::make('pages_tab')
                                    ->label('')
                                    ->options(fn (): array => static::getPageOptions())
                                    ->searchable()
                                    ->live()
                                    ->afterStateHydrated(function (Component $component, $livewire, string $operation, ?Model $record, Forms\Set $set) {
                                        static::setPermissionStateForRecordPermissions(
                                            component: $component,
                                            operation: $operation,
                                            permissions: static::getPageOptions(),
                                            record: $record
                                        );
                                        static::toggleSelectAllViaEntities($livewire, $set);
                                    })
                                    ->afterStateUpdated(fn ($livewire, Forms\Set $set) => static::toggleSelectAllViaEntities($livewire, $set))
                                    ->selectAllAction(fn (FormAction $action, Component $component, $livewire, Forms\Set $set) => static::bulkToggleableAction(
                                        action: $action,
                                        component: $component,
                                        livewire: $livewire,
                                        set: $set
                                    ))
                                    ->deselectAllAction(fn (FormAction $action, Component $component, $livewire, Forms\Set $set) => static::bulkToggleableAction(
                                        action: $action,
                                        component: $component,
                                        livewire: $livewire,
                                        set: $set,
                                        resetState: true
                                    ))
                                    ->dehydrated(fn ($state) => blank($state) ? false : true)
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->columns(FilamentShieldPlugin::get()->getCheckboxListColumns())
                                    ->columnSpan(FilamentShieldPlugin::get()->getCheckboxListColumnSpan()),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('filament-shield::filament-shield.widgets'))
                            ->visible(fn (): bool => (bool) Utils::isWidgetEntityEnabled() && (count(FilamentShield::getWidgets()) > 0 ? true : false))
                            ->badge(count(static::getWidgetOptions()))
                            ->schema([
                                Forms\Components\CheckboxList::make('widgets_tab')
                                    ->label('')
                                    ->options(fn (): array => static::getWidgetOptions())
                                    ->searchable()
                                    ->live()
                                    ->afterStateHydrated(function (Component $component, $livewire, string $operation, ?Model $record, Forms\Set $set) {
                                        static::setPermissionStateForRecordPermissions(
                                            component: $component,
                                            operation: $operation,
                                            permissions: static::getWidgetOptions(),
                                            record: $record
                                        );

                                        static::toggleSelectAllViaEntities($livewire, $set);
                                    })
                                    ->afterStateUpdated(fn ($livewire, Forms\Set $set) => static::toggleSelectAllViaEntities($livewire, $set))
                                    ->selectAllAction(fn (FormAction $action, Component $component, $livewire, Forms\Set $set) => static::bulkToggleableAction(
                                        action: $action,
                                        component: $component,
                                        livewire: $livewire,
                                        set: $set
                                    ))
                                    ->deselectAllAction(fn (FormAction $action, Component $component, $livewire, Forms\Set $set) => static::bulkToggleableAction(
                                        action: $action,
                                        component: $component,
                                        livewire: $livewire,
                                        set: $set,
                                        resetState: true
                                    ))
                                    ->dehydrated(fn ($state) => blank($state) ? false : true)
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->columns(FilamentShieldPlugin::get()->getCheckboxListColumns())
                                    ->columnSpan(FilamentShieldPlugin::get()->getCheckboxListColumnSpan()),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('filament-shield::filament-shield.custom'))
                            ->visible(fn (): bool => (bool) Utils::isCustomPermissionEntityEnabled() && (count(static::getCustomEntities()) > 0 ? true : false))
                            ->badge(count(static::getCustomPermissionOptions()))
                            ->schema([
                                Forms\Components\CheckboxList::make('custom_permissions')
                                    ->label('')
                                    ->options(fn (): array => static::getCustomPermissionOptions())
                                    ->searchable()
                                    ->live()
                                    ->afterStateHydrated(function (Component $component, $livewire, string $operation, ?Model $record, Forms\Set $set) {
                                        static::setPermissionStateForRecordPermissions(
                                            component: $component,
                                            operation: $operation,
                                            permissions: static::getCustomPermissionOptions(),
                                            record: $record
                                        );
                                        static::toggleSelectAllViaEntities($livewire, $set);
                                    })
                                    ->afterStateUpdated(fn ($livewire, Forms\Set $set) => static::toggleSelectAllViaEntities($livewire, $set))
                                    ->selectAllAction(fn (FormAction $action, Component $component, $livewire, Forms\Set $set) => static::bulkToggleableAction(
                                        action: $action,
                                        component: $component,
                                        livewire: $livewire,
                                        set: $set
                                    ))
                                    ->deselectAllAction(fn (FormAction $action, Component $component, $livewire, Forms\Set $set) => static::bulkToggleableAction(
                                        action: $action,
                                        component: $component,
                                        livewire: $livewire,
                                        set: $set,
                                        resetState: true
                                    ))
                                    ->dehydrated(fn ($state) => blank($state) ? false : true)
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->columns(FilamentShieldPlugin::get()->getCheckboxListColumns())
                                    ->columnSpan(FilamentShieldPlugin::get()->getCheckboxListColumnSpan()),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()
                    ->badge()
                    ->label(__('filament-shield::filament-shield.column.name'))
                    ->formatStateUsing(fn ($state): string => Str::headline($state))
                    ->colors(['primary'])
                    ->searchable(),
                // Tables\Columns\TextColumn::make('guard_name')
                //     ->badge()
                //     ->label(__('filament-shield::filament-shield.column.guard_name')),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->badge()
                    ->label(__('filament-shield::filament-shield.column.permissions'))
                    ->counts('permissions')
                    ->colors(['success']),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->label(__('filament-shield::filament-shield.column.updated_at'))
                //     ->dateTime(),
                ToggleColumn::make('is_active')->label('Active')
                ->afterStateUpdated(function($state,$record){
                        $users = User::where('role_id',$record->id)->get();
                        foreach($users as $user){
                            $user->active = $state;
                            $user->save();
                        }
                })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getModel(): string
    {
        return Utils::getRoleModel();
    }

    public static function getModelLabel(): string
    {
        return __('filament-shield::filament-shield.resource.label.role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-shield::filament-shield.resource.label.roles');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Utils::isResourceNavigationRegistered();
    }

    public static function getNavigationGroup(): ?string
    {
        return Utils::isResourceNavigationGroupEnabled()
            ? __('filament-shield::filament-shield.nav.group')
            : '';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-shield::filament-shield.nav.role.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament-shield::filament-shield.nav.role.icon');
    }

    public static function getNavigationSort(): ?int
    {
        return Utils::getResourceNavigationSort();
    }

    public static function getSlug(): string
    {
        return Utils::getResourceSlug();
    }

    public static function getNavigationBadge(): ?string
    {
        return Utils::isResourceNavigationBadgeEnabled()
            ? static::getModel()::count()
            : null;
    }

    public static function isScopedToTenant(): bool
    {
        return Utils::isScopedToTenant();
    }

    public static function canGloballySearch(): bool
    {
        return Utils::isResourceGloballySearchable() && count(static::getGloballySearchableAttributes()) && static::canViewAny();
    }

    public static function getResourceEntitiesSchema(callable $get): ?array
    {
        static::$permissionsCollection = static::$permissionsCollection ?: Utils::getPermissionModel()::all();
        $searchTerm = str_replace(' ', '', strtolower($get('search', '')));

        // Check if user is Property Manager
        if (auth()->user()->role && auth()->user()->role->name === 'Property Manager') {
            $exclusions = [
                'ActivityResource',
                'BankStatementResource',
                'AgingReportResource',
                'AppFeedbackResource',
                'BudgetResource',
                'ServiceBookingResource',
                'ComplaintsEnquiryResource',
                'ComplaintssuggestionResource',
                'ContractResource',
                'OAMInvoiceResource',
                'InvoiceResource',
                'LedgersResource',
                'LegalNoticeResource',
                'VendorServiceResource',
                'OacomplaintReportsResource',
                'BillResource',
                'OwnerAssociationResource',
                'PatrollingResource',
                'PollResource',
                'ProposalResource',
                'ResidentialFormResource',
                'TechnicianAssetsResource',
                'TenderResource',
                'VendorLedgersResource',
                'VisitorFormResource',
                'VendorResource',
                'VehicleResource',
                'WDAResource'
            ];

            $resources = collect(FilamentShield::getResources())
                ->filter(function ($entity) use ($exclusions, $searchTerm) {
                    $resourceClass = class_basename($entity['fqcn']);

                    // First check exclusions
                    if (in_array($resourceClass, $exclusions)) {
                        return false;
                    }

                    // Then check search if term exists
                    if ($searchTerm) {
                        $label = strtolower(FilamentShield::getLocalizedResourceLabel($entity['fqcn']));
                        return str_contains($label, $searchTerm);
                    }

                    return true;
                });
        } else {
            // Original behavior for other roles
            $resources = collect(FilamentShield::getResources())
                ->filter(function ($entity) use ($searchTerm) {
                    if ($searchTerm) {
                        $label = strtolower(FilamentShield::getLocalizedResourceLabel($entity['fqcn']));
                        return str_contains($label, $searchTerm);
                    }
                    return true;
                });
        }

        // Keep original mapping logic
        return $resources->map(function ($entity) {
            return Forms\Components\Section::make(FilamentShield::getLocalizedResourceLabel($entity['fqcn']))
                ->description(fn() => new HtmlString('<span style="word-break: break-word;">'))
                ->compact()
                ->schema([
                    static::getCheckBoxListComponentForResource($entity),
                ])
                ->columnSpan(FilamentShieldPlugin::get()->getSectionColumnSpan());
        })->toArray();
    }

    public static function getResourceTabBadgeCount(): ?int
    {
        // Check if user is Property Manager
        if (auth()->user()->role && auth()->user()->role->name === 'Property Manager') {
            $exclusions = [
                'ActivityResource',
                'BankStatementResource',
                'AgingReportResource',
                'AppFeedbackResource',
                'BudgetResource',
                'ServiceBookingResource',
                'ComplaintsEnquiryResource',
                'ComplaintssuggestionResource',
                'ContractResource',
                'OAMInvoiceResource',
                'InvoiceResource',
                'LedgersResource',
                'LegalNoticeResource',
                'VendorServiceResource',
                'OacomplaintReportsResource',
                'BillResource',
                'OwnerAssociationResource',
                'PatrollingResource',
                'PollResource',
                'ProposalResource',
                'ResidentialFormResource',
                'TechnicianAssetsResource',
                'TenderResource',
                'VendorLedgersResource',
                'VisitorFormResource',
                'VendorResource',
                'VehicleResource',
                'WDAResource'
            ];

            return collect(FilamentShield::getResources())
                // First filter out excluded resources
                ->filter(function ($entity) use ($exclusions) {
                    $resourceClass = class_basename($entity['fqcn']);
                    return !in_array($resourceClass, $exclusions);
                })
                // Then count permissions for remaining resources
                ->map(fn ($resource) => count(static::getResourcePermissionOptions($resource)))
                ->sum();
        }

        // Original behavior for other roles
        return collect(FilamentShield::getResources())
            ->map(fn ($resource) => count(static::getResourcePermissionOptions($resource)))
            ->sum();
    }

    public static function getResourcePermissionOptions(array $entity): array
    {
        // List of resources that do not support certain actions
        $resourcesWithoutCreate = config('role_resource_permission.resourcesWithoutCreate');
        $resourcesWithoutEdit = config('role_resource_permission.resourcesWithoutEdit');
        $resourcesWithoutView = config('role_resource_permission.resourcesWithoutView');

        // Get permission prefixes dynamically from the utility function
        $permissionPrefixes = collect(Utils::getResourcePermissionPrefixes($entity['fqcn']));

        // Dynamically construct permissions
        return $permissionPrefixes->flatMap(function ($permission) use ($entity, $resourcesWithoutCreate, $resourcesWithoutEdit, $resourcesWithoutView) {

            // Skip the 'create' permission if the resource is in the list
            if ($permission === 'create' && in_array(class_basename($entity['fqcn']), $resourcesWithoutCreate)) {
                return []; // Don't add 'create' permission for this resource
            }

            // Skip the 'edit' or 'update' permission if the resource is in the list
            if (($permission === 'edit' || $permission === 'update') && in_array(class_basename($entity['fqcn']), $resourcesWithoutEdit)) {
                return []; // Don't add 'edit/update' permission for this resource
            }

            // Skip the 'view' permission if the resource is in the list
            if ($permission === 'view' && in_array(class_basename($entity['fqcn']), $resourcesWithoutView)) {
                return []; // Don't add 'view' permission for this resource
            }

            // Check if it's the 'view_any' permission and modify the label accordingly
            if ($permission === 'view_any') {
                return [
                    $permission . '_' . $entity['resource'] => 'Show Resource', // Custom label for 'view_any'
                ];
            }

            // Handle all other permissions dynamically
            return [
                $permission . '_' . $entity['resource'] => FilamentShield::getLocalizedResourcePermissionLabel($permission),
            ];

        })->toArray();
    }

    public static function setPermissionStateForRecordPermissions(Component $component, string $operation, array $permissions, ?Model $record): void
    {

        if (in_array($operation, ['edit', 'view'])) {

            if (blank($record)) {
                return;
            }
            if ($component->isVisible() && count($permissions) > 0) {
                $component->state(
                    collect($permissions)
                        /** @phpstan-ignore-next-line */
                        ->filter(fn ($value, $key) => $record->checkPermissionTo($key))
                        ->keys()
                        ->toArray()
                );
            }
        }
    }

    public static function toggleEntitiesViaSelectAll($livewire, Forms\Set $set, bool $state): void
    {
        $entitiesComponents = collect($livewire->form->getFlatComponents())
            ->filter(fn (Component $component) => $component instanceof Forms\Components\CheckboxList);

        if ($state) {
            $entitiesComponents
                ->each(
                    function (Forms\Components\CheckboxList $component) use ($set) {
                        $set($component->getName(), array_keys($component->getOptions()));
                    }
                );
        } else {
            $entitiesComponents
                ->each(fn (Forms\Components\CheckboxList $component) => $component->state([]));
        }
    }

    public static function toggleSelectAllViaEntities($livewire, Forms\Set $set): void
    {
        $entitiesStates = collect($livewire->form->getFlatComponents())
            ->reduce(function ($counts, $component) {
                if ($component instanceof Forms\Components\CheckboxList) {
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

    public static function getPageOptions(): array
    {
        // Check if user is Property Manager
        if (auth()->user()->role && auth()->user()->role->name === 'Property Manager') {
            $exclusions = [
                'Dashboard',
                'OwnerAssociationInvoice',
                'OwnerAssociationReceipt',
                'AgingReport',
                'DelinquentOwners',
                'BudgetListing',
                'CreateTender',
                'ListAllReceipts',
                'BudgetVsActual',
                'GeneralFundStatement',
                'GeneralFundStatementMollak',
                'ReserveFundStatement',
                'ReserveFundStatementMollak',
                'TrialBalance'
            ];

            return collect(FilamentShield::getPages())
                // First filter out excluded pages
                ->filter(function ($page) use ($exclusions) {
                    $pageClass = class_basename($page['class']);
                    return !in_array($pageClass, $exclusions);
                })
                // Then map the remaining pages
                ->flatMap(fn ($page) => [
                    $page['permission'] => FilamentShield::getLocalizedPageLabel($page['class']),
                ])
                ->toArray();
        }

        // Original behavior for other roles
        return collect(FilamentShield::getPages())
            ->flatMap(fn ($page) => [
                $page['permission'] => FilamentShield::getLocalizedPageLabel($page['class']),
            ])
            ->toArray();
    }

    public static function getWidgetOptions(): array
    {
        return collect(FilamentShield::getWidgets())
            ->flatMap(fn ($widget) => [
                $widget['permission'] => FilamentShield::getLocalizedWidgetLabel($widget['class']),
            ])
            ->toArray();
    }

    public static function getCustomPermissionOptions(): array
    {
        return collect(static::getCustomEntities())
            ->flatMap(fn ($customPermission) => [
                $customPermission => str($customPermission)->headline()->toString(),
            ])
            ->toArray();
    }

    protected static function getCustomEntities(): ?Collection
    {
        static::$permissionsCollection = static::$permissionsCollection ?: Utils::getPermissionModel()::all();
        $resourcePermissions = collect();
        collect(FilamentShield::getResources())->each(function ($entity) use ($resourcePermissions) {
            collect(Utils::getResourcePermissionPrefixes($entity['fqcn']))->map(function ($permission) use ($resourcePermissions, $entity) {
                $resourcePermissions->push((string) Str::of($permission . '_' . $entity['resource']));
            });
        });

        $entitiesPermissions = $resourcePermissions
            ->merge(collect(FilamentShield::getPages())->map(fn ($page) => $page['permission'])->values())
            ->merge(collect(FilamentShield::getWidgets())->map(fn ($widget) => $widget['permission'])->values())
            ->values();

        return static::$permissionsCollection->whereNotIn('name', $entitiesPermissions)->pluck('name');
    }

    public static function bulkToggleableAction(FormAction $action, Component $component, $livewire, Forms\Set $set, bool $resetState = false): void
    {
        $action
            ->livewireClickHandlerEnabled(true)
            ->action(function () use ($component, $livewire, $set, $resetState) {
                /** @phpstan-ignore-next-line */
                $component->state($resetState ? [] : array_keys($component->getOptions()));
                static::toggleSelectAllViaEntities($livewire, $set);
            });
    }

    public static function getCheckBoxListComponentForResource(array $entity): Component
    {
        $permissionsArray = static::getResourcePermissionOptions($entity);

        return Forms\Components\CheckboxList::make($entity['resource'])
            ->label('')
            ->options(fn (): array => $permissionsArray)
            ->live()
            ->afterStateHydrated(function (Component $component, $livewire, string $operation, ?Model $record, Forms\Set $set) use ($permissionsArray) {
                static::setPermissionStateForRecordPermissions(
                    component: $component,
                    operation: $operation,
                    permissions: $permissionsArray,
                    record: $record
                );

                static::toggleSelectAllViaEntities($livewire, $set);
            })
            ->afterStateUpdated(fn ($livewire, Forms\Set $set) => static::toggleSelectAllViaEntities($livewire, $set))
            ->selectAllAction(fn (FormAction $action, Component $component, $livewire, Forms\Set $set) => static::bulkToggleableAction($action, $component, $livewire, $set))
            ->deselectAllAction(fn (FormAction $action, Component $component, $livewire, Forms\Set $set) => static::bulkToggleableAction($action, $component, $livewire, $set, true))
            ->dehydrated(fn ($state) => ! blank($state))
            ->bulkToggleable()
            ->gridDirection('row')
            ->columns(FilamentShieldPlugin::get()->getResourceCheckboxListColumns());
    }
}
