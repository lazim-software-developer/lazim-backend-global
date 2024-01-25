<?php

namespace App\Filament\Pages;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\OwnerAssociationReceipt as ModelsOwnerAssociationReceipt;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
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
            TextInput::make('receipt_to')->rules(['max:15'])->required()
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
            TextInput::make('payment_reference')->rules(['max:15'])->required(),
            TextInput::make('amount')->numeric()->rules([
                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    if ($value > 99999999) {
                        $fail('The quantity must not be greater than 8 digits.');
                    }
                },
            ])->required(),
            TextInput::make('on_account_of')->rules(['max:15'])->reactive()->required()->disabled(function (callable $get,Set $set) {
                if ($get('type') == 'building' && $get('on_account_of') == ' ') {
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
            $oam = auth()->user()->ownerAssociation;
            $data['owner_association_id'] = $oam->id;
            $receipt_id = strtoupper(substr($oam->name, 0, 4)) . date('YmdHis');
            $data['receipt_number'] = $receipt_id;
            // dd($data);
            $receipt = ModelsOwnerAssociationReceipt::create($data);
            Notification::make()
                ->title("Receipt created successfully")
                ->success()
                ->send();
            session()->forget('receipt_data');
            session(['receipt_data' => $receipt->id]);
            redirect()->route('receipt') ;
        } catch (Halt $exception) {
            return;
        }
    }
}
