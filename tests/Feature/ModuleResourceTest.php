<?php

use App\Models\Module;
use App\Models\User\User;
use Filament\Facades\Filament;
use App\Filament\Resources\ModuleResource;
use App\Filament\Resources\ModuleResource\Pages\ListModules;
use App\Filament\Resources\ModuleResource\Pages\CreateModule;
use function Pest\Livewire\livewire;

uses(Tests\TestCase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));

    $this->adminUser = User::whereEmail('admin@gmail.com')->firstOrFail();
    $this->noPermissionUser = User::whereEmail('user@gmail.com')->firstOrFail();
});

function actAsAdmin(): void
{
    test()->actingAs(test()->adminUser);
}

function actAsNoPermissionUser(): void
{
    test()->actingAs(test()->noPermissionUser);
}

test('index page renders for admin', function () {
    actAsAdmin();

    $this->get(ModuleResource::getUrl('index', panel: 'app'))
        ->assertSuccessful()
        ->assertSee('Module');
});

test('admin can create module', function () {
    actAsAdmin();

    livewire(CreateModule::class, ['tenant' => 1])
        ->fillForm(['name' => 'ValidModuleName'])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('modules', ['name' => 'ValidModuleName']);
});

test('edit page loads for admin', function () {
    actAsAdmin();

    $module = Module::firstOrFail();

    $this->get(ModuleResource::getUrl('edit', ['record' => $module], panel: 'app'))
        ->assertSuccessful()
        ->assertSee($module->name);
});

test('delete action visible for admin', function () {
    actAsAdmin();

    $module = Module::first();

    livewire(ListModules::class, ['tenant' => 1])
        ->assertTableActionVisible('delete', record: $module);
});

test('bulk delete action visible for admin', function () {
    actAsAdmin();

    livewire(ListModules::class)
        ->assertTableBulkActionVisible('delete');
});

/**
 * --- Forbidden actions ---
 */
beforeEach(function () {
    // Reset noPermissionUser before forbidden tests
    $this->noPermissionUser->syncRoles([])->syncPermissions([])->refresh();
});

test('index page forbidden for user without permission', function () {
    actAsNoPermissionUser();

    $this->get(ModuleResource::getUrl('index', panel: 'app'))
        ->assertForbidden();
});

test('create page forbidden without permission', function () {
    actAsNoPermissionUser();

    $this->get(ModuleResource::getUrl('create', panel: 'app'))
        ->assertForbidden();
});

test('edit page forbidden without permission', function () {
    actAsNoPermissionUser();

    $module = Module::firstOrFail();

    $this->get(ModuleResource::getUrl('edit', ['record' => $module], panel: 'app'))
        ->assertForbidden();
});

test('delete action hidden without permission', function () {
    // only view permission given
    $this->noPermissionUser->givePermissionTo('view_any_module')->refresh();
    actAsNoPermissionUser();

    $module = Module::firstOrFail();

    livewire(ListModules::class, ['tenant' => 1])
        ->assertSuccessful()
        ->assertTableActionHidden('delete', record: $module);
});

test('bulk delete action hidden without permission', function () {
    $this->noPermissionUser->givePermissionTo('view_any_module')->refresh();
    actAsNoPermissionUser();

    livewire(ListModules::class)
        ->assertTableBulkActionHidden('delete');
});
