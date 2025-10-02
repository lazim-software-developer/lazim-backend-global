<?php

use App\Models\User\User;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class);


beforeEach(function () {
    $this->user = User::whereEmail('admin@gmail.com')->firstOrFail();
});

test('authenticated user can create review', function () {
    $payload = [
        'oa_id'    => 1,
        'flat_id'  => 101,
        'type'     => 'feedback', // must match your ReviewType enum label
        'comment'  => 'Nice place!',
        'feedback' => 'Loved the cleanliness'
    ];  
    

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/review/create', $payload);

    $response->assertStatus(200)
             ->assertJson([
                 'success' => true,
                 'message' => 'Review created successfully',
             ])
             ->assertJsonStructure([
                 'success',
                 'error',
                 'data' => [
                     'id',
                     'user_id',
                     'oa_id',
                     'flat_id',
                     'type',
                     'comment',
                     'feedback',
                     'created_at'
                 ],
                 'message'
             ]);

    expect(Review::count())->toBe(1);
});

test('guest cannot create review', function () {
    $payload = [
        'oa_id'    => 1,
        'flat_id'  => 101,
        'type'     => 'feedback',
        'comment'  => 'Unauthorized attempt',
        'feedback' => 'No login'
    ];

    $response = $this->postJson('/api/review/create', $payload);

    $response->assertStatus(401);
});
