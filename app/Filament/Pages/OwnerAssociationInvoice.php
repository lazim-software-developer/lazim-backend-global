<?php

namespace App\Filament\Pages;

use App\Models\Building\Building;
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
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\View\View;
use NumberFormatter;

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
            Select::make('to')->required()
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
            })
            ->preload()
            ->live()
            ->label('Building Name'),
            TextInput::make('bill_to')->required()->visible(function (callable $get) {
                if ($get('to') == 'other') {
                    return true;
                }
                return false;
            }),
            TextInput::make('mode_of_payment'),
            TextInput::make('supplier_reference'),
            TextInput::make('job')->required(),
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
            TextInput::make('unit_price')->label('Rate')->numeric()->required(),
            TextInput::make('tax')->numeric()->inputMode('decimal')->placeholder(0)->hidden(function (callable $get) {
                if ($get('to') == 'building') {
                    return true;
                }
                return false;
            })->required(),
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
            $invoice_id = strtoupper(substr($oam->name, 0, 4)) . date('YmdHis');
            $data['invoice_id'] = $invoice_id;
            $building = Building::find($this->form->getState()['building_id']);
            if($this->form->getState()['to'] == 'building'){
                $data['tax'] = 0.00;
            }
            $total = ($data['quantity'] * $data['unit_price']) + (($data['quantity'] * $data['unit_price'] * $data['tax'])/100);
            $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            $totalWords = ucwords($formatter->format($total));
            // dd($totalWords);
            session()->forget('invoice_data');
            session(['invoice_data' => $data,'oam' => $oam,'building' => $building,'total' => $total,'totalWords'=> $totalWords]);
            redirect()->route('invoice') ;
        } catch (Halt $exception) {
            return;
        }
    }
}
