<template>
    <header
        class="fixed top-0 left-0 right-0 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 z-40 transition-colors"
    >
        <!-- Left -->
        <div class="flex items-center gap-4">
            <!-- Mobile menu button -->
            <button
                class="lg:hidden text-gray-600"
                @click="$emit('toggle-sidebar')"
            >
                <i class="pi pi-bars text-xl"></i>
            </button>

            <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                Bithaven Admin
            </h1>
        </div>

        <!-- Right -->
        <div class="flex items-center gap-4">
            <button
                @click="$emit('toggle-dark')"
                class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition"
            >
                <i class="pi pi-moon text-lg"></i>
            </button>
            <span class="text-sm text-gray-600 dark:text-gray-300">
                Admin
            </span>

            <div class="w-8 h-8 rounded-full bg-gray-200"></div>
        </div>
    </header>
</template>
