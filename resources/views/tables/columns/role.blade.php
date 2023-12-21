<div>
    {{ $getRecord()->documentUsers->role->name === 'Tenant' ? 'Resident' : $getRecord()->documentUsers->role->name }}
</div>