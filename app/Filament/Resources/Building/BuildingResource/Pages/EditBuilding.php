<?php
namespace App\Filament\Resources\Building\BuildingResource\Pages;

use DB;
use Carbon\Carbon;
use App\Models\Floor;
use App\Models\Master\Role;
use Filament\Actions\Action;
use App\Models\Building\Building;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Jobs\FetchFlatsAndOwnersForBuilding;
use App\Filament\Resources\Building\BuildingResource;

class EditBuilding extends EditRecord
{
    protected static string $resource = BuildingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Sync Unit from Mollak')
            ->icon('heroicon-o-information-circle')
            ->disabled(function (): bool {
                // Get the latest record for this user
                $lastSync = DB::table('mollak_api_call_histories')
                    ->where('module', 'Unit')
                    ->where('job_name', 'FetchFlatsAndOwnersForBuilding')
                    ->where('user_id', auth()->user()->id)
                    ->orderBy('created_at', 'DESC')
                    ->first();
                
                // If no record exists, enable the button (return false for disabled)
                if (!$lastSync) {
                    return false;
                }
                
                // If record exists, check if it's less than 30 minutes old
                return now()->diffInMinutes(Carbon::parse($lastSync->created_at)) < 30;
            })
            ->extraAttributes(function () {
                // Get the last sync time from database
                $lastSync = DB::table('mollak_api_call_histories')->where('module', 'Unit')->where('job_name', 'FetchFlatsAndOwnersForBuilding')->where('user_id', auth()->user()->id)->orderBy('created_at', 'DESC')->first();
                
                // Default value if no sync history exists
                $lastSyncDisplay = 'Never synced';
                $lastSyncTime = now()->format('Y-m-d H:i:s');
                
                if ($lastSync) {
                    $lastSyncTime = $lastSync->created_at;
                    
                    // Format the display text based on time difference
                    $diffInMinutes = now()->diffInMinutes($lastSyncTime);
                    if ($diffInMinutes < 60) {
                        $lastSyncDisplay = $diffInMinutes . ' minutes ago';
                    } else {
                        $diffInHours = now()->diffInHours($lastSyncTime);
                        if ($diffInHours < 24) {
                            $lastSyncDisplay = $diffInHours . ' hours ago';
                        } else {
                            $lastSyncDisplay = Carbon::parse($lastSyncTime)->format('Y-m-d H:i:s');
                        }
                    }
                }
                
                return [
                    'title' => 'Last Sync: ' . $lastSyncDisplay,
                    'class' => 'relative',
                    'x-data' => '{
                        lastSync: "' . $lastSyncDisplay . '",
                        init() {
                            $el.innerHTML = "Sync Unit from Mollak<div class=\'text-xs mt-1 opacity-75\'>Last Sync: " + this.lastSync + "</div>";
                        }
                    }'
                ];
            })
                ->visible(function () {
                    $auth_user = auth()->user();
                    $role      = Role::where('id', $auth_user->role_id)->first()?->name;

                    if ($role === 'Admin' || $role === 'Property Manager' || $role === 'OA') {
                        return true;
                    }
                })
                ->action(function () {
                    $building = Building::where('id', $this->record->id)->first();
                    if (!empty($building->property_group_id)) {
                    FetchFlatsAndOwnersForBuilding::dispatch($building, 'Manual');
                    DB::table('mollak_api_call_histories')->insert([
                        'api_url'     => '/sync/propertygroups/' . $building->property_group_id . '/units',
                        'module'      => 'Unit',
                        'job_name'    => 'FetchFlatsAndOwnersForBuilding',
                        'user_id'     => auth()->user()->id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    Notification::make()
                        ->title('Unit Successfully Synced From Mollak')
                        ->success()
                        ->send();
                    }else{
                        Notification::make()
                        ->title('This Building is not found in Mollak')
                        ->warning()
                        ->send();
                    }
                })
        ];
    }
    public function beforeSave(){
         if ($this->record->floors != $this->data['floors']) {
            $currentFloorCount = Floor::where('building_id', $this->record->id)->count();
            $newFloorCount     = $this->data['floors'];

            if ($newFloorCount > $currentFloorCount) {
                // Add new floors
                $countfloor = $newFloorCount;
                while ($countfloor > $currentFloorCount) {
                    $qrCodeContent = [
                        'floors'      => $countfloor,
                        'building_id' => $this->record->id,
                    ];
                    $qrCode = QrCode::size(200)->generate(json_encode($qrCodeContent));
                    Floor::create([
                        'floors'      => $countfloor,
                        'building_id' => $this->record->id,
                        'qr_code'     => $qrCode,
                    ]);
                    $countfloor--;
                }
            } elseif ($newFloorCount < $currentFloorCount) {
                // Remove excess floors
                Floor::where('building_id', $this->record->id)
                    ->where('floors', '>', $newFloorCount)
                    ->delete();
            }
        }
    }
    public function afterSave()
    {
        if ($this->record->floors != null && Floor::where('building_id', $this->record->id)->count() === 0) {
            $countfloor = $this->record->floors;
            while ($countfloor > 0) {
                // Build an object with the required properties
                $qrCodeContent = [
                    'floors' => $countfloor,
                    'building_id' => $this->record->id,
                ];
                
                $exists = Floor::where('floors', $countfloor)
                ->where('building_id', $this->record->id)
                ->exists();

                if (!$exists) {
                // Generate a QR code using the QrCode library
                $qrCode = QrCode::size(200)->generate(json_encode($qrCodeContent));
                    Floor::create([
                        'floors' => $countfloor,
                        'building_id' => $this->record->id,
                        'qr_code' => $qrCode,
                    ]);
                }
                $countfloor = $countfloor - 1;
            }
        }

        $connection = DB::connection(env('SECOND_DB_CONNECTION'));
        $created_by = $connection->table('users')->where('owner_association_id', $this->record->owner_association_id)->where('type', 'company')->first()?->id;
        $connection->table('users')->updateOrInsert([
            'building_id' => $this->record->id,
            'owner_association_id' => $this->record->owner_association_id,
        ],[
            'name' => $this->record->name,
        ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('search', $data)) {
            $data['address'] = $data['search'];
        }
        if (auth()->user()->role->name == 'Property Manager') {
            DB::table('building_owner_association')
                ->where('building_id', $this->record->id)
                ->where('owner_association_id', auth()->user()->owner_association_id)
                ->where('active', true)
                ->update([
                    'from' => $data['from'],
                    'to'   => $data['to'],
                ]);
        }
        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($this->record) && ! empty($this->record->address)) {
            $data['search'] = $this->record->address;
        } else {
            $data['search'] = null;
        }
        if(isset($this->record->lat) && isset($this->record->lng)){
            $data['lat'] = $this->record->lat;
            $data['lng'] = $this->record->lng;
        }

        if (auth()->user()->role->name == 'Property Manager') {
            $data['from'] = DB::table('building_owner_association')
                ->where('building_id', $this->record->id)
                ->where('owner_association_id', auth()->user()->owner_association_id)
                ->where('active', true)
                ->first()->from??null;
            $data['to'] = DB::table('building_owner_association')
                ->where('owner_association_id', auth()->user()->owner_association_id)
                ->where('active', true)
                ->where('building_id', $this->record->id)
                ->first()->to??null;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
