<?php

namespace App\Observers;

use App\Filament\Resources\ContractResource;
use App\Models\Accounting\Proposal;
use App\Models\Building\Building;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ContractObserver
{
    /**
     * Handle the Contract "created" event.
     */
    public function created(Contract $contract): void
    {
        $requiredPermissions = ['view_any_contract'];
        $user = auth()->user();
        $building = Building::where('id', $contract->building_id)->first();
        $roles = Role::where('owner_association_id',$building->owner_association_id)->whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff'])->pluck('id');
        $notifyTo = User::where('owner_association_id', $building->owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
        ->filter(function ($notifyTo) use ($requiredPermissions) {
            return $notifyTo->can($requiredPermissions);
        });
        Notification::make()
            ->success()
            ->title("New Contract")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->body('New contract is created')
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(fn () => ContractResource::getUrl('edit', [$contract])),
            ])
            ->sendToDatabase($notifyTo);
    }

    /**
     * Handle the Contract "updated" event.
     */
    public function updated(Contract $contract): void
    {
        //contract document updates vendor will notify
        if ($contract->document_url) {
            $vendor = Vendor::where('id', $contract->vendor_id)->first();
            DB::table('notifications')->insert([
                'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                'type' => 'Filament\Notifications\DatabaseNotification',
                'notifiable_type' => 'App\Models\User\User',
                'notifiable_id' => $vendor->owner_id,
                'data' => json_encode([
                    'actions' => [],
                    'body' => 'Contract document has been updated.',
                    'duration' => 'persistent',
                    'icon' => 'heroicon-o-document-text',
                    'iconColor' => 'warning',
                    'title' => 'Contract document updates!',
                    'view' => 'notifications::notification',
                    'viewData' => [],
                    'format' => 'filament',
                    'url' => '',
                ]),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Handle the Contract "deleted" event.
     */
    public function deleted(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "restored" event.
     */
    public function restored(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "force deleted" event.
     */
    public function forceDeleted(Contract $contract): void
    {
        //
    }
}
