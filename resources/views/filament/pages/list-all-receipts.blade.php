<x-filament-panels::page>
<x-filament::breadcrumbs :breadcrumbs="[
    '/admin/ledgers' => 'Service Charge Ledgers',
    '' => 'Receipts',
]" />
    {{$this->table}}
</x-filament-panels::page>
