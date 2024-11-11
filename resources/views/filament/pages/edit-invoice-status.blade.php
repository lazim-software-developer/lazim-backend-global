<x-filament-panels::page>
    <form wire:submit="save" class="space-y-8"> {{-- Add space-y-8 class --}}
        {{ $this->form }}

        <div class="mt-20 pt-5"> {{-- Increased margin-top and added padding-top --}}
            @foreach($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament-panels::page>
