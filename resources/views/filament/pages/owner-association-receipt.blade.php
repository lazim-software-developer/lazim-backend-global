<x-filament-panels::page>
      @php
        $redirectUrl = auth()->user()->role->name == 'Admin' ? '/app/owner-association-invoices' : '/admin/owner-association-invoices'
    @endphp
<x-filament::breadcrumbs :breadcrumbs="[
    $redirectUrl => 'Back',
    ' ' => 'Generate Receipt'
]" />
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}
        <div>
        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
