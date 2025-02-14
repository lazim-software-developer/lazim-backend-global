<x-filament-panels::page>
    <form wire:submit="save" class="space-y-8">
        {{ $this->form }}

        <div class="mt-20 pt-5">
            @foreach($this->getFormActions() as $action)
            {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament-panels::page>
