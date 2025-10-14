<?php

use App\Filament\Resources\HistoryRelationManagerResource\RelationManagers\HistoriesRelationManager;
use App\Models\Complaint;
use App\Models\ModelHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows related histories in relation manager table', function () {
    $complaint = Complaint::factory()->create();
    $history = ModelHistory::factory()->create([
        'historable_type' => $complaint->getMorphClass(),
        'historable_id' => $complaint->id,
        'action' => 'updated',
        'changes' => ['status' => ['old' => 'pending', 'new' => 'done']],
    ]);

    Livewire::test(HistoriesRelationManager::class, [
        'ownerRecord' => $complaint,
        'pageClass' => \App\Filament\Resources\ComplaintResource\Pages\EditComplaint::class,
    ])
        ->assertCanSeeTableRecords([$history])
        ->assertSee($history->action)
        ->assertSee($history->created_at->format('Y-m-d'));
});

it('formats change values correctly in form view', function () {
    $complaint = Complaint::factory()->create();
    $history = ModelHistory::factory()->create([
        'historable_type' => $complaint->getMorphClass(),
        'historable_id' => $complaint->id,
        'changes' => ['due_date' => ['old' => '2025-10-13', 'new' => '2025-10-14']],
    ]);

    Livewire::test(HistoriesRelationManager::class, [
        'ownerRecord' => $complaint,
        'pageClass' => \App\Filament\Resources\ComplaintResource\Pages\EditComplaint::class,
    ])
        ->callTableAction('view', $history)
        ->assertFormSet([
            'changes' => [
                'due_date' => '2025-10-13 → 2025-10-14'
            ],
        ]);
});
