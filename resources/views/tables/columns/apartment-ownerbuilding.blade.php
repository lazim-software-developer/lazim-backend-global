<div>
@foreach ($getRecord()->flatOwners as $flatOwner)
    <div>
        {{ $flatOwner->flat->building->name }}
    </div>
@endforeach
</div>
