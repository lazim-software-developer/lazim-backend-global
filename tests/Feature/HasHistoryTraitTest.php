<?php

use App\Models\ModelHistory;
use App\Models\Complaint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Prevent recursion on ModelHistory updates
    ModelHistory::unguard();
});

it('creates a history record when model is updated', function () {
    // Create a model using the HasHistory trait
    $complaint = Complaint::create([
        'title' => 'Old title',
        'description' => 'Old desc',
    ]);

    // Authenticate fake user
    $user = \App\Models\User::factory()->create();
    actingAs($user);

    // Update the model
    $complaint->update([
        'title' => 'New title',
    ]);

    // Check if a history record is created
    $history = ModelHistory::first();

    expect($history)->not->toBeNull();
    expect($history->historable_type)->toBe($complaint->getMorphClass());
    expect($history->historable_id)->toBe($complaint->id);
    expect($history->user_id)->toBe($user->id);
    expect($history->action)->toBe('updated');
    expect($history->changes)
        ->toHaveKey('title')
        ->and($history->changes['title']['old'])->toBe('Old title')
        ->and($history->changes['title']['new'])->toBe('New title');
});

it('does not create history for unchanged updates', function () {
    $complaint = Complaint::create(['title' => 'Static']);
    actingAs(\App\Models\User::factory()->create());

    // Updating with same value
    $complaint->update(['title' => 'Static']);

    expect(ModelHistory::count())->toBe(0);
});
