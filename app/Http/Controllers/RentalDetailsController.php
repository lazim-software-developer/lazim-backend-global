<?php

namespace App\Http\Controllers;

use App\Http\Resources\RentalDetailsResource;
use App\Models\OwnerAssociation;
use App\Models\RentalCheque;
use App\Models\RentalDetail;
use App\Models\User\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RentalDetailsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'flat_id' => 'required|exists:flats,id',
        ]);

        $rentalDetails = RentalDetail::where('flat_id', $request->flat_id);

        if ($request->filled('date')) {
            $date = Carbon::createFromFormat('m-Y', $request->date);
            $rentalDetails->whereHas('rentalCheques', function ($query) use ($date) {
                $query->whereMonth('due_date', $date->month)->whereYear('due_date', $date->year);
            });
        }

        return RentalDetailsResource::collection($rentalDetails->paginate(10));
    }
    public function requestPayment(Request $request, RentalCheque $rentalCheque)
    {
        try {
            $request->validate([
                'building_id' => 'required|exists:buildings,id',
            ]);

            // Get owner association ID
            $oa = DB::table('building_owner_association')
                ->where('building_id', $request->building_id)
                ->where('active', true)
                ->first()?->owner_association_id;

            // Find user
            $user = User::where('owner_association_id', $oa)->first();

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
                                return RentalDetailsResource::getUrl('edit', [$slug, $rentalCheque?->id]);
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
}
