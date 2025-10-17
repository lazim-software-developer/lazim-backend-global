<?php

use App\Models\User\User;
use App\Models\ModelHistory;
use App\Models\Building\Complaint;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

uses(Tests\TestCase::class);


beforeEach(function () {
    $user = User::whereEmail('user12613@gmail.com')->firstOrFail();
    Log::error("ss");
    if (!$user) {
            $this->fail('User not found');
        }
        $this->actingAs($user);
    });

 test('creates a history record', function () {
    Log::error("ss");
 });

// test('creates a history record when model is updated', function () {
//     // Create a model using the HasHistory trait

//     // Log::error("ss");
//     // $complaint = Complaint::where('id', 27)->firstOrFail();
//     // Log::error(json_encode($complaint));

//     // // Update the model
//     // $complaint->update([
//     //     'title' => 'New title',
//     // ]);

//     //   Log::error(json_encode($complaint));

//     // Check if a history record is created
//     // $history = ModelHistory::first();

//     // expect($history)->not->toBeNull();
//     // expect($history->historable_type)->toBe($complaint->getMorphClass());
//     // expect($history->historable_id)->toBe($complaint->id);
//     // expect($history->user_id)->toBe($this->user->id);
//     // expect($history->action)->toBe('updated');
//     // expect($history->changes)
//     //     ->toHaveKey('title')
//     //     ->and($history->changes['title']['old'])->toBe('Old title')
//     //     ->and($history->changes['title']['new'])->toBe('New title');
// });

// // test('does not create history for unchanged updates', function () {
// //     $complaint = Complaint::create(['title' => 'Static']);

// //     // Updating with same value
// //     $complaint->update(['title' => 'Static']);

// //     expect(ModelHistory::count())->toBe(0);
// // });
