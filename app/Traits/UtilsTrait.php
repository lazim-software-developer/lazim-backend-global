<?php
namespace App\Traits;

use GuzzleHttp\Client;

trait UtilsTrait
{

    public function expoNotification($message)
    {
        $client = new Client();

        $client->post('https://exp.host/--/api/v2/push/send', [
            'headers' => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json'    => $message,
        ]);
    }
}
