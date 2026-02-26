<script setup>
import { ref, computed } from "vue";
import { useAuthStore } from "../stores/auth";
import { useRouter } from "vue-router";

const emit = defineEmits(["toggle-sidebar", "toggle-dark"]);

const auth = useAuthStore();
const router = useRouter();

const menu = ref(null);

const adminName = computed(() => auth.admin?.name || "Admin");

function toggleMenu(event) {
    menu.value.toggle(event);
}

async function logout() {
    await auth.logout();
    router.push("/admin/login");
}

const items = computed(() => [
    {
        label: "Account Settings",
        icon: "pi pi-cog",
        command: () => router.push("/admin/account"),
    },
    {
        label: "Super Admin Panel",
        icon: "pi pi-shield",
        disabled: !auth.isSuperAdmin,
        command: () => {
            if (auth.isSuperAdmin) {
                router.push("/admin/manage");
            }
        },
    },
    {
        separator: true,
    },
    {
        label: "Logout",
        icon: "pi pi-sign-out",
        command: logout,
    },
]);
</script>

<template>
    <header
        class="fixed top-0 left-0 right-0 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 z-40 transition-colors"
    >
        <!-- Left -->
        <div class="flex items-center gap-4">
            <button
                class="lg:hidden text-gray-600 dark:text-gray-300"
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
            <!-- Dark toggle -->
            <button
                @click="$emit('toggle-dark')"
                class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition"
            >
                <i class="pi pi-moon text-lg"></i>
            </button>

            <!-- Admin Name -->
            <span
                class="text-sm font-medium text-gray-700 dark:text-gray-300 hidden sm:block"
            >
                {{ adminName }}
            </span>

            <!-- Avatar Button -->
            <div class="relative">
                <button
                    @click="toggleMenu"
                    class="flex items-center justify-center w-9 h-9 rounded-full bg-cyan-500 text-white hover:opacity-90 transition"
                >
                    <Avatar
                        :label="adminName.charAt(0).toUpperCase()"
                        shape="circle"
                        class="bg-cyan-500 text-white"
                    />
                </button>

                <!-- Dropdown -->
                <Menu ref="menu" :model="items" popup />
            </div>
        </div>
    </header>
</template>
