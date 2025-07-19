<?php

namespace App\Filament\Resources\AccessCardFormsDocumentResource\Pages;

use App\Models\Order;
use Filament\Actions;
use App\Traits\UtilsTrait;
use App\Models\Configuration;
use App\Models\Forms\AccessCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ExpoPushNotification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\AccessCardFormsDocumentResource;

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
        if($parkingDetails == [] ){
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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
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
                        'data' => [
                            'notificationType' => 'MyRequest',
                            'building_id' => $this->record->building_id,
                            'flat_id' => $this->record->flat_id,
                        ],
                    ];

                    $this->expoNotification($message);
                }
            }

                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'custom_json_data' => json_encode([
                            'owner_association_id' => $this->record->building->owner_association_id ?? 1,
                            'building_id' => $this->record->building_id,
                            'flat_id' => $this->record->flat_id,
                            'user_id' => $this->record->user_id,
                            'type' => 'AccessCard',
                            'priority' => 'Medium',
                        ]),
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your access card form has been approved. ',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title' => 'Access card form status',
                            'view' => 'notifications::notification',
                            'viewData' => [
                                'building_id' => $this->record->building_id,
                                'flat_id' => $this->record->flat_id
                            ],
                            'format' => 'filament',
                            'url' => 'MyRequest',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
            // Generate payment link and save it in access_cards_table

            try {
                $price = Configuration::where('key', 'access_card_price')->where('owner_association_id', $this->record->building->owner_association_id)->first()->value;
                // $payment = createPaymentIntent($price ?? 100, $this->record->email);

                // if ($payment) {
                //     $this->record->update([
                //         'payment_link' => $payment->client_secret
                //     ]);
                // }
                // Create an entry in orders table with status pending
                $existingOrder = Order::where('orderable_id', $this->record->id)->where('orderable_type', AccessCard::class)->latest()->first();
                if ($existingOrder) {
                    Order::updateOrCreate(
                        [
                            'orderable_id' => $this->record->id,
                            'orderable_type' => AccessCard::class,
                            'payment_status' => $this->record->payment_status,
                        ],
                        [
                            'amount' => $this->record->payment_amount ?? 0, // Get amount from record or set default
                            'payment_intent_id' => $existingOrder->payment_intent_id, // Set appropriate value
                        ]
                    );
                } else {
                    Order::create([
                        'orderable_id' => $this->record->id,
                        'orderable_type' => AccessCard::class,
                        'payment_status' => $this->record->payment_status,
                        'amount' => $this->record->payment_amount ?? 0,
                        'payment_intent_id' => (new class {
                            public function generateRandomString()
                            {
                                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                                $charactersLength = strlen($characters);
                                $randomString = '';
                                for ($i = 0; $i < 50; $i++) {
                                    $randomString .= $characters[random_int(0, $charactersLength - 1)];
                                }
                                return 'lazim_' . $randomString;
                            }
                        })->generateRandomString()
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
                        'data' => [
                            'notificationType' => 'MyRequest',
                            'building_id' => $this->record->building_id,
                            'flat_id' => $this->record->flat_id
                        ],
                    ];
                    $this->expoNotification($message);
                }
            }
                    DB::table('notifications')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type' => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id' => $this->record->user_id,
                        'custom_json_data' => json_encode([
                            'owner_association_id' => $this->record->building->owner_association_id ?? 1,
                            'building_id' => $this->record->building_id,
                            'flat_id' => $this->record->flat_id,
                            'user_id' => $this->record->user_id,
                            'type' => 'AccessCard',
                            'priority' => 'Medium',
                        ]),
                        'data' => json_encode([
                            'actions' => [],
                            'body' => 'Your access card form has been rejected.',
                            'duration' => 'persistent',
                            'icon' => 'heroicon-o-document-text',
                            'iconColor' => 'danger',
                            'title' => 'Access card form status!',
                            'view' => 'notifications::notification',
                            'viewData' => [
                                'building_id' => $this->record->building_id,
                                'flat_id' => $this->record->flat_id
                            ],
                            'format' => 'filament',
                            'url' => 'MyRequest',
                        ]),
                        'created_at' => now()->format('Y-m-d H:i:s'),
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]);
        }
    }
}
