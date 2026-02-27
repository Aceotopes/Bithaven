<script setup>
import { ref } from "vue";
import Dialog from "primevue/dialog";

import AdminManagement from "./AdminManagement.vue";
import CardManagement from "./Cardmanagement.vue";
const props = defineProps({
    visible: Boolean,
});

const emit = defineEmits(["update:visible"]);

const currentView = ref("menu");

function updateVisible(val) {
    if (!val) {
        currentView.value = "menu";
    }
    emit("update:visible", val);
}

function openAdmins() {
    currentView.value = "admins";
}

function openCards() {
    currentView.value = "cards";
}

function goBack() {
    currentView.value = "menu";
}
</script>

<template>
    <div>
        <Dialog
            :visible="visible"
            modal
            :style="{ width: currentView === 'menu' ? '500px' : '700px' }"
            class="rounded-2xl"
            @update:visible="updateVisible"
            :draggable="false"
        >
            <!-- HEADER -->
            <template #header>
                <div class="flex items-center gap-3">
                    <button
                        v-if="currentView !== 'menu'"
                        @click="goBack"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <i class="pi pi-arrow-left"></i>
                    </button>

                    <span class="font-semibold text-lg">
                        {{
                            currentView === "menu"
                                ? "Developer Options"
                                : currentView === "admins"
                                ? "Manage Admins"
                                : "Manage Kiosk Cards"
                        }}
                    </span>
                </div>
            </template>

            <!-- CONTENT -->
            <div class="mt-4 transition-all">
                <!-- MAIN MENU -->
                <div v-if="currentView === 'menu'" class="grid gap-4">
                    <div
                        class="p-6 rounded-2xl border border-gray-200 dark:border-gray-700 hover:shadow-lg transition cursor-pointer bg-white dark:bg-gray-800"
                        @click="openAdmins"
                    >
                        <div class="flex items-center gap-4">
                            <i
                                class="pi pi-users text-cyan-500"
                                style="font-size: 2rem"
                            ></i>
                            <div>
                                <h3
                                    class="font-semibold text-gray-800 dark:text-gray-100"
                                >
                                    Manage Admins
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Create, update and control admin accounts.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="p-6 rounded-2xl border border-gray-200 dark:border-gray-700 hover:shadow-lg transition cursor-pointer bg-white dark:bg-gray-800"
                        @click="openCards"
                    >
                        <div class="flex items-center gap-4">
                            <i
                                class="pi pi-id-card text-cyan-500"
                                style="font-size: 2rem"
                            ></i>
                            <div>
                                <h3
                                    class="font-semibold text-gray-800 dark:text-gray-100"
                                >
                                    Manage Kiosk Cards
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Assign and control kiosk admin RFID cards.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ADMINS VIEW -->
                <AdminManagement
                    v-if="currentView === 'admins'"
                    class="space-y-6"
                />

                <!-- CARDS VIEW -->
                <CardManagement
                    v-if="currentView === 'cards'"
                    class="space-y-6"
                />
            </div>
        </Dialog>
    </div>
</template>
