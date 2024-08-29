<div>
    {{ $getRecord()->documentUsers?->role->name === 'Tenant' ? 'Tenant' : $getRecord()->documentUsers?->role->name }}
</div>