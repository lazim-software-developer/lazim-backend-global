<?php

namespace App\Observers;

use App\Filament\Resources\InvoiceResource;
use App\Models\Accounting\Invoice;
use App\Models\Building\Building;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
            $requiredPermissions = ['view_any_invoice'];
            $oam_ids = DB::table('building_owner_association')->where('building_id', $invoice?->building_id)->where('active', true)->pluck('owner_association_id');
            $roles = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor','Staff', 'Facility Manager'])->pluck('id');
            foreach($oam_ids as $oam_id){
                $notifyTo = User::where('owner_association_id', $oam_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get()
                ->filter(function ($notifyTo) use ($requiredPermissions) {
                    return $notifyTo->can($requiredPermissions);
                });
                if($notifyTo->count() > 0){
                    foreach($notifyTo as $user){
                        if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->invoice_id', $invoice->id)->exists()){
                            $data=[];
                            $data['notifiable_type']='App\Models\User\User';
                            $data['notifiable_id']=$user->id;
                            $slug = OwnerAssociation::where('id',$oam_id)->first()?->slug;
                            if($slug){
                                $data['url']=InvoiceResource::getUrl('view', [$slug,$invoice?->id]);
                            }else{
                                $data['url']=url('/app/invoices/' . $invoice?->id.'/view');
                            }
                            $data['title']="New Invoice for Building:".$invoice->building->name;
                            $data['body']='New Invoice submitted by  ' . auth()->user()->first_name;
                            $data['building_id']=$invoice->building_id;
                            $data['custom_json_data']=json_encode([
                                'building_id' => $invoice->building_id,
                                'invoice_id' => $invoice->id,
                                'user_id' => auth()->user()->id ?? null,
                                'owner_association_id' => $oam_id,
                                'type' => 'Invoice',
                                'priority' => 'Medium',
                            ]);
                            NotificationTable($data);
                        }
                    }
                }
                // Notification::make()
                //     ->success()
                //     ->title("New Invoice")
                //     ->icon('heroicon-o-document-text')
                //     ->iconColor('warning')
                //     ->body('New Invoice submitted by  ' . auth()->user()->first_name)
                //     ->actions([
                //         Action::make('view')
                //             ->button()
                //             ->url(function() use ($oam_id,$invoice){
                //                 $slug = OwnerAssociation::where('id',$oam_id)->first()?->slug;
                //                 if($slug){
                //                     return InvoiceResource::getUrl('edit', [$slug,$invoice?->id]);
                //                 }
                //                 return url('/app/invoices/' . $invoice?->id.'/edit');
                //             }),
                //     ])
                //     ->sendToDatabase($notifyTo);
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
                    'custom_json_data' => json_encode([
                        'owner_association_id' => $invoice->building->owner_association_id,
                        'building_id' => $invoice->building_id,
                        'invoice_id' => $invoice->id,
                        'user_id' => auth()->user()->id ?? null,
                        'type' => 'Invoice',
                        'priority' => 'Medium',
                    ]),
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
                        'url' => 'invoice',
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
                    'custom_json_data' => json_encode([
                        'owner_association_id' => $invoice->building->owner_association_id,
                        'building_id' => $invoice->building_id,
                        'invoice_id' => $invoice->id,
                        'user_id' => auth()->user()->id ?? null,
                        'type' => 'Invoice',
                        'priority' => 'Medium',
                    ]),
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
                        'url' => 'invoice',
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
