@props(['notifications', 'unreadNotificationsCount'])

@php
    $host = request()->getHost();
    $tenantId = explode('.', $host)[0];
@endphp

<div {{ $attributes->class('mt-2 flex gap-x-3') }}>
    @if ($unreadNotificationsCount)
        <x-filament::link color="primary" tabindex="-1" tag="button" wire:click="markAllNotificationsAsRead">
            {{ __('filament-notifications::database.modal.actions.mark_all_as_read.label') }}
        </x-filament::link>
    @endif

    <x-filament::link tag="a" href="{{ route('filament.admin.resources.notification-lists.index', ['tenant' => $tenantId]) }}"
        color="primary" size="sm" class="font-bold">
        View All
    </x-filament::link>
    <x-filament::link color="danger" tabindex="-1" tag="button" wire:click="clearNotifications" x-on:click="close()">
        {{ __('filament-notifications::database.modal.actions.clear.label') }}
    </x-filament::link>
</div>
