<?php

namespace App\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\Component;
use Filament\Forms\Components\Contracts\HasForms;
class VendorRegistration extends Component 
{
    use InteractsWithForms;
    public function render()
    {
        return view('livewire.vendor-registration');
    }
}
