<?php

namespace App\Filament\Pages;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\OwnerAssociationReceipt as ModelsOwnerAssociationReceipt;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use NumberFormatter;

class OwnerAssociationReceipt extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.owner-association-receipt';

    protected static ?string $title = 'Generate Receipt';

    protected static ?string $slug = 'generate-receipt';

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
            Select::make('type')->required()
            ->options([
                "building" => "Building",
                "other" => "Other",
            ])->reactive(),
            TextInput::make('receipt_to')->required()
            ->visible(function (callable $get) {
                if ($get('type') == 'other') {
                    return true;
                }
                return false;
            }),
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
            Select::make('flat_id')
            ->required()
            ->options(function (callable $get) {
                return Flat::where('building_id', $get('building_id'))
                    ->pluck('property_number', 'id');
            })->visible(function (callable $get) {
                if ($get('type') == 'building') {
                    return true;
                }
                return false;
            })
            ->preload()
            ->live()
            ->label('Unit'),
            Select::make('paid_by')->required()
            ->options([
                "owner" => "Owner",
                "behalf of owner" => "Behalf of owner",
            ]),
            Select::make('payment_method')->required()
            ->options([
                "direct deposit" => "Direct Deposit",
                "cheque" => "Cheque",
                "cash" => "Cash",
                "virtual account" => "Virtual Account",
                "noqodi payment" => "Noqodi Payment",
            ]),
            Select::make('received_in')->required()
            ->options([
                "general fund" => "General Fund",
                "reserve fund" => "Reserve Fund",
            ]),
            TextInput::make('payment_reference')->required(),
            TextInput::make('amount')->numeric()->required(),
            TextInput::make('on_account_of')->required()->disabled(function (callable $get,Set $set) {
                if ($get('type') == 'building') {
                    $set('on_account_of','Service charge');
                }
            }),
            FileUpload::make('receipt_document'),


            
        ])
    ])->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Generate Receipt')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            // $oam = auth()->user()->ownerAssociation;
            // $building = Building::find($this->form->getState()['building_id']);
            // $receipt_id = strtoupper(substr($building->name, 0, 4)) . date('YmdHis');
            // $data['receipt_id'] = $receipt_id;
            // $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            // $words = ucwords($formatter->format($data['amount']));
            // session(['receipt_data' => $data,'oam' => $oam,'building' => $building,'words'=> $words]);
            // redirect()->route('receipt') ;
            $oam = auth()->user()->ownerAssociation;
            $data['owner_association_id'] = $oam->id;
            $receipt_id = strtoupper(substr($oam->name, 0, 4)) . date('YmdHis');
            $data['receipt_number'] = $receipt_id;
            // dd($data);
            $receipt = ModelsOwnerAssociationReceipt::create($data);
            session()->forget('receipt_data');
            session(['receipt_data' => $receipt->id]);
            redirect()->route('receipt') ;
        } catch (Halt $exception) {
            return;
        }
    }
}
