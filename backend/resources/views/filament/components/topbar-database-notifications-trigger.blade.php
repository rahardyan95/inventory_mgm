@php
    use Filament\Support\Icons\Heroicon;
@endphp

<span class="fi-no-database-trigger relative inline-flex h-10 w-10 items-center justify-center">
    <x-filament::icon-button
        color="gray"
        :icon="Heroicon::OutlinedBell"
        icon-size="lg"
        :label="__('filament-panels::layout.actions.open_database_notifications.label')"
        class="fi-topbar-database-notifications-btn !h-9 !w-9 transition duration-200 hover:scale-105 hover:!bg-gray-100 hover:!text-primary-500 dark:hover:!bg-gray-800 dark:hover:!text-primary-400"
    />

    @if ($unreadNotificationsCount > 0)
        <span
            class="fi-no-database-badge absolute top-0 right-0 z-10 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-danger-500 px-[5px] text-[10px] font-bold leading-none text-white shadow-md shadow-danger-500/40 ring-2 ring-white dark:ring-gray-950"
        >
            {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
        </span>
        <span
            class="fi-no-database-ping absolute top-0 right-0 h-[18px] w-[18px] rounded-full bg-danger-500"
            style="animation: fi-notif-ping 1.6s cubic-bezier(0, 0, 0.2, 1) infinite;"
        ></span>
    @endif
</span>

<style>
    @keyframes fi-notif-ping {
        0% {
            transform: scale(1);
            opacity: 0.6;
        }
        75%, 100% {
            transform: scale(2.4);
            opacity: 0;
        }
    }
</style>
