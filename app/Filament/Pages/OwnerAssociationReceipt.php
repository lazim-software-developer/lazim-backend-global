<?php

namespace App\Filament\Pages;

use App\Models\Building\Building;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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
            })->visible(function (callable $get) {
                if ($get('to') == 'building') {
                    return true;
                }
                return false;
            })
            ->preload()
            ->live()
            ->label('Building Name'),
            // TextInput::make('bill_to')->required()->visible(function (callable $get) {
            //     if ($get('to') == 'other') {
            //         return true;
            //     }
            //     return false;
            // }),
            TextInput::make('through')->required(),
            TextInput::make('account')->required(),
            TextInput::make('amount')->numeric()->required(),
            TextInput::make('on_account_of')->required(),


            
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
            $building = Building::find($this->form->getState()['building_id']);
            $receipt_id = strtoupper(substr($building->name, 0, 4)) . date('YmdHis');
            $data['receipt_id'] = $receipt_id;
            $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
            $words = ucwords($formatter->format($data['amount']));
            // dd($data);
            // session()->forget('receipt_data');
            // session()->forget('oam');
            // session()->forget('building');            
            // session()->forget('words');
            session(['receipt_data' => $data,'oam' => $oam,'building' => $building,'words'=> $words]);
            redirect()->route('receipt') ;
        } catch (Halt $exception) {
            return;
        }
    }
}
