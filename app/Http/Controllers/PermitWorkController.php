<?php
namespace App\Http\Controllers;

use App\Filament\Resources\FacilityBookingResource;
use App\Http\Requests\WorkPermitRequest;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\PermitWorkResource;
use App\Http\Resources\WorkListResource;
use App\Models\Building\FacilityBooking;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use App\Models\WorkPermit;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        if ($request->hasFile('file')) {
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
        $flat_id              = $request->input('flat_id');
        $owner_association_id = DB::table('building_owner_association')->where(['building_id' => $request->building_id, 'active' => true])->first()->owner_association_id;

        $data                         = $request->all();
        $data['bookable_id']          = $request->facility_id;
        $data['bookable_type']        = WorkPermit::class;
        $data['user_id']              = auth()->user()->id;
        $data['flat_id']              = $flat_id;
        $data['owner_association_id'] = $owner_association_id;

        $workPermit            = FacilityBooking::create($data);
        $owner_association_ids = DB::table('building_owner_association')
            ->where(['building_id' => $request->building_id, 'active' => true])->pluck('owner_association_id');

        $propertyManagerRole = Role::where('name', 'Property Manager')->first();
        $user                = User::whereIn('owner_association_id', $owner_association_ids)
            ->where('role_id', $propertyManagerRole?->id)
            ->first();

        // Create and send notification
        if ($user) {
            Notification::make()
                ->success()
                ->title("Work permit Request")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body("Please approve the Permit to work request.")
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(function () use ($user, $workPermit) {
                            $slug = OwnerAssociation::where('id', $user->owner_association_id)->first()?->slug;
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

    public function vendorWorkPermits(Request $request, $vendorId)
    {
        // Get buildings associated with this vendor
        $buildings = DB::table('building_vendor')
            ->where('vendor_id', $vendorId)
            ->where('active', true)
            ->pluck('building_id');

        $workPermits = FacilityBooking::whereIn('building_id', $buildings)
            ->where('bookable_type', WorkPermit::class)
            ->with(['building', 'user', 'bookable'])
            ->latest();

        // Apply optional filters
        if ($request->has('date')) {
            $workPermits->whereDate('date', $request->date);
        }

        if ($request->has('status')) {
            $workPermits->where('approved', $request->status === 'approved');
        }

        return PermitWorkResource::collection($workPermits->paginate($request->paginate ?? 10));
    }

    public function show(FacilityBooking $workPermit)
    {
        // Verify this is a work permit booking
        if ($workPermit->bookable_type !== WorkPermit::class) {
            return new CustomResponseResource([
                'title'   => 'Error',
                'message' => 'Invalid Work Permit Request',
                'code'    => 404,
            ]);
        }

        return new PermitWorkResource($workPermit->load(['building', 'user', 'bookable']));
    }

    public function updateStatus(Request $request, FacilityBooking $workPermit)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        if ($workPermit->bookable_type !== WorkPermit::class) {
            return new CustomResponseResource([
                'title'   => 'Error',
                'message' => 'Invalid Work Permit Request',
                'code'    => 404,
            ]);
        }

        $workPermit->update([
            'approved' => $request->status,
        ]);

        return new CustomResponseResource([
            'title'   => 'Status Updated',
            'message' => 'Work permit status updated successfully',
            'code'    => 200,
            'data'    => new PermitWorkResource($workPermit->load(['building', 'user', 'bookable']))
        ]);
    }

}
