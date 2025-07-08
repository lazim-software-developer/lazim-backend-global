<x-filament-notifications::notification :notification="$notification"
    class="filament-notifications-customnotification
 view-all-btn flex w-80 rounded-lg transition duration-200 mb-3"
    x-transition:enter-start="opacity-0" x-transition:leave-end="opacity-0">


    <div style="margin-left: 1rem; display: inline-block;" class="filament-notifications-icon">
        @if ($icon = $getIcon())
            <x-filament-notifications::icon :color="$getIconColor()" :icon="$icon" icon-size="lg"
                class="w-6 h-6 text-primary-600 fi-color-warning mt-3" />
        @endif
    </div>
    <div style="margin-left: .500rem; display: inline-block;">
        <h3 class="fi-no-notification-title text-sm font-medium text-gray-950 dark:text-white mt-3">
            {{ $getTitle() }}
        </h3>

        <p class="fi-no-notification-date text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $getDate() }}
        </p>

        <div class="fi-no-notification-body overflow-hidden break-words text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $getBody() }}
        </div>
        <p
            class="filament-notifications-icon overflow-hidden break-words text-sm text-gray-500 dark:text-gray-400 mt-1 ml-2 overflow-hidden break-words text-sm text-gray-500 dark:text-gray-400 mt-1 ml-2">
            {{ $getType() }}
        </p>


        <p class="fi-no-notification-body overflow-hidden break-words text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $getPriority() }}
        </p>
        <div class="mb-3">
            @if ($getActions())
                @foreach ($getActions() as $action)
                    <div style="margin-bottom: 1rem; display: inline-block;"
                        class="fi-no-notification-actions flex gap-x-3 mt-3 mb-3">
                        {{ $action }}</div>
                @endforeach
            @endif
        </div>
    </div>

</x-filament-notifications::notification>
