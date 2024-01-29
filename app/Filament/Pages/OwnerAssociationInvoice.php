<?php

namespace App\Filament\Pages;


use App\Models\Building\Building;
use App\Models\OwnerAssociationInvoice as ModelsOwnerAssociationInvoice;
use Carbon\Carbon;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Days;

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
            DatePicker::make('due_date')->minDate(Carbon::now()->toDateString()),
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
            TextInput::make('bill_to')->required()
                ->rules(['max:15'])
                ->visible(function (callable $get) {
                    if ($get('type') == 'other') {
                        return true;
                    }
                    return false;
                }),
            TextInput::make('address')->rules(['max:30'])->required()->visible(function (callable $get) {
                if ($get('type') == 'other') {
                    return true;
                }
                return false;
            }),
            TextInput::make('mode_of_payment')->rules(['max:15']),
            TextInput::make('supplier_name')->rules(['max:15']),
            TextInput::make('job')->rules(['max:15'])->required()->reactive()->disabled(function (callable $get,Set $set) {
                if ($get('type') == 'building' && $get('job') == ' ' ) {
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
            TextInput::make('description')->rules(['max:15'])->required(),
            TextInput::make('quantity')->numeric()->rules([
                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    if ($value > 99) {
                        $fail('The quantity must not be greater than 2 digits.');
                    }
                },
            ])->required(),
            TextInput::make('rate')->numeric()->rules([
                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    if ($value > 999999) {
                        $fail('The quantity must not be greater than 6 digits.');
                    }
                },
            ])->required(),
            TextInput::make('tax')->numeric()->placeholder(0)->visible(function (callable $get) {
                if ($get('type') == 'other') {
                    return true;
                }
                return false;
            })->required(),
            TextInput::make('trn')->label('TRN'),
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
