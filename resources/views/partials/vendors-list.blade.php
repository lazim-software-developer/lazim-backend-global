@if($vendors->isEmpty())
    <p>No vendors found for the selected services.</p>
@else
    <ul>
        @foreach($vendors as $vendor)
            <li>{{ $vendor->name }}</li>
            <input id="{{ $vendor['id'] }}" name="vendors[]" value="{{ $vendor['id']}}" type="hidden">
        @endforeach
    </ul>
@endif
