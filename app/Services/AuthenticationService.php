<?php

namespace App\Services;


class AuthenticationService
{

    public static function authenticate($loginData)
    {
        // $genericService = apkp(GenericHttpService::class);
        $response = GenericHttpService::post('/auth/login', $loginData);
        if (!empty($response['success'] ?? null) && !empty($response['data']['token'] ?? null)) {
            $token = $response['data']['token'];
            $responseData = $response["data"];

            // Session me token store karna
            SessionCryptoService::set(GenericHttpService::$TOKEN_KEY, $token);
            return $responseData;
        }

        return null;
    }

    public static function getInvoices($request)
    {
        // $genericService = apkp(GenericHttpService::class);
        $requestPayload =  [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'customer' => $request->customer ?? null,
            'status' => $request->status ?? null,
            'page' => $request->page ?? 1,
            'per_page' => $request->per_page ?? 20,
            'order_by' => $request->order_by ?? 'created_at',
            'direction' => $request->direction ?? 'desc',
        ];
        $response = GenericHttpService::post('/report/invoice', $requestPayload);
        if (!empty($response['success'] ?? null) && !empty($response['data'] ?? null)) {
            $responseData = $response["data"];
            return $responseData;
        }

        return [];
    }
}
