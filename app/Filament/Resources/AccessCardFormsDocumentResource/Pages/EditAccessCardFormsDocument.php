<?php

namespace App\Filament\Resources\AccessCardFormsDocumentResource\Pages;

use App\Filament\Resources\AccessCardFormsDocumentResource;
use App\Models\ExpoPushNotification;
use App\Models\Forms\AccessCard;
use App\Models\Order;
use App\Traits\UtilsTrait;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditAccessCardFormsDocument extends EditRecord
{
    use UtilsTrait;
    protected static string $resource = AccessCardFormsDocumentResource::class;
    protected static ?string $title = 'Access card';
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $parkingDetails = json_decode($data['parking_details'], true);

        $formattedDetails = '';

        if (is_array($parkingDetails)) {
            foreach ($parkingDetails as $key => $val) {
                // Accumulate the formatted details with line breaks
                $formattedDetails .= ucfirst(str_replace('_', ' ', $key)) . ": $val\n";
            }
        } else {
            // Handle the case where parking_details is not an array
            $formattedDetails = "Invalid parking details format";
        }

        // Assign the accumulated content to $data['parking_details']
        $data['parking_details'] = $formattedDetails;

        // Your other logic for data manipulation...

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    public function afterSave()
    {
        if ($this->record->status == 'approved') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Access card form status',
                        'body' => 'Your access card form has been approved.',
                        'data' => ['notificationType' => 'MyRequest'],
                    ];

                    $this->expoNotification($message);

                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your access card form has been approved. ',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Access card form status',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament'
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }

            // Generate payment link and save it in access_cards_table

            try {
                $payment = createPaymentIntent(env('ACCESS_CARD_AMOUNT'), 'punithprachi113@gmail.com');

                if ($payment) {
                    $this->record->update([
                        'payment_link' => $payment->client_secret
                    ]);
                    Log::info("This email".$this->record->user->email);

                    // Create an entry in orders table with status pending
                    Order::create([
                        'orderable_id' => $this->record->id,
                        'orderable_type' => AccessCard::class,
                        'payment_status' => 'pending',
                        'amount' => env('ACCESS_CARD_AMOUNT'),
                        'payment_intent_id' => $payment->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
        if ($this->record->status == 'rejected') {
            $expoPushTokens = ExpoPushNotification::where('user_id', $this->record->user_id)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to' => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Access card form status!',
                        'body' => 'Your access card form has been rejected.',
                        'data' => ['notificationType' => 'MyRequest'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your access card form has been rejected.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'danger',
                            'title' => 'Access card form status!',
                            'view' => 'notifications::notification',
                            'viewData' => [],
                            'format' => 'filament'
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
    }
}
