<div>
@foreach ($getRecord()->flatOwners as $flatOwner)
    <div>
        {{$flatOwner->flat->property_number}}
    </div>
@endforeach
</div>
