<script setup>
import { ref, onMounted } from "vue";
import Sidebar from "./Sidebar.vue";
import Topbar from "./Topbar.vue";

const mobileOpen = ref(false);
const isDark = ref(false);

function toggleDark() {
    isDark.value = !isDark.value;

    document.documentElement.classList.toggle("dark", isDark.value);
    localStorage.setItem("darkMode", isDark.value);
}

onMounted(() => {
    const saved = localStorage.getItem("darkMode") === "true";
    isDark.value = saved;
    document.documentElement.classList.toggle("dark", saved);
});
</script>

<template>
    <div
        class="h-screen bg-gray-100 dark:bg-gray-900 transition-colors overflow-hidden"
    >
        <!-- Topbar -->
        <Topbar @toggle-sidebar="mobileOpen = true" @toggle-dark="toggleDark" />

        <!-- Sidebar (Desktop) -->
        <Sidebar class="hidden lg:flex" />

        <!-- Sidebar (Mobile Drawer) -->
        <div v-if="mobileOpen" class="fixed inset-0 z-50 flex lg:hidden">
            <div
                class="fixed inset-0 bg-black/40 backdrop-blur-sm"
                @click="mobileOpen = false"
            ></div>

            <Sidebar class="relative z-50" @close="mobileOpen = false" />
        </div>

        <!-- Content -->
        <main
            class="absolute top-15 left-0 lg:left-74 right-0 bottom-0 overflow-y-auto p-8"
        >
            <router-view />
        </main>
    </div>
</template>
