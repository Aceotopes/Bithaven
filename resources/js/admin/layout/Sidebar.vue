<script setup>
import { useRoute } from "vue-router";
const route = useRoute();
</script>

<template>
    <aside
        class="fixed lg:top-20 lg:left-8 lg:bottom-8 lg:w-72 top-0 left-0 bottom-0 w-80 bg-white dark:bg-gray-800 lg:rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col p-8 transition-all duration-300"
    >
        <!-- Mobile Close -->
        <div class="flex justify-between items-center lg:hidden mb-8">
            <h2 class="text-lg font-semibold">Menu</h2>
            <button
                @click="$emit('close')"
                class="text-gray-500 hover:text-gray-800"
            >
                <i class="pi pi-times text-xl"></i>
            </button>
        </div>

        <!-- Desktop Title -->
        <div class="hidden lg:block mb-10">
            <h2
                class="text-xs font-semibold text-gray-400 uppercase tracking-widest"
            >
                Menu
            </h2>
        </div>

        <!-- Links -->
        <nav class="flex flex-col gap-3 text-[15px]">
            <RouterLink
                to="/admin/home"
                class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-200"
                :class="
                    route.path.startsWith('/admin/home')
                        ? 'bg-cyan-500 text-white shadow-lg'
                        : 'text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700'
                "
            >
                <i class="pi pi-home text-lg"></i>
                <span>Dashboard</span>
            </RouterLink>

            <RouterLink
                to="/admin/live"
                class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-200"
                :class="
                    route.path.startsWith('/admin/live')
                        ? 'bg-blue-600 text-white shadow-lg'
                        : 'text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700'
                "
            >
                <i class="pi pi-bolt text-lg"></i>
                <span>Live Operations</span>
            </RouterLink>

            <RouterLink
                to="/admin/students"
                class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-200"
                :class="
                    route.path.startsWith('/admin/students')
                        ? 'bg-blue-600 text-white shadow-lg'
                        : 'text-gray-600 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700'
                "
            >
                <i class="pi pi-users text-lg"></i>
                <span>Students</span>
            </RouterLink>
        </nav>

        <!-- Bottom -->
        <div class="mt-auto pt-8 border-t border-gray-200">
            <button
                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition"
            >
                Logout
            </button>
        </div>
    </aside>
</template>
