<?php

namespace App\Filament\Pages;


use App\Jobs\SendInvoiceEmail;
use App\Mail\InvoiceGenerated;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\FlatOwners;
use App\Models\OwnerAssociation;
use App\Models\OwnerAssociationInvoice as ModelsOwnerAssociationInvoice;
use App\Models\User\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Closure;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Log;
use Mail;
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
            DatePicker::make('date')->required()
            ->label(function(){
                if(auth()->user()->role->name == 'Property Manager'){
                    return 'Invoice Date ';
                }
            }),
            DatePicker::make('due_date')->minDate(Carbon::now()->toDateString())
            ->label(function(){
                if(auth()->user()->role->name == 'Property Manager'){
                    return 'Invoice Due date';
                }
            }),
            Select::make('type')->required()
            ->options([
                "building" => "Building",
                "other" => "Other",
            ])->reactive(),
            Select::make('building_id')
            ->required()
            ->live()
            ->afterStateUpdated(function(Set $set){
                $set('flat_id', null);
            })
            ->options(function ($state) {
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
                    ->preload()
                    ->label('Flat Number')
                    ->live()
                    ->required(auth()->user()->role->name == 'Property Manager')
                    ->hidden(function(callable $get){
                        $userRole = auth()->user()->role->name;

                        if ($userRole === 'Property Manager' && $get('building_id') === null) {
                            return true;
                        }
                        elseif($userRole === 'Property Manager' && $get('building_id') != null){
                            return false;
                        }
                        return true;
                    })
                    ->searchable()
                    ->options(function(callable $get){
                        return Flat::where('building_id', $get('building_id'))->pluck('property_number', 'id');
                    }),

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
                    ->helperText('Select the resident to whom you want to send the generated invoice.')
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
            TextInput::make('supplier_name')->rules(['max:15'])
            ->hidden(function(){
                if (auth()->user()->role->name == 'Property Manager') {
                    return true;
                }
            }),
            TextInput::make('job')->rules(['max:15'])->required()->reactive()->disabled(function (callable $get,Set $set) {
                if ($get('type') == 'building' && $get('job') == ' ' ) {
                    $set('job','Management Fee');
                }
            })
            ->label(function(){
                if(auth()->user()->role->name == 'Property Manager'){
                    return 'Service/Job ';
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
            Textarea::make('description')
            ->rules(['max:100'])
            ->required(),
            TextInput::make('quantity')->numeric()->rules([
                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                    if ($value > 99) {
                        $fail('The quantity must not be greater than 2 digits.');
                    }
                },
            ])->required(auth()->user()->role->name == 'Property Manager'? false: true)
            ->hidden(auth()->user()->role->name == 'Property Manager'? true: false),
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
            Log::info('Form Data: ', $data);

            Log::info('Selected Resident: ', ['resident' => $this->form->getState('resident')]);

            $oam_id = DB::table('building_owner_association')->where('building_id', $data['building_id'])->where('active', true)->first();
            $oam = OwnerAssociation::find($oam_id?->owner_association_id ?: auth()->user()->ownerAssociation->first()->id);
            Log::info('Owner Association: ', ['oam' => $oam]);

            $data['owner_association_id'] = $oam?->id;
            $invoice_id = strtoupper(substr($oam->name, 0, 4)) . date('YmdHis');
            $data['invoice_number'] = $invoice_id;
            if ($data['type'] == 'building') {
                $data['tax'] = 0.00;
            }

            $receipt = ModelsOwnerAssociationInvoice::create($data);

            $pdf = Pdf::loadView('owner-association-invoice', ['data' => $receipt]);
            $pdfDirectory = storage_path('app/public/invoices');
            $pdfPath = $pdfDirectory . '/' . $invoice_id . '.pdf';

            // Ensure the directory exists
            if (!file_exists($pdfDirectory)) {
                if (!mkdir($pdfDirectory, 0755, true)) {
                    Log::error('Failed to create directory: ' . $pdfDirectory);
                    throw new Exception('Failed to create directory for storing invoices.');
                }
            }

            // Save the PDF
            if ($pdf->save($pdfPath)) {
                Log::info('PDF generated and saved: ', ['path' => $pdfPath]);
            } else {
                Log::error('Failed to save PDF: ' . $pdfPath);
                throw new Exception('Failed to save the invoice PDF.');
            }

            if (auth()->user()->role->name == 'Property Manager' && isset($data['resident'])) {
                $resident = User::find($data['resident']);

                if ($resident && filter_var($resident->email, FILTER_VALIDATE_EMAIL)) {
                    Log::info('Email job dispatched for resident: ', ['email' => $resident->email]);
                    dispatch(new SendInvoiceEmail($resident->email, $receipt, $pdfPath));
                } else {
                    Log::warning('Resident not found or email invalid: ',
                    ['resident_id' => $data['resident'], 'email' => $resident ? $resident->email : null]);
                }
            }

            Notification::make()
                ->title("Invoice created successfully")
                ->success()
                ->send();

            session()->forget('invoice_data');
            session(['invoice_data' => $receipt->id]);
            Log::info('Session updated with invoice data: ', ['invoice_id' => $receipt->id]);

            // redirect()->route('invoice');
            $appUrl      = config('app.url'); // Get the APP_URL from the environment configuration
            $redirectUrl = $appUrl . '/app/owner-association-invoices';

            redirect()->to($redirectUrl);


        } catch (Halt $exception) {
            Log::error('Error in save method: ', ['exception' => $exception->getMessage()]);
            return;
        } catch (\Exception $e) {
            Log::error('Unexpected error in save method: ', ['exception' => $e->getMessage()]);
            
        }
    }

}
