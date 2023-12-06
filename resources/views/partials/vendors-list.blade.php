<!-- @if($vendors->isEmpty())
    <p>No vendors found for the selected services.</p>
@else
    <ul>
        @foreach($vendors as $vendor)
            <li>{{ $vendor->name }}</li>
            <input id="{{ $vendor['id'] }}" name="vendors[]" value="{{ $vendor['id']}}" type="hidden">
        @endforeach
    </ul>
@endif -->

<div class="border-t border-gray-100 py-6 sm:col-span-2 sm:px-0">
    <!-- <dt class="text-sm font-semibold leading-2 text-gray-900">Matching Vendors</dt> -->
    <div class="pb-5">
  <h3 class="text-base font-semibold leading-6 text-gray-900">Matching Vendors</h3>
</div>
    <dd class="mt-2 text-sm text-gray-900">
        @if($vendors->isEmpty())
            <p>No vendors found for the selected services.</p>
        @else
        <ul role="list" class="divide-y divide-gray-100 rounded-md border border-gray-200  px-4">
        @foreach($vendors as $vendor)
            <li class="flex items-center justify-between py-4 pl-4 pr-5 text-sm leading-6">
                <div class="flex w-0 flex-1 items-center">
                    <div class="ml-4 flex min-w-0 flex-1 gap-2">
                        <span class="truncate font-medium">{{ $vendor->name }}</span>
                        @if($vendor->status && $vendor->status === 'pending')
                        <span class="inline-flex items-center rounded-md bg-pink-50 px-2 py-1 text-xs font-medium text-pink-700 ring-1 ring-inset ring-pink-700/10">{{$vendor->status}}</span>
                        @elseif($vendor->status && $vendor->status === 'approved')
                        <span class="inline-flex items-center rounded-md bg-pink-50 px-2 py-1 text-xs font-medium text-pink-700 ring-1 ring-inset ring-pink-700/10">{{$vendor->status}}</span>
                        @endif
                    </div>
                </div>
                <!-- <div class="ml-4 flex-shrink-0">
                    <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">{{$vendor->tl_number}}</a>
                    <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">{{$vendor->tl_expiry}}</a>
                </div> -->
            </li>
            <input id="{{ $vendor['id'] }}" name="vendors[]" value="{{ $vendor['id']}}" type="hidden">
            @endforeach
        </ul>
        @endif
    </dd>
</div>