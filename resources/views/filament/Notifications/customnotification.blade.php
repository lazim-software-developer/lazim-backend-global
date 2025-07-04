{{-- <x-filament-notifications::notification :notification="$notification" class="flex w-80 rounded-lg transition duration-200"
    x-transition:enter-start="opacity-0" x-transition:leave-end="opacity-0">

    <a href="{{ \App\Filament\Resources\NotificationListResource::getUrl() }}"
        class="absolute top-2 right-8 text-xs text-blue-600 hover:underline">
        View All
    </a>

    
        <h3 fi-no-notification-title text-sm font-medium text-gray-950 dark:text-white>
            {{ $getTitle() }}
        </h3>

        <p>
            {{ $getDate() }}
        </p>

        <div class="fi-no-notification-body overflow-hidden break-words text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $getBody() }}
        </div>
        <p>
            {{ $getIcon() }}
        </p>
        <p>
            {{ $getType() }}
        </p>
        <p>
            {{ $getPriority() }}
        </p>
        @if ($getActions())
            @foreach ($getActions() as $action)
                {{ $action }}
            @endforeach
        @endif

   
</x-filament-notifications::notification> --}}

<x-filament-notifications::notification :notification="$notification">
    {{-- Notification content --}}
</x-filament-notifications::notification>
