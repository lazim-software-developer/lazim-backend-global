<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\Notifications\NotificationsResource;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    use UtilsTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $count        = request('count', 10);
        $notification = DB::table('notifications')->where('notifiable_id', auth()->user()->id)->orderBy('created_at', 'desc')->paginate($count);
        return NotificationsResource::collection($notification);
    }

    public function clearNotifications()
    {
        DB::table('notifications')->where('notifiable_id', auth()->user()->id)->delete();
        return (new CustomResponseResource([
            'title'   => 'Success',
            'message' => 'Notifications cleared!',
            'code'    => 200,
        ]))->response()->setStatusCode(200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function pushNotification(Request $request)
    {
        $expoPushTokens = ExpoPushNotification::whereIn('user_id', $request->ids)->pluck('token');

        if ($expoPushTokens->count() > 0) {
            foreach ($expoPushTokens as $expoPushToken) {

                $message = [
                    'to'    => $expoPushToken,
                    'sound' => 'default',
                    'url'   => 'ComunityPostTab',
                    'title' => 'Notification working',
                    'body'  => 'Notification working',
                    'data'  => ['notificationType' => 'Testing'],
                ];
                $this->expoNotification($message);
            }
        }
    }
    public function pushNotificationNew(Request $request)
    {
        $expoPushTokens = ExpoPushNotification::whereIn('user_id', $request->ids)->pluck('token');

        if ($expoPushTokens->count() > 0) {
            foreach ($expoPushTokens as $expoPushToken) {

                $message = [
                    'to'    => $expoPushToken,
                    'sound' => 'default',
                    'url'   => 'ComunityPostTab',
                    'title' => 'Notification working',
                    'body'  => 'Notification working',
                    'data'  => ['notificationType' => 'Testing'],
                ];
                $this->expoNotificationFcm($message);
            }
        }
    }
}
