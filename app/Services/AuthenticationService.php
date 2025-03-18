<?php

namespace App\Services;


class AuthenticationService
{

    public static function authenticate($loginData)
    {
        // $genericService = apkp(GenericHttpService::class);
        $response = GenericHttpService::post('/login', $loginData);
        if ($response['success']) {
            $responseData = $response["data"];
            $token = $responseData["token"];
            //$sessionService = app(SessionLocalService::class);
            SessionCryptoService::set(GenericHttpService::$TOKEN_KEY, $token);
            // Explicitly save the session
            // SessionService::set("API_TOKEN", $token);
            //CryptoStorageService::writeJsonFile($httpService->TOKEN_FILE_PATH, $responseData);
            //CryptoStorageService::writeJsonFile(GenericHttpService::$TOKEN_FILE_PATH, $responseData);
            return $responseData;
        }
        return null;
    }
}
