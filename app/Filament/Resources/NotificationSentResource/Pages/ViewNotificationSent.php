<?php

namespace App\Filament\Resources\NotificationSentResource\Pages;

use Filament\Actions;
use App\Models\NotificationHistory;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\SaleNocNotifictionResource;

class ViewNotificationSent extends ViewRecord
{
    protected static string $resource = SaleNocNotifictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewSource')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->label('View Source')
                ->color('primary')
                ->url(fn () => $this->getRecord()->data['url']) // Use the URL from the record
                ->openUrlInNewTab(), // Open the URL in a new tab
        ];
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Extract data from JSON columns to make them available to the form
        if (isset($data['custom_json_data']) && is_string($data['custom_json_data'])) {
            $jsonData = json_decode($data['custom_json_data'], true);
            foreach ($jsonData as $key => $value) {
                $data['custom_json_data'][$key] = $value;
            }
        }
        
        if (isset($data['data']) && is_string($data['data'])) {
            $jsonData = json_decode($data['data'], true);
            foreach ($jsonData as $key => $value) {
                $data['data'][$key] = $value;
            }
        }
        
        return $data;
    }
    
    protected function beforeFill(): void
    {
        $record = $this->getRecord();
        $record->update(['read_at' => now()]);
        NotificationHistory::create([
            'notification_id' => $record->id,
            'user_id' => auth()->user()->id,
            'read_by' => auth()->user()->id,
            'action' => 'read',
            'read_at' => now()
        ]);
    }
}