<script setup>
import ConfirmDialog from "primevue/confirmdialog";
</script>

<template>
    <router-view />
    <toast />
    <ConfirmDialog>
        <template #container="{ message, acceptCallback, rejectCallback }">
            <div
                class="w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-8 text-center"
            >
                <!-- ICON (CENTERED) -->
                <div
                    class="mx-auto w-18 h-18 rounded-full bg-cyan-100 dark:bg-red-900/30 flex items-center justify-center mb-6"
                >
                    <i
                        class="pi pi-trash text-cyan-500 dark:text-red-400"
                        style="font-size: 2rem"
                    ></i>
                </div>

                <!-- TITLE -->
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ message.header }}
                </h3>

                <!-- MESSAGE -->
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    {{ message.message }}
                </p>

                <!-- ACTIONS -->
                <div class="flex justify-center gap-4 mt-8">
                    <Button
                        label="Cancel"
                        severity="secondary"
                        outlined
                        @click="rejectCallback"
                    />

                    <Button
                        label="Delete"
                        icon="pi pi-trash"
                        class="!bg-cyan-500 hover:!bg-cyan-600 !text-white !border-none"
                        @click="acceptCallback"
                    />
                </div>
            </div>
        </template>
    </ConfirmDialog>
</template>
