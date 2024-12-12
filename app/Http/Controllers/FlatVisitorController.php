<?php

namespace App\Http\Controllers;

use App\Http\Resources\FlatVisitorResource;
use App\Models\Building\BuildingPoc;
use App\Models\ExpoPushNotification;
use App\Models\Vendor\Vendor;
use App\Models\Visitor\FlatVisitor;
use App\Traits\UtilsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlatVisitorController extends Controller
{
    use UtilsTrait;
    public function index(Vendor $vendor)
    {
        $ownerAssociationIds = DB::table('owner_association_vendor')
            ->where('vendor_id', $vendor->id)->pluck('owner_association_id');

        $buildingIds = DB::table('building_owner_association')
            ->whereIn('owner_association_id', $ownerAssociationIds)->pluck('building_id');

        $flatVisitors = FlatVisitor::whereIn('building_id', $buildingIds)->where('type','visitor')->orderByDesc('created_at');

        return FlatVisitorResource::collection($flatVisitors->paginate(10));
    }
    public function updateStatus(Vendor $vendor, FlatVisitor $flatVisitor, Request $request)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'required_if:status,rejected|max:150',
        ]);
        $data = $request->only(['status', 'remarks']);
        $flatVisitor->update($data);

        if ($request->status == 'approved') {
            $security       = BuildingPoc::where('building_id', $flatVisitor->building_id)
                ->where('active', true)->first()?->user_id;
            $expoPushTokens = ExpoPushNotification::where('user_id', $security)->pluck('token');
            if ($expoPushTokens->count() > 0) {
                $date         = $flatVisitor->start_time->toDateString();
                $time         = $flatVisitor->time_of_viewing;
                $visitorCount = $flatVisitor->number_of_visitors;
                $unit         = $flatVisitor->flat->property_number;
                foreach ($expoPushTokens as $expoPushToken) {
                    $message = [
                        'to'    => $expoPushToken,
                        'sound' => 'default',
                        'title' => 'Visitor form status.',
                        'body'  => "Visitor form has been approved \nfor $date at $time\n No. of visitors: $visitorCount\n Unit:$unit ",
                        'data'  => ['notificationType' => 'InAppNotfication'],
                    ];
                    $this->expoNotification($message);
                    DB::table('notifications')->insert([
                        'id'              => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'type'            => 'Filament\Notifications\DatabaseNotification',
                        'notifiable_type' => 'App\Models\User\User',
                        'notifiable_id'   => $security,
                        'data'            => json_encode([
                            'actions'   => [],
                            'body'      => "Visitor form has been approved \nfor $date at $time\n No. of visitors: $visitorCount\n Unit:$unit ",
                            'duration'  => 'persistent',
                            'icon'      => 'heroicon-o-document-text',
                            'iconColor' => 'warning',
                            'title'     => 'Visitor form status.',
                            'view'      => 'notifications::notification',
                            'viewData'  => [],
                            'format'    => 'filament',
                            'url'       => '',
                        ]),
                        'created_at'      => now()->format('Y-m-d H:i:s'),
                        'updated_at'      => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        return FlatVisitorResource::make($flatVisitor);
    }
     public function show(Vendor $vendor, FlatVisitor $flatVisitor, Request $request)
    {
        return FlatVisitorResource::make($flatVisitor);
    }
}
