<div>
@foreach ($getRecord()->flatOwners as $flatOwner)
    <div>
        {{ $flatOwner->flat?->building?->name ?? null }}
    </div>
@endforeach
</div>
