<?php
namespace App\Traits;

use App\Models\AccountCredentials;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
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
            return 'success';
        } catch (GuzzleException $e) {
            Log::error('ExpoFailed', [
                'error' => $e->getMessage(),
                'status' => $e->getCode()
            ]);
            return $e->getMessage();
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
            return 'success';
        } catch (GuzzleException $e) {
            Log::error('ExpoFailed', [
                'error' => $e->getMessage(),
                'status' => $e->getCode()
            ]);
            return $e->getMessage();
        }
    }


    public function configureMail($oaId){
        $credentials = AccountCredentials::where('oa_id', $oaId)->where('active', true)->latest()->first();

        // Config::set('mail.mailers.smtp.host', $credentials->host ?? env('MAIL_HOST'));
        // Config::set('mail.mailers.smtp.port', $credentials->port ?? env('MAIL_PORT'));
        // Config::set('mail.mailers.smtp.username', $credentials->username ?? env('MAIL_USERNAME'));
        // Config::set('mail.mailers.smtp.password',  $credentials->password ?? env('MAIL_PASSWORD'));
        // Config::set('mail.mailers.smtp.encryption', $credentials->encryption ?? env('MAIL_ENCRYPTION'));
        // Config::set('mail.mailers.smtp.email', $credentials->email ?? env('MAIL_FROM_ADDRESS'));
    }
}
