<?php

namespace App\Console\Commands;

use App\Filament\Resources\Building\FlatTenantResource;
use App\Filament\Resources\MoveOutFormsDocumentResource;
use App\Jobs\MoveoutNotificationJob;
use App\Models\AccountCredentials;
use App\Models\Building\FlatTenant;
use App\Models\Forms\MoveInOut;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class MoveoutNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:moveout-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $moveouts = MoveInOut::where('type', 'move-out')->where('status', 'approved')->whereDate('moving_date', now()->addDay()->toDateString())
            ->get();

        // ->pluck('user_id');
        foreach ($moveouts as $moveout) {
            $user = User::whereHas('role', function($query) {
                $query->where('name', 'OA');
            })->where('owner_association_id',$moveout->owner_association_id)->first();
            $flatTenat = FlatTenant::where('tenant_id', $moveout->user_id)->where('flat_id', $moveout->flat_id)->first();
            if($user){
                if(!DB::table('notifications')->where('notifiable_id', $user->id)->where('custom_json_data->post_id', $moveout->id)->exists()){
                        $data=[];
                        $data['notifiable_type']='App\Models\User\User';
                        $data['notifiable_id']=$user->id;
                        $slug = OwnerAssociation::where('id',$moveout->owner_association_id)->first()?->slug;
                        if($slug){
                            $data['url']=MoveOutFormsDocumentResource::getUrl('edit', [$slug, $moveout->id]);
                        }else{
                            $data['url']=url('/app/move-out-forms-documents/' . $moveout->id.'/edit');
                        }
                        $data['title']='New Move Out Submission';
                        $data['body']='New form submission by ' . auth()->user()->first_name;
                        $data['building_id']=$moveout->building_id;
                        $data['custom_json_data']=json_encode([
                            'building_id' => $moveout->building_id,
                            'post_id' => $moveout->id,
                            'user_id' => auth()->user()->id ?? null,
                            'owner_association_id' => $moveout->owner_association_id,
                            'type' => 'Move Out',
                            'priority' => 'Medium',
                        ]);
                        NotificationTable($data);
                    }
                }
            // Notification::make()
            //         ->success()
            //         ->title("Move out Reminder")
            //         ->icon('heroicon-o-document-text')
            //         ->iconColor('warning')
            //         ->body('Resident moving out on ' . $moveout->moving_date )
            //         ->actions([
            //             Action::make('view')
            //                 ->button()
            //                 ->url(function() use ($moveout){
            //                     $slug = OwnerAssociation::where('id',$moveout->owner_association_id)->first()?->slug;
            //                     if($slug){
            //                         return MoveOutFormsDocumentResource::getUrl('edit', [$slug,$moveout?->id]);
            //                     }
            //                     return url('/app/move-out-forms-documents/' . $moveout?->id.'/edit');
            //                 }),
            //         ])
            //         ->sendToDatabase($user);
            $credentials = AccountCredentials::where('oa_id', $moveout->owner_association_id)->where('active', true)->latest()->first();
            $mailCredentials = [
                'mail_host' => $credentials->host??env('MAIL_HOST'),
                'mail_port' => $credentials->port??env('MAIL_PORT'),
                'mail_username'=> $credentials->username??env('MAIL_USERNAME'),
                'mail_password' => $credentials->password??env('MAIL_PASSWORD'),
                'mail_encryption' => $credentials->encryption??env('MAIL_ENCRYPTION'),
                'mail_from_address' => $credentials->email??env('MAIL_FROM_ADDRESS'),
            ];
            MoveoutNotificationJob::dispatch($user, $moveout, $mailCredentials);
        }
    }
}
