<?php

use App\Models\User\User;
use App\Models\OwnerAssociation;
use Filament\Facades\Filament;
use App\Filament\Resources\OwnerAssociationResource;
use App\Filament\Resources\OwnerAssociationResource\Pages\CreateOwnerAssociation;
use App\Filament\Resources\User\OwnerResource;

use function Pest\Livewire\livewire;

uses(Tests\TestCase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('app'));

    $this->adminUser = User::whereEmail('admin@gmail.com')->firstOrFail();
    $this->noPermissionUser = User::whereEmail('user@gmail.com')->firstOrFail();
});


function validOwnerAssociationData(): array
{
    return [
        'name'                => 'Test Owner Association',
        'slug'                => 'test-owner-' . uniqid(), // unique, lowercase
        'trn_number'          => 'TRN' . rand(100000,999999), // unique
        'phone'               => '+9715' . rand(1000000,9999999), // E.164 style + unique
        'address'             => 'Dubai Address',
        'email'               => 'oa' . uniqid() . '@example.com',
        'password'            => 'Pass@word123', // will be hashed by form
        'bank_account_number' => '1234567890',
        'verified'            => true,
        'active'              => true,
        // for FileUpload you can use UploadedFile::fake()->create()
        'trn_certificate'            => \Illuminate\Http\UploadedFile::fake()->create('trn.pdf', 100, 'application/pdf'),
        'trade_license'              => \Illuminate\Http\UploadedFile::fake()->create('trade.pdf', 100, 'application/pdf'),
        'dubai_chamber_document'     => \Illuminate\Http\UploadedFile::fake()->create('chamber.pdf', 100, 'application/pdf'),
        'memorandum_of_association'  => \Illuminate\Http\UploadedFile::fake()->create('memo.pdf', 100, 'application/pdf'),
        'profile_photo'              => \Illuminate\Http\UploadedFile::fake()->image('logo.png'),
    ];
}

test('create page renders for no permission', function () {
    actAsNoPermissionUser();

    $response = $this->get(OwnerAssociationResource::getUrl('create'));

    expect($response->status())->toBe(403);
});

test('edit page renders for no permission', function () {
    actAsNoPermissionUser();
    $module = OwnerAssociation::firstOrFail();
    $response = $this->get(OwnerAssociationResource::getUrl('edit ', ['record' => $module]));

    expect($response->status())->toBe(403);
});

// test('index page renders for admin', function () {
//     actAsAdmin();

//     $this->get(OwnerResource::getUrl('index'))
//         ->assertSuccessful()
//         ->assertSee('General Information') // Section heading
//         ->assertSee('Documents'); // Section heading
// });


// test('create page renders for admin', function () {
//     actAsAdmin();

//     $this->get(OwnerAssociationResource::getUrl('create', panel: 'app'))
//         ->assertSuccessful()
//         ->assertSee('General Information') // Section heading
//         ->assertSee('Documents'); // Section heading
// });

// test('admin can create owner association with valid data', function () {
//     actAsAdmin();

//     $data = validOwnerAssociationData();

//     livewire(CreateOwnerAssociation::class)
//         ->fillForm($data)
//         ->call('create')
//         ->assertHasNoFormErrors();

//     $this->assertDatabaseHas('owner_associations', [
//         'slug'  => $data['slug'],
//         'email' => $data['email'],
//     ]);
// });

// test('required fields validation works', function () {
//     actAsAdmin();

//     livewire(CreateOwnerAssociation::class)
//         ->fillForm([]) // empty form
//         ->call('create')
//         ->assertHasFormErrors([
//             'name'        => 'required',
//             'slug'        => 'required',
//             'trn_number'  => 'required',
//             'phone'       => 'required',
//             'address'     => 'required',
//             'email'       => 'required',
//             'trn_certificate' => 'required',
//             'trade_license'   => 'required',
//         ]);
// });


