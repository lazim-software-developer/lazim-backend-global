<?php
namespace App\Filament\Resources\UserApprovalResource\Pages;

use App\Filament\Resources\UserApprovalResource;
use App\Jobs\Residentapproval;
use App\Jobs\ResidentRejection;
use App\Models\AccountCredentials;
use App\Models\Building\FlatTenant;
use App\Models\RentalCheque;
use App\Models\RentalDetail;
use App\Models\UserApproval;
use App\Models\UserApprovalAudit;
use App\Models\User\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditUserApproval extends EditRecord
{
    protected static string $resource = UserApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            // Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (auth()->user()?->role->name !== 'Property Manager') {
            $user          = User::find($data['user_id']);
            $data['email'] = $user->email;
            $data['phone'] = $user->phone;
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
            $existingRental = RentalDetail::where('flat_tenant_id', $flatTenant->id)->first();
            if ($existingRental) {
                $data['admin_fee'] = $existingRental->admin_fee;
                $data['number_of_cheques'] = $existingRental->number_of_cheques;
                $data['contract_start_date'] = $existingRental->contract_start_date;
                $data['contract_end_date'] = $existingRental->contract_end_date;
                $data['advance_amount'] = $existingRental->advance_amount;
                $data['advance_amount_payment_mode'] = $existingRental->advance_amount_payment_mode;
                $data['other_charges'] = $existingRental->other_charges;
                $data['admin_charges'] = $existingRental->admin_charges;
                $data['brokerage'] = $existingRental->brokerage;
                $data['contract_status'] = $existingRental->status; // Use contract_status instead of status

                // Get associated cheques
                $data['cheques'] = $existingRental->rentalCheques()->get()->map(function($cheque) {
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->role->name !== 'Property Manager') {
            return $data;
        }

        if ($data['status'] === 'approved') {
            // Get the correct flat_tenant_id
            $flatTenant = DB::table('flat_tenants')
                ->where('tenant_id', $this->record->user_id)
                ->where('flat_id', $this->record->flat_id)
                ->where('role', 'Tenant')
                ->latest()
                ->first();

            if ($flatTenant) {
                $existingRental = RentalDetail::where('flat_tenant_id', $flatTenant->id)->first();

                if ($existingRental) {
                    // Update existing rental detail
                    $existingRental->update([
                        'status' => $data['contract_status'] ?? 'Active',
                        'contract_start_date' => $data['contract_start_date'],
                        'contract_end_date' => $data['contract_end_date'],
                        // 'advance_amount' => $data['advance_amount'],
                        // 'advance_amount_payment_mode' => $data['advance_amount_payment_mode'],
                    ]);
                } else {
                    // Only validate and create rental details if they don't exist
                    if (isset($data['cheques']) && isset($data['admin_fee'])) {
                        // Validate cheque amounts
                        $chequesSum = collect($data['cheques'])->sum('amount');
                        if ($chequesSum != $data['admin_fee']) {
                            Notification::make()
                                ->title('Incorrect Cheque Amounts')
                                ->body('The sum of cheque amounts must equal the contract amount')
                                ->danger()
                                ->send();
                            $this->halt();
                        }

                        try {
                            DB::beginTransaction();

                            // Create rental details with correct flat_tenant_id
                            $rentalDetail = RentalDetail::create([
                                'flat_id' => $this->record->flat_id,
                                'flat_tenant_id' => $flatTenant->id,
                                'number_of_cheques' => $data['number_of_cheques'],
                                'admin_fee' => $data['admin_fee'] ?? null,
                                'other_charges' => $data['other_charges'] ?? null,
                                'admin_charges' => $data['admin_charges'] ?? null,
                                'brokerage' => $data['brokerage'] ?? null,
                                'advance_amount' => $data['advance_amount'],
                                'advance_amount_payment_mode' => $data['advance_amount_payment_mode'],
                                'status' => $data['contract_status'] ?? 'Active',
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

                            DB::commit();

                            Notification::make()
                                ->title('Success')
                                ->body('Rental details have been created successfully.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to create rental details: ' . $e->getMessage())
                                ->danger()
                                ->send();
                            $this->halt();
                        }
                    }
                }
            }
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        UserApproval::find($this->data['id'])->update([
            'updated_by' => auth()->user()->id,
        ]);
        $tenant = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;

        // if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
        //     $emailCredentials = OwnerAssociation::find($this->record->owner_association_id)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
        // }else{
        // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
        // }
        $credentials     = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
        $mailCredentials = [
            'mail_host'         => $credentials->host ?? env('MAIL_HOST'),
            'mail_port'         => $credentials->port ?? env('MAIL_PORT'),
            'mail_username'     => $credentials->username ?? env('MAIL_USERNAME'),
            'mail_password'     => $credentials->password ?? env('MAIL_PASSWORD'),
            'mail_encryption'   => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
            'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
        ];

        $user  = User::find($this->record->user_id);
        $pm_oa = auth()->user()?->first_name ?? '';
        $pm_logo = auth()->user()?->ownerAssociation?->first()?->profile_photo ?? null;
        if ($this->data['status'] == 'approved' &&
        $this->record->status == null &&
            DB::table('flat_tenants')
            ->where('tenant_id', $this->record->user_id)
            ->where('flat_id', $this->record->flat_id)
            ->exists()
        ) {
            $user->active = true;
            $user->save();
            FlatTenant::where(['tenant_id' => $user->id, 'flat_id' => $this->record->flat_id, 'active' => false])
            ->latest()->first()?->update(['active' => true]);
            Residentapproval::dispatch($user, $mailCredentials, $pm_oa, $pm_logo, $this->record);
            Notification::make()
                ->title("Resident Approved")
                ->success()
                ->body("Resident approved successfully")
                ->send();
        }
        if ($this->data['status'] == 'rejected' && $this->record->status == null) {

            ResidentRejection::dispatch($user, $mailCredentials, $this->record, $pm_oa, $pm_logo);
            Notification::make()
                ->title("Resident Rejected")
                ->danger()
                ->body("Resident has been rejected")
                ->send();
        }
        if ($this->record->status == null) {
            UserApprovalAudit::where('user_approval_id', $this->record->id)->where('status', null)->first()?->update([
                'status'     => $this->data['status'],
                'remarks'    => $this->data['remarks'],
                'updated_by' => auth()->user()->id,
            ]);
        }
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }
}
