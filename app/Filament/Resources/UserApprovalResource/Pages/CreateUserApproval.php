<?php

namespace App\Filament\Resources\UserApprovalResource\Pages;

use App\Models\RentalDetail;
use App\Models\RentalCheque;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\UserApprovalResource;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CreateUserApproval extends CreateRecord
{
    protected static string $resource = UserApprovalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['status'] === 'approved' &&
            DB::table('flat_tenants')
                ->where('tenant_id', $this->record->user_id)
                ->where('role', 'Tenant')
                ->exists()
        ) {
            // Create rental details
            $rentalDetail = RentalDetail::create([
                'flat_id' => $data['flat_id'],
                'flat_tenant_id' => $this->record->id,
                'number_of_cheques' => $data['number_of_cheques'],
                'admin_fee' => $data['admin_fee'] ?? null,
                'advance_amount' => $data['advance_amount'],
                'advance_amount_payment_mode' => $data['advance_amount_payment_mode'],
                'status' => 'Active',
                'contract_start_date' => $data['contract_start_date'],
                'contract_end_date' => $data['contract_end_date'],
                'created_by' => auth()->user()->id,
                'status_updated_by' => auth()->user()->id,
                'property_manager_id' => auth()->user()->id,
            ]);

            // Create rental cheques
            if (isset($data['cheques']) && is_array($data['cheques'])) {
                foreach ($data['cheques'] as $cheque) {
                    RentalCheque::create([
                        'rental_detail_id' => $rentalDetail->id,
                        'cheque_number' => $cheque['cheque_number'],
                        'amount' => $cheque['amount'],
                        'due_date' => $cheque['due_date'],
                        'status' => 'Upcoming',
                        'status_updated_by' => auth()->user()->id,
                        'mode_payment' => $cheque['mode_payment'] ?? 'Cheque',
                    ]);
                }
            }
        }

        return $data;
    }
}
