<x-filament-panels::page>
    <x-filament::tabs>
        @foreach ($this->getTabGroups() as $key => $tab)
            <x-filament::tabs.item
                :active="$this->getActiveTab() === (string) $key"
                wire:click="setActiveTab('{{ $key }}')"
                wire:loading.attr="disabled"
            >
                {{ $tab['label'] }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    {{ $this->table }}
</x-filament-panels::page>
