<?php

namespace App\Http\Controllers;

use App\Filament\Resources\RentalChequeResource;
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
        ]);

        // Get owner association ID
        $oa = DB::table('building_owner_association')
            ->where('building_id', $request->building_id)
            ->where('active', true)
            ->pluck('owner_association_id');

        $pm = OwnerAssociation::whereIn('id', $oa)->where('role', 'Property Manager')->first()->id;
        // Find user
        $user = User::where('owner_association_id', $pm)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Property Manager not found.',
                'status'  => 'error',
            ], 404);
        }
        $rentalCheque->update(['payment_link_requested' => true]);
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
                        ->url(function () use ($oa, $rentalCheque) {
                            $slug = OwnerAssociation::where('id', $oa)->first()?->slug;
                            if ($slug) {
                                return RentalChequeResource::getUrl('edit', [$slug, $rentalCheque?->id]);
                            }
                            return url('/app/rental-cheques/' . $rentalCheque?->id . '/edit');
                        }),
                ])
                ->sendToDatabase($user);

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
