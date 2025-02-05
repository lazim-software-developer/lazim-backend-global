<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use App\Filament\Resources\ComplaintResource;
use App\Models\Building\Complaint;
use App\Models\Master\Service;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComplaint extends EditRecord
{
    protected static string $resource = ComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $complaint = $this->record;

        $service = Service::find($complaint->service_id);

        if ($service) {
            $data['subcategory_id'] = $service->subcategory_id;
        }

        // Load existing media URLs
        $data['media'] = $this->record->media->pluck('url')->toArray();

        return $data;
    }

    public function afterSave(): void
    {
        if ($this->record->status == 'closed') {
            Complaint::where('id', $this->data['id'])
                ->update([
                    'closed_by'  => auth()->user()->id,
                    'close_time' => Carbon::now(),
                ]);
        }

        $complaint = $this->record;

        // Handle media updates
        if(isset($this->data['media'])) {
            // Remove old media not in new state
            $complaint->media()
                ->whereNotIn('url', $this->data['media'])
                ->delete();

            // Add new media
            foreach($this->data['media'] as $file) {
                if(!$complaint->media()->where('url', $file)->exists()) {
                    $complaint->media()->create([
                        'name' => $complaint->status === 'closed' ? 'after' : 'before',
                        'url' => $file,
                    ]);
                }
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
