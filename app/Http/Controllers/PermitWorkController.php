<?php

namespace App\Http\Controllers;

use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\WorkPermit;
use Illuminate\Http\Request;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\WorkPermitRequest;
use App\Http\Resources\WorkListResource;
use App\Models\Building\FacilityBooking;
use Filament\Notifications\Notification;
use App\Http\Resources\PermitWorkResource;
use Filament\Notifications\Actions\Action;
use App\Http\Resources\CustomResponseResource;
use App\Filament\Resources\FacilityBookingResource;

class PermitWorkController extends Controller
{
    public function workList()
    {
        $workPermit = WorkPermit::where('active', true);

        return WorkListResource::collection($workPermit->paginate(10));
    }
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:work_permits,name',
        ]);

        $data = $request->all();
        if($request->hasFile('file')){
            $data['icon'] = optimizeDocumentAndUpload($request->file);
        }
        WorkPermit::create($data);

        return new CustomResponseResource([
            'title'   => 'Created Successful',
            'message' => 'Successful creted new work list',
            'code'    => 201,
            'status'  => 'Ok',
        ]);

    }
    public function index(Request $request)
    {
        $workPermit = FacilityBooking::where([
            'user_id'       => auth()->user()->id,
            'bookable_type' => WorkPermit::class,
            'building_id'   => $request->building_id,
            'flat_id'       => $request->flat_id,
        ]);

        return PermitWorkResource::collection($workPermit->paginate($request->paginate ?? 10));
    }

    public function store(WorkPermitRequest $request)
    {
        $flat_id = $request->input('flat_id');
        $owner_association_id = DB::table('property_manager_flats')->where(['flat_id' => $flat_id , 'active' => true])->first()->owner_association_id;

        $data = $request->all();
        $data['bookable_id'] = $request->facility_id;
        $data['bookable_type'] = WorkPermit::class;
        $data['user_id'] = auth()->user()->id;
        $data['flat_id'] = $flat_id;
        $data['owner_association_id'] = $owner_association_id;

        $workPermit = FacilityBooking::create($data);

        // Find user
         $roles               = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor', 'Staff', 'Facility Manager'])->pluck('id');
        $user                = User::where('owner_association_id', $owner_association_id)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get();

        // Create and send notification
        if($user){
            Notification::make()
                ->success()
                ->title("Work permit Request")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body("Please approve the Permit to work request.")
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(function () use ($owner_association_id, $workPermit) {
                            $slug = OwnerAssociation::where('id', $owner_association_id)->first()?->slug;
                            if ($slug) {
                                return FacilityBookingResource::getUrl('edit', [$slug, $workPermit?->id]);
                            }
                            return url('/app/facility-bookings/' . $workPermit?->id . '/edit');
                        }),
                ])
                ->sendToDatabase($user);
        }

        return new CustomResponseResource([
            'title'   => 'Booking Successful',
            'message' => 'Permit to work has been successfully created.',
            'code'    => 200,
        ]);

    }
}
