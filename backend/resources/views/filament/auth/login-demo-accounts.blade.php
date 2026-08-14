<div class="fi-auth-demo-accounts mt-6">
    <div
        class="fi-auth-demo-heading text-center text-sm font-medium text-gray-500 dark:text-gray-400"
    >
        Akun Demo — klik untuk mengisi otomatis
    </div>

    <div class="mt-3 grid grid-cols-1 gap-2">
        <button
            type="button"
            wire:click="fillDemoAccount('admin@inventory.test', 'password')"
            class="fi-auth-demo-btn group flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white/70 px-4 py-3 text-left transition hover:border-primary-400 hover:bg-primary-50 dark:border-gray-700 dark:bg-gray-800/70 dark:hover:border-primary-500 dark:hover:bg-primary-950/40"
        >
            <span class="flex items-center gap-3">
                <span
                    class="fi-demo-avatar flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-500 text-sm font-semibold text-white"
                >SA</span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold text-gray-900 dark:text-white">Super Admin</span>
                    <span class="block truncate text-xs text-gray-500 dark:text-gray-400">admin@inventory.test</span>
                </span>
            </span>
            <span class="text-xs text-gray-400 transition group-hover:text-primary-500 dark:text-gray-500">Isi →</span>
        </button>

        <button
            type="button"
            wire:click="fillDemoAccount('manager@inventory.test', 'password')"
            class="fi-auth-demo-btn group flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white/70 px-4 py-3 text-left transition hover:border-primary-400 hover:bg-primary-50 dark:border-gray-700 dark:bg-gray-800/70 dark:hover:border-primary-500 dark:hover:bg-primary-950/40"
        >
            <span class="flex items-center gap-3">
                <span
                    class="fi-demo-avatar flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500 text-sm font-semibold text-white"
                >MN</span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold text-gray-900 dark:text-white">Manajer</span>
                    <span class="block truncate text-xs text-gray-500 dark:text-gray-400">manager@inventory.test</span>
                </span>
            </span>
            <span class="text-xs text-gray-400 transition group-hover:text-primary-500 dark:text-gray-500">Isi →</span>
        </button>

        <button
            type="button"
            wire:click="fillDemoAccount('staff@inventory.test', 'password')"
            class="fi-auth-demo-btn group flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white/70 px-4 py-3 text-left transition hover:border-primary-400 hover:bg-primary-50 dark:border-gray-700 dark:bg-gray-800/70 dark:hover:border-primary-500 dark:hover:bg-primary-950/40"
        >
            <span class="flex items-center gap-3">
                <span
                    class="fi-demo-avatar flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-500 text-sm font-semibold text-white"
                >ST</span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold text-gray-900 dark:text-white">Staf Gudang</span>
                    <span class="block truncate text-xs text-gray-500 dark:text-gray-400">staff@inventory.test</span>
                </span>
            </span>
            <span class="text-xs text-gray-400 transition group-hover:text-primary-500 dark:text-gray-500">Isi →</span>
        </button>
    </div>
</div>
