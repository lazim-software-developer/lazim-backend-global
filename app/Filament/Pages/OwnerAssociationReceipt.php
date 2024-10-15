<?php

namespace App\Filament\Pages;

use App\Jobs\SendInvoiceEmail;
use App\Jobs\SendReceiptEmail;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\OwnerAssociation;
use App\Models\OwnerAssociationReceipt as ModelsOwnerAssociationReceipt;
use App\Models\User\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Exception;
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
use Illuminate\Support\Facades\DB;
use Log;
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
                if(auth()->user()->role->name == 'Admin'){
                    return Building::pluck('name', 'id');
                }elseif(auth()->user()->role->name == 'Property Manager'){
                    $buildingIds = DB::table('building_owner_association')
                    ->where('owner_association_id', auth()->user()->owner_association_id)
                    ->where('active', true)
                    ->pluck('building_id');

                return Building::whereIn('id', $buildingIds)
                    ->pluck('name', 'id');
                }
                else{
                    $oaId = auth()->user()?->owner_association_id;
                    return Building::where('owner_association_id', $oaId)
                        ->pluck('name', 'id');
                }
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
                if ($get('building_id') != null) {
                    return true;
                }
                return false;
            })
            ->preload()
            ->live()
            ->label('Unit'),

            Select::make('resident')
                ->label('Resident')
                ->hidden(function(callable $get){
                    $userRole = auth()->user()->role->name;

                    if ($userRole === 'Property Manager' && $get('flat_id') === null) {
                        return true;
                    }
                    elseif($userRole === 'Property Manager' && $get('flat_id') != null){
                        return false;
                    }
                    return true;
                })
                ->helperText('Select the resident to whom you want to send the generated receipt.')
                ->options(function (callable $get) {
                    $flatId    = $get('flat_id');
                    $residents = FlatTenant::where('flat_id', $flatId)
                        ->where('active', true)
                        ->get()
                        ->map(function ($tenant) {
                            $role            = $tenant->role;
                            $roleDescription = $role == 'Owner' ? 'Owner' : 'Tenant';
                            return [
                                'id'   => $tenant->tenant_id,
                                'name' => $tenant->user->first_name . ' (' . $roleDescription . ')',
                            ];
                        });
                    Log::info('Resident Options', $residents->toArray()); // Log resident data
                    return $residents->pluck('name', 'id')->toArray();
                })
                ->reactive(),

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
            Log::info('Form Data: ', $data);

            $oam_id = DB::table('building_owner_association')->where('building_id', $data['building_id'])->where('active', true)->first();
            $oam = OwnerAssociation::find($oam_id?->owner_association_id ?: auth()->user()->ownerAssociation->first()->id);
            Log::info('Owner Association: ', ['oam' => $oam]);

            $data['owner_association_id'] = $oam?->id;
            $receipt_id = strtoupper(substr($oam->name, 0, 4)) . date('YmdHis');
            $data['receipt_number'] = $receipt_id;

            // Create the receipt
            $receipt = ModelsOwnerAssociationReceipt::create($data);

            // Generate the PDF
            $pdf = Pdf::loadView('owner-association-receipts', ['data' => $receipt]);
            $pdfDirectory = storage_path('app/public/receipts');
            $pdfPath = $pdfDirectory . '/' . $receipt_id . '.pdf';

            // Ensure the directory exists
            if (!file_exists($pdfDirectory)) {
                if (!mkdir($pdfDirectory, 0755, true)) {
                    Log::error('Failed to create directory: ' . $pdfDirectory);
                    throw new Exception('Failed to create directory for storing receipts.');
                }
            }

            // Save the PDF
            if ($pdf->save($pdfPath)) {
                Log::info('PDF generated and saved: ', ['path' => $pdfPath]);
            } else {
                Log::error('Failed to save PDF: ' . $pdfPath);
                throw new Exception('Failed to save the receipt PDF.');
            }

            // Send email if applicable
            if (auth()->user()->role->name == 'Property Manager' && isset($data['resident'])) {
                $resident = User::find($data['resident']);

                if ($resident && filter_var($resident->email, FILTER_VALIDATE_EMAIL)) {
                    Log::info('Email job dispatched for resident: ', ['email' => $resident->email]);
                    dispatch(new SendReceiptEmail($resident->email, $receipt, $pdfPath));
                } else {
                    Log::warning('Resident not found or email invalid: ', [
                        'resident_id' => $data['resident'],
                        'email' => $resident ? $resident->email : null
                    ]);
                }
            }

            Notification::make()
                ->title("Receipt created successfully")
                ->success()
                ->send();

            session()->forget('receipt_data');
            session(['receipt_data' => $receipt->id]);
            Log::info('Session updated with receipt data: ', ['receipt_id' => $receipt->id]);

            // Redirect to the receipts page
            $appUrl = config('app.url'); // Get the APP_URL from the environment configuration
            $redirectUrl = $appUrl . '/app/owner-association-receipts';
            redirect()->to($redirectUrl);

        } catch (Halt $exception) {
            Log::error('Error in save method: ', ['exception' => $exception->getMessage()]);
            return;
        } catch (\Exception $e) {
            Log::error('Unexpected error in save method: ', ['exception' => $e->getMessage()]);
            Notification::make()
                ->title("Failed to create receipt")
                ->body("An unexpected error occurred. Please try again.")
                ->danger()
                ->send();
        }
    }

}
