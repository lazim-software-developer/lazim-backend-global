<?php

namespace App\Observers;

use App\Models\Accounting\Invoice;
use App\Models\Building\Building;
use App\Models\User\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        $vendor = DB::table('building_vendor')->where('building_id', $invoice->building_id)
            ->where('vendor_id', $invoice->vendor_id)->first();
        if ($vendor) {
            $building = Building::where('id', $vendor->building_id)->first();
            $notifyTo = User::where('owner_association_id', $building->owner_association_id)->where('role_id', 10)->get();
            Notification::make()
                ->success()
                ->title("New Invoice")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body('New Invoice submitted by  ' . auth()->user()->first_name)
                ->sendToDatabase($notifyTo);
        }

    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        $user = auth()->user();
        if ($user->role->name == 'OA') {
            if ($invoice->status == 'approved') {
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $invoice->created_by,
                    'data' => json_encode([
                        'actions' => [],
                        'body' => 'Your invoice has been approved.',
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title' => 'invoice status update.',
                        'view' => 'notifications::notification',
                        'viewData' => [],
                        'format' => 'filament',
                    ]),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }
            if ($invoice->status == 'rejected') {
                DB::table('notifications')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'type' => 'Filament\Notifications\DatabaseNotification',
                    'notifiable_type' => 'App\Models\User\User',
                    'notifiable_id' => $invoice->created_by,
                    'data' => json_encode([
                        'actions' => [],
                        'body' => 'Your invoice has been rejected.',
                        'duration' => 'persistent',
                        'icon' => 'heroicon-o-document-text',
                        'iconColor' => 'warning',
                        'title' => 'invoice status update.',
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
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(Invoice $invoice): void
    {
        //
    }
}
