<x-filament-panels::page>
<x-filament-panels::form wire:submit="save">
        {{ $this->form }}
        <div>
        <x-filament-panels::form.actions 
            :actions="$this->getFormActions()"
        /> 
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
