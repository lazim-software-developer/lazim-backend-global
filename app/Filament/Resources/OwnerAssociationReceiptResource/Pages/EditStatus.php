<?php

namespace App\Filament\Resources\OwnerAssociationReceiptResource\Pages;

use App\Filament\Resources\OwnerAssociationReceiptResource;
use App\Models\OwnerAssociationReceipt;
use App\Models\User;
use App\Notifications\OverdueReceiptNotification;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditStatus extends EditRecord
{
    protected static string $resource = OwnerAssociationReceiptResource::class;
    protected static string $view     = 'filament.pages.edit-receipt-status';
    // protected static ?string $panel   = 'app';

    // public function mount($record): void
    // {
    //     $this->record = OwnerAssociationReceipt::find($record);
    //     $this->form->fill($this->record->toArray());
    // }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make()
                ->schema([
                    Grid::make(['default' => 2])
                        ->schema([
                            TextInput::make('receipt_number')->disabled(),
                            TextInput::make('date')->disabled(),
                            TextInput::make('payment_method')->disabled(),
                            TextInput::make('amount')->disabled(),
                            Select::make('status')
                                ->required()
                                ->options([
                                    'pending' => 'Pending',
                                    'paid'    => 'Paid',
                                    'overdue' => 'Overdue',
                                ]),
                        ]),
                ]),
        ])->statePath('data');
    }

    protected function afterSave(): void
    {
        if ($this->data['status'] === 'overdue') {
            $propertyManager = User::whereHas('role', function ($query) {
                $query->where('name', 'Property Manager');
            })
                ->where('owner_association_id', $this->record->owner_association_id)
                ->first();

            if ($propertyManager) {
                $propertyManager->notify(new OverdueReceiptNotification($this->record));
            }
        }

        Notification::make()
            ->title('Receipt status updated successfully')
            ->success()
            ->send();
    }

    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     return $data;
    // }

    protected function getRedirectUrl(): string
    {
        return '/app/owner-association-receipts';

    }
}
