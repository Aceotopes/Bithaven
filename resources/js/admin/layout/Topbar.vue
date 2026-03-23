<script setup>
import { computed, ref, onMounted, onUnmounted } from "vue";
import { useAuthStore } from "@/admin/stores/auth";
import { useRouter } from "vue-router";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import axios from "axios";
import BithavenLogo from "@/kiosk/assets/idle/BithavenLogo2.svg";

const emit = defineEmits([
    "toggle-sidebar",
    "toggle-dark",
    "open-super-admin",
    "open-account-settings",
]);

let interval = null;

const auth = useAuthStore();
const router = useRouter();
const confirm = useConfirm();
const toast = useToast();

const menu = ref(null);

const adminName = computed(() => auth.admin?.name || "Admin");

const daemonStatus = ref("OFFLINE");
const lastSeenHuman = ref(null);

function toggleMenu(event) {
    menu.value.toggle(event);
}

function confirmLogout() {
    confirm.require({
        group: "action",
        message: "Are you sure you want to logout?",
        header: "Confirm Logout",
        icon: "pi pi-exclamation-triangle",
        acceptClass: "p-button-danger",
        acceptLabel: "Logout",
        rejectLabel: "Cancel",
        accept: async () => {
            await auth.logout();
            toast.add({
                severity: "success",
                summary: "Logged out",
                detail: "You have been logged out successfully.",
                life: 3000,
            });
            router.push("/admin/login");
        },
    });
}

async function fetchDaemonStatus() {
    try {
        const res = await axios.get("/admin/daemon/status");

        daemonStatus.value = res.data.status;
        lastSeenHuman.value = res.data.last_seen_human;
    } catch (e) {
        daemonStatus.value = "OFFLINE";
        lastSeenHuman.value = null;
    }
}

const items = computed(() => [
    {
        label: "Account Settings",
        icon: "pi pi-cog",
        command: () => emit("open-account-settings"),
    },
    {
        label: "Developer Options",
        icon: "pi pi-shield",
        disabled: !auth.isSuperAdmin,
        command: () => emit("open-super-admin"),
    },
    { separator: true },
    {
        label: "Logout",
        icon: "pi pi-sign-out",
        command: confirmLogout,
    },
]);

onMounted(() => {
    fetchDaemonStatus();

    interval = setInterval(() => {
        fetchDaemonStatus();
    }, 5000); // every 5 seconds
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>

<template>
    <header
        class="fixed top-0 left-0 right-0 h-17 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6 z-40 transition-colors"
    >
        <!-- Left -->
        <div class="flex items-center gap-4">
            <button
                class="lg:hidden text-gray-600 dark:text-gray-300"
                @click="$emit('toggle-sidebar')"
            >
                <i class="pi pi-bars text-xl"></i>
            </button>

            <div class="flex items-center gap-3">
                <div class="w-70 h-12">
                    <img :src="BithavenLogo" alt="BithavenLogo" />
                </div>
            </div>
        </div>

        <!-- Right -->
        <div class="flex items-center gap-4">
            <button @click="$emit('toggle-dark')">
                <i class="pi pi-moon text-lg"></i>
            </button>
            <div class="flex items-center gap-2 text-sm">
                <span
                    class="w-2.5 h-2.5 rounded-full"
                    :class="{
                        'bg-green-500': daemonStatus === 'ONLINE',
                        'bg-yellow-500': daemonStatus === 'STALE',
                        'bg-red-500': daemonStatus === 'OFFLINE',
                    }"
                ></span>

                <div class="flex flex-col leading-tight">
                    <span class="text-gray-600 dark:text-gray-300">
                        {{ daemonStatus }}
                    </span>
                    <small class="text-gray-500">Daemon Status</small>

                    <!-- <small v-if="lastSeenHuman" class="text-xs text-gray-400">
                        {{ lastSeenHuman }}
                    </small> -->
                </div>
            </div>

            <span class="hidden sm:block">
                {{ adminName }}
            </span>

            <div class="relative">
                <button
                    @click="toggleMenu"
                    class="flex items-center justify-center"
                >
                    <Avatar
                        v-if="auth.admin?.photo_url"
                        :image="`/storage/${auth.admin.photo_url}`"
                        shape="circle"
                        size="xlarge"
                    />

                    <Avatar
                        v-else
                        :label="adminName.charAt(0).toUpperCase()"
                        shape="circle"
                        size="large"
                        style="background-color: #00bcd4; color: #ffffff"
                    />
                </button>

                <Menu ref="menu" :model="items" popup />
            </div>
        </div>
    </header>
</template>
