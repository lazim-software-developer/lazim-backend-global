{{-- filepath: /d:/Projects/lazim-backend/resources/views/icons/sidebar-notifications.blade.php --}}
<style>
    @keyframes bell-ring {
        0% {
            transform: rotate(0);
        }

        10% {
            transform: rotate(10deg);
        }

        20% {
            transform: rotate(-20deg);
        }

        30% {
            transform: rotate(10deg);
        }

        40% {
            transform: rotate(-10deg);
        }

        50% {
            transform: rotate(0);
        }

        100% {
            transform: rotate(0);
        }
    }

    .bell-ring {
        animation: bell-ring 2s infinite;
        transform-origin: top;
        color: rgb(239 68 68);
        /* text-red-500 */
        filter: drop-shadow(0 0 8px rgb(239 68 68 / 0.5));
    }

    .bell-ring:hover {
        color: rgb(220 38 38);
        /* text-red-600 */
    }
</style>

@props([
    'unread' => false,
    'class' => null,
])
@php
    $unread = auth()->user()?->unreadNotifications->count() > 0;
@endphp

<svg {{ $attributes->class([
    'h-6 w-6 transition-all duration-300',
    'bell-ring' => $unread,
    'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' => !$unread,
    $class,
]) }}
    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round"
        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
</svg>
