<?php
namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

trait UtilsTrait
{
    public function expoNotification($message)
    {
        try {
            $client = new Client();
            $client->post('https://exp.host/--/api/v2/push/send', [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json'    => $message,
            ]);
        } catch (GuzzleException $e) {
            Log::error('Expo notification failed', [
                'error' => $e->getMessage(),
                'status' => $e->getCode()
            ]);
        }
    }
    public function expoNotificationFcm($message)
    {
        try {
            $client = new Client();
            $client->post('https://fcm.googleapis.com/fcm/send', [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json'    => $message,
            ]);
        } catch (GuzzleException $e) {
            Log::error('Expo notification failed', [
                'error' => $e->getMessage(),
                'status' => $e->getCode()
            ]);
        }
    }

}
