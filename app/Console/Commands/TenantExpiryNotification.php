<?php

namespace App\Console\Commands;

use App\Filament\Resources\Building\FlatTenantResource;
use App\Jobs\TenantExpiryNotificationJob;
use App\Models\AccountCredentials;
use App\Models\Building\FlatTenant;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TenantExpiryNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tenant-expiry-notification';

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
        $tenants = FlatTenant::whereDate('end_date', now()->addDay())->get();
        // Log::info(now()->addDay()->toDateString());
        foreach ($tenants as $tenant) {
            // Log::info($tenant);
            $user = User::whereHas('role', function($query) {
                $query->where('name', 'OA');
            })->where('owner_association_id',$tenant->owner_association_id)->first();
            Notification::make()
                    ->success()
                    ->title("Contract Expiration Reminder")
                    ->icon('heroicon-o-document-text')
                    ->iconColor('warning')
                    ->body('Resident contract is expiring on '. now()->addDay()->toDateString())
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url(function() use ($tenant){
                                $slug = OwnerAssociation::where('id',$tenant?->owner_association_id)->first()?->slug;
                                if($slug){
                                    return FlatTenantResource::getUrl('edit', [$slug,$tenant?->id]);
                                }
                                return url('/app/building/flat-tenants/' . $tenant?->id.'/edit');
                            }),
                    ])
                    ->sendToDatabase($user);
            $credentials = AccountCredentials::where('oa_id', $tenant->owner_association_id)->where('active', true)->latest()->first();
            $mailCredentials = [
                'mail_host' => $credentials->host??env('MAIL_HOST'),
                'mail_port' => $credentials->port??env('MAIL_PORT'),
                'mail_username'=> $credentials->username??env('MAIL_USERNAME'),
                'mail_password' => $credentials->password??env('MAIL_PASSWORD'),
                'mail_encryption' => $credentials->encryption??env('MAIL_ENCRYPTION'),
                'mail_from_address' => $credentials->email??env('MAIL_FROM_ADDRESS'),
            ];
            TenantExpiryNotificationJob::dispatch($user, $tenant, $mailCredentials);
        }
    }
}
