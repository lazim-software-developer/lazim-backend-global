<?php

namespace App\Filament\Pages;


use App\Models\Building\Building;
use App\Models\OwnerAssociationInvoice as ModelsOwnerAssociationInvoice;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;

class OwnerAssociationInvoice extends Page implements HasForms
{
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.owner-association-invoice';

    protected static ?string $title = 'Generate Invoice';

    protected static ?string $slug = 'generate-invoice';

    public ?array $data = []; 

    public function form(Form $form):Form
    {
        return $form->schema([
            Grid::make([
                'sm' => 1,
                'md' => 2,
                'lg' => 2,
            ])
        ->schema([
            DatePicker::make('date')->required(),
            DatePicker::make('due_date')->required(),
            Select::make('type')->required()
            ->options([
                "building" => "Building",
                "other" => "Other",
            ])->reactive(),
            Select::make('building_id')
            ->required()
            ->options(function () {
                $oaId = auth()->user()->owner_association_id;
                return Building::where('owner_association_id', $oaId)
                    ->pluck('name', 'id');
            })->visible(function (callable $get) {
                if ($get('type') == 'building') {
                    return true;
                }
                return false;
            })
            ->preload()
            ->live()
            ->label('Building Name'),
            TextInput::make('bill_to')->required()->visible(function (callable $get) {
                if ($get('type') == 'other') {
                    return true;
                }
                return false;
            }),
            TextInput::make('address')->required()->visible(function (callable $get) {
                if ($get('type') == 'other') {
                    return true;
                }
                return false;
            }),
            TextInput::make('mode_of_payment'),
            TextInput::make('supplier_name'),
            TextInput::make('job')->required()->disabled(function (callable $get,Set $set) {
                if ($get('type') == 'building') {
                    $set('job','Management Fee');
                }
            }),
            Select::make('month')->required()
                ->options([
                    'january' =>'January',
                    'february' =>'February',
                    'march' =>'March',
                    'april' =>'April',
                    'may' =>'May',
                    'june' =>'June',
                    'july' =>'July',
                    'august' =>'August',
                    'september' =>'September',
                    'october' =>'October',
                    'november' =>'November',
                    'december' =>'December'
                ]),
            TextInput::make('description')->required(),
            TextInput::make('quantity')->numeric()->required(),
            TextInput::make('rate')->numeric()->required(),
            TextInput::make('tax')->numeric()->placeholder(0)->visible(function (callable $get) {
                if ($get('type') == 'other') {
                    return true;
                }
                return false;
            })->required(),
            TextInput::make('trn'),
        ])
    ])->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Generate Invoice')
                ->submit('save'),
        ];
    }
    
    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $oam = auth()->user()->ownerAssociation;
            $data['owner_association_id'] = $oam->id;
            $invoice_id = strtoupper(substr($oam->name, 0, 4)) . date('YmdHis');
            $data['invoice_number'] = $invoice_id;
            if($data['type'] == 'building'){
                $data['tax'] = 0.00;
            }
            
            $receipt = ModelsOwnerAssociationInvoice::create($data);
            Notification::make()
                ->title("Invoice created successfully")
                ->success()
                ->send();
            session()->forget('invoice_data');
            session(['invoice_data' => $receipt->id]);
            redirect()->route('invoice');
            // redirected to owner association controller
            // route written in web.php
        } catch (Halt $exception) {
            return;
        }
    }
}
