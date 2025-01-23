<?php

namespace App\Http\Controllers;

use App\Filament\Resources\RentalChequeResource;
use App\Models\Master\Role;
use App\Models\User\User;
use App\Models\RentalCheque;
use App\Models\RentalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Jobs\PaymentRequestMail;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\DB;
use App\Models\Building\FlatTenant;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Http\Resources\RentalDetailsResource;

class RentalDetailsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'flat_id' => 'required|exists:flats,id',
        ]);

        $rentalDetails = RentalDetail::where('flat_id', $request->flat_id);

        if ($request->filled('date')) {
            $date = Carbon::createFromFormat('Y', $request->date);
            $rentalDetails->whereHas('rentalCheques', function ($query) use ($date) {
                $query->whereYear('due_date', $date->year);
            });
        }

        return RentalDetailsResource::collection($rentalDetails->paginate(10));
    }
    public function requestPayment(Request $request, RentalCheque $rentalCheque)
    {
        $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'flat_id'     => 'required|exists:flats,id',
        ]);

        // Get owner association ID
        $oa = DB::table('property_manager_flats')
            ->where(['flat_id' => $request->flat_id, 'active' => true])
            ->first()->owner_association_id;

        $pm = OwnerAssociation::where('id', $oa)->where('role', 'Property Manager')
            ->first()?->id;
        // Find user
        $roles               = Role::whereIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor', 'Staff', 'Facility Manager'])->pluck('id');
        $user                = User::where('owner_association_id', $pm)->whereNotIn('role_id', $roles)->whereNot('id', auth()->user()?->id)->get();
        if (!$user) {
            return response()->json([
                'message' => 'Property Manager not found.',
                'status'  => 'error',
            ], 404);
        }
        PaymentRequestMail::dispatch($user, $rentalCheque);
        try {
            // Create and send notification
            Notification::make()
                ->success()
                ->title("Payment Request")
                ->icon('heroicon-o-document-text')
                ->iconColor('warning')
                ->body("Please approve the payment request by providing the payment details.")
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(function () use ($pm, $rentalCheque) {
                            $slug = OwnerAssociation::where('id', $pm)->first()?->slug;
                            if ($slug) {
                                return RentalChequeResource::getUrl('edit', [$slug, $rentalCheque?->id]);
                            }
                            return url('/app/rental-cheques/' . $rentalCheque?->id . '/edit');
                        }),
                ])
                ->sendToDatabase($user);
            $rentalCheque->update(['payment_link_requested' => true]);

            return response()->json([
                'message' => 'Request sent successfully.',
                'status'  => 'success',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing the request',
                'error'   => $e->getMessage(),
                'status'  => 'error',
            ], 500);
        }

    }
    public function tenantsCheques(Request $request)
    {
        $request->validate([
            'flat_id' => 'required|exists:flats,id',
            'building_id' => 'required|exists:buildings,id',
            'flat_tenant_id' => 'required|exists:flat_tenants,id',
        ]);
        // $user       = auth()->user();
        // $flatTenant = FlatTenant::where([
        //     'tenant_id'   => $user->id,
        //     'building_id' => $request->building_id,
        //     'flat_id'     => $request->flat_id,
        //     'active'      => true,
        // ])->first();
        // abort_if($flatTenant->role !== 'Owner', 403, 'You are not Owner');

        // $tenants = FlatTenant::where([
        //     'building_id' => $request->building_id,
        //     'flat_id'     => $request->flat_id,
        //     'active'      => true,
        //     'role'        => 'Tenant',
        // ])->pluck('id');
        $tenants = $request->flat_tenant_id;

        $rentalDetails = RentalDetail::where('flat_tenant_id', $tenants);

        if ($request->filled('date')) {
            $date = Carbon::createFromFormat('Y', $request->date);
            $rentalDetails->whereHas('rentalCheques', function ($query) use ($date) {
                $query->whereYear('due_date', $date->year);
            });
        }

        return RentalDetailsResource::collection($rentalDetails->paginate(10));
    }
}
