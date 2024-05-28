<?php

namespace App\Filament\Resources\FitOutFormsDocumentResource\Pages;

use App\Filament\Resources\FitOutFormsDocumentResource;
use App\Jobs\SaleNocMailJob;
use App\Models\ExpoPushNotification;
use App\Models\Forms\FitOutForm;
use App\Models\Master\Service;
use App\Models\Order;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use App\Traits\UtilsTrait;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditFitOutFormsDocument extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = FitOutFormsDocumentResource::class;
    protected static ?string $title = 'Fit out';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function beforeSave(){
        if($this->record->admin_document != $this->data['admin_document']){
            $user= $this->record->user;
            $file = $this->data['admin_document'];
            SaleNocMailJob::dispatch($user,$file);

            $vendor = Contract::where('service_id', Service::where('name','MEP Services')->first()?->id)->where('end_date', now()->toDateString())->first()?->vendor_id;
            if($vendor){
                $vendor = Vendor::find($vendor);
                $user = $vendor->user;
                SaleNocMailJob::dispatch($user,$file);
            }
            $gatekeeper = $this->record->fitOut?->user_id;
            if($gatekeeper){
                $user = User::find($gatekeeper);
                SaleNocMailJob::dispatch($user,$file);
            }
        }
    }
    public function afterSave()
    {
        if ($this->record->status == 'approved') {
            $this->record->contractorRequest->update(['status'=>$this->record->status]);

            try {
                $payment = createPaymentIntent(env('FIT_OUT_AMOUNT'), 'punithprachi113@gmail.com');

                if ($payment) {
                    $this->record->update([
                        'payment_link' => $payment->client_secret
                    ]);

                    // Create an entry in orders table with status pending
                    Order::create([
                        'orderable_id' => $this->record->id,
                        'orderable_type' => FitOutForm::class,
                        'payment_status' => 'pending',
                        'amount' => env('FIT_OUT_AMOUNT'),
                        'payment_intent_id' => $payment->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Fit out form status',
                        'body' => 'Your fit out form has been approved.',
                        'data' => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);
                }
            }
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your fit out form has been approved.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Fit out form status',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                            'url' => 'MyRequest',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                
            
        }
        if ($this->record->status == 'rejected') {
            $this->record->contractorRequest->update(['status'=>$this->record->status]);
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Fit out form status',
                        'body' => 'Your fit out form has been rejected.',
                        'data' => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);
                }
            }
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'user',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your fit out form has been rejected.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'danger',
                            'title' => 'Fit out form status',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament',
                            'url' => 'MyRequest',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                
            
        }
        if ($this->record->rejected_fields){
            $rejectedFieldsJson = json_encode(['rejected_fields' => $this->record->rejected_fields]);
            $this->record->update(['rejected_fields' =>  $rejectedFieldsJson]);
            $this->record->save();
        }
    }
}
