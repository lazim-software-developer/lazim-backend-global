<?php

namespace App\Livewire;

use Livewire\Component;

class Datepicker extends Component
{
    public $selectedDate;
    
    public function render()
    {
        return view('livewire.datepicker');
    }
}
