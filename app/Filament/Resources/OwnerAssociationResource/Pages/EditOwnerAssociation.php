<?php

namespace App\Filament\Resources\OwnerAssociationResource\Pages;

use App\Filament\Resources\OwnerAssociationResource;
use App\Models\User\User;
use App\Models\OwnerAssociation;
use App\Models\Module;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditOwnerAssociation extends EditRecord
{
    protected static string $resource = OwnerAssociationResource::class;
    protected ?string $heading = 'Owner Association';

    public $value;
    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    public function beforeSave()
    {
        $email_value = OwnerAssociation::where('id', $this->data['id'])->get();
        $this->value = $email_value->first()->email;
    }

    public function afterSave()
    {
        $this->UpdateUser($this->record);
    }
    public function UpdateUser($data)
    {
        $user = User::where('owner_association_id', $data->id)->where('phone', $data->phone)->where('email', $data->email)
            ->update([
                'first_name' => $data->name,
                'phone'      => $data->phone,
                'profile_photo' => $data->profile_photo,
                'active'  => $data->active,
            ]);

        $state = $this->form->getState();

        $modules = $state['modules'] ?? [];

        $this->record->modules()->sync(
            collect($modules)
                ->filter() 
                ->keys()
                ->toArray()
        );

        foreach ($modules as $moduleId => $active) {
            if ($active) {
                $moduleName = Module::find($moduleId)?->name;

                if ($moduleName === 'Accounts') {
                    Log::info("Accounts module toggled for OA {$this->record->id}");
                }

                if ($moduleName === 'Management') {
                    Log::info("Management module toggled for OA {$this->record->id}");
                }
            }
        }

        $connection = DB::connection(env('SECOND_DB_CONNECTION'));
        $connection->table('users')->where('email', $data->email)->where('owner_association_id', $data->id)->update([
            'name' => $data->name,
        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['modules'] = $this->record->modules
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [$id => true])
            ->toArray();

        return $data;
    }
}
