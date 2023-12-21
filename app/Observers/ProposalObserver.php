<?php

namespace App\Observers;

use App\Models\Accounting\Proposal;
use App\Models\Accounting\Tender;
use App\Models\Building\Building;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ProposalObserver
{
    /**
     * Handle the Proposal "created" event.
     */
    public function created(Proposal $proposal): void
    {
        $tenders = Tender::where('id', $proposal->tender_id)->first();
        $building = Building::where('id', $tenders->building_id)->first();
        $notifyTo = User::where('owner_association_id', $building->owner_association_id)->where('role_id', 10)->get();
        Notification::make()
            ->success()
            ->title("New Proposal")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New proposal by ' . auth()->user()->first_name)
            ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the Proposal "updated" event.
     */
    public function updated(Proposal $proposal): void
    {
        $vendor = Vendor::where('id', $proposal->vendor_id)->first();
        $user = auth()->user();
        if ($user->role->name == 'OA') {
            if ($proposal->status == 'approved') {
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $vendor->owner_id ,
                    'data' => json_encode([
                        'actions' => [],
                        'body' => 'Your Proposal has been approved.',
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title' => 'Proposal status update.',
                        'view' => 'notifications::notification',
                        'viewData' => [],
                        'format' => 'filament',
                    ]),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }
            if ($proposal->status == 'rejected') {
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $vendor->owner_id,
                    'data' => json_encode([
                        'actions' => [],
                        'body' => 'Your Proposal has been approved.',
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title' => 'Proposal status update.',
                        'view' => 'notifications::notification',
                        'viewData' => [],
                        'format' => 'filament',
                    ]),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }

        }
    }

    /**
     * Handle the Proposal "deleted" event.
     */
    public function deleted(Proposal $proposal): void
    {
        //
    }

    /**
     * Handle the Proposal "restored" event.
     */
    public function restored(Proposal $proposal): void
    {
        //
    }

    /**
     * Handle the Proposal "force deleted" event.
     */
    public function forceDeleted(Proposal $proposal): void
    {
        //
    }
}
