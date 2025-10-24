<?php

namespace App\Traits;

use App\Models\User\User;
use App\Models\OwnerAssociation;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Filament\Resources\User\OwnerResource;

trait SendsMollakNotification
{
    /**
     * Send a Filament notification to selected roles within an owner association.
     *
     * @param  int|null $ownerAssociationId
     * @param  int|null $buildingId
     * @param  string $title
     * @param  string $body
     * @param  string $type
     * @param  string $resourceClass   // e.g. App\Filament\Resources\User\OwnerResource::class
     * @param  string[] $rolesToInclude // Roles to send notification to (e.g. ['Admin', 'Manager'])
     * @param  string|null $icon
     * @param  string|null $priority
     * @param  string $urlAction // 'index', 'view', 'edit'
     * @param  mixed|null $recordId
     */
    public function sendMollakNotification(
        ?int $ownerAssociationId,
        ?int $buildingId,
        string $title,
        string $body,
        string $type,
        array $rolesToInclude = ['Admin'],
        ?string $icon = 'heroicon-o-bell',
        ?string $priority = 'Medium',
        string $urlAction = 'edit',
    ): void 
    {  
        if (!$ownerAssociationId) {
            return;
        }

        $roleIds = Role::where('owner_association_id', $ownerAssociationId)->whereIn('name', $rolesToInclude)->pluck('id');

        $notifyTo = User::where('owner_association_id', 2)
           ->whereIn('role_id', $roleIds)
           ->when(auth()->check(), fn($q) => $q->whereNot('id', auth()->id()))
           ->get();

        if ($notifyTo->isEmpty()) {
            return;
        }

        Notification::make()
            ->success()
            ->title($title)
            ->body($body)
            ->icon($icon)
            ->iconColor('warning')
            ->type($type)
            ->priority($priority)
            ->building($buildingId)
            ->actions([
                Action::make('view')
                    ->button()
                    ->markAsRead()
                    ->url($urlAction),
            ])
            ->sendToDatabase($notifyTo);
    }
}
