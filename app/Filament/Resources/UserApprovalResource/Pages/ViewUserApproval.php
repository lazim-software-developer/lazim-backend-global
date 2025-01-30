<?php

namespace App\Filament\Resources\UserApprovalResource\Pages;

use App\Filament\Resources\UserApprovalResource;
use App\Models\RentalDetail;
use App\Models\User\User;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewUserApproval extends ViewRecord
{
    protected static string $resource = UserApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (auth()->user()?->role->name !== 'Property Manager') {
            return $data;
        }

        $user = User::find($data['user_id']);
        $data['user'] = $user->first_name;
        $data['email'] = $user->email;
        $data['phone'] = $user->phone;

        // Get the correct flat_tenant_id
        $flatTenant = DB::table('flat_tenants')
            ->where('tenant_id', $data['user_id'])
            ->where('flat_id', $data['flat_id'])
            ->where('role', 'Tenant')
            ->latest()
            ->first();

        if ($flatTenant) {
            // Check for existing rental details with correct flat_tenant_id
            $rentalDetail = RentalDetail::where('flat_tenant_id', $flatTenant->id)->first();
            if ($rentalDetail) {
                $data['admin_fee'] = $rentalDetail->admin_fee;
                $data['number_of_cheques'] = $rentalDetail->number_of_cheques;
                $data['contract_start_date'] = $rentalDetail->contract_start_date;
                $data['contract_end_date'] = $rentalDetail->contract_end_date;
                $data['advance_amount'] = $rentalDetail->advance_amount;
                $data['advance_amount_payment_mode'] = $rentalDetail->advance_amount_payment_mode;
                $data['other_charges'] = $rentalDetail->other_charges;
                $data['admin_charges'] = $rentalDetail->admin_charges;
                $data['brokerage'] = $rentalDetail->brokerage;

                // Load associated cheques
                $data['cheques'] = $rentalDetail->rentalCheques()->get()->map(function($cheque) {
                    return [
                        'cheque_number' => $cheque->cheque_number,
                        'amount' => $cheque->amount,
                        'due_date' => $cheque->due_date,
                        'mode_payment' => $cheque->mode_payment,
                    ];
                })->toArray();
            }
        }

        return $data;
    }
}
