<?php
namespace App\Filament\Resources\Building\FlatResource\Pages;

use App\Filament\Resources\Building\FlatResource;
use App\Models\OwnerAssociation;
use DB;
use Filament\Resources\Pages\CreateRecord;

class CreateFlat extends CreateRecord
{

    protected static string $resource = FlatResource::class;


    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_association_id'] ?? $data['owner_association_id'] = auth()->user()->owner_association_id;
        return $data;
    }

    protected function afterCreate()
    {
        $oaId = $this->record->owner_association_id;
        $role = OwnerAssociation::where('id', $oaId)->pluck('role')->toArray()[0];

        if ($role == 'Property Manager') {
            DB::table('property_manager_flats')->insert([
                'owner_association_id' => $this->record->owner_association_id,
                'flat_id'              => $this->record->id,
                'active'               => true,
            ]);
        }
    }

}
