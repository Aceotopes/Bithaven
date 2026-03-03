<script setup>
import SystemHeader from "@/kiosk/components/kiosk/SystemHeader.vue";
import AdminLockerPanel from "../components/kiosk/AdminLockerPanel.vue";
import AdminLockerDetailsModal from "../components/kiosk/AdminLockerDetailsModal.vue";
import { ref } from "vue";

const props = defineProps({
    lockers: Array,
    selectedLockerDetails: Object,
    showDetails: Boolean,
});

const emit = defineEmits([
    "exit-admin",
    "select-locker",
    "close-details",
    "force-unlock",
    "disable-locker",
    "enable-locker",
    "clear-penalty",
    "end-rental",
]);
</script>

<template>
    <div
        class="relative min-h-screen w-full overflow-hidden bg-gradient-to-b from-slate-50 via-slate-100 to-slate-200"
    >
        <!-- Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div
                class="absolute inset-0 bg-gradient-to-br from-slate-100 via-slate-200 to-slate-300"
            ></div>

            <div
                class="absolute -top-[20%] left-[10%] w-[700px] h-[700px] bg-red-400/20 rounded-full blur-[180px]"
            ></div>
        </div>

        <SystemHeader @end-session="$emit('exit-admin')" />

        <main class="relative z-10 w-full px-16 pt-12">
            <AdminLockerPanel
                :lockers="lockers"
                :selectedLockerDetails="selectedLockerDetails"
                @select-locker="$emit('select-locker', $event)"
                @force-unlock="$emit('force-unlock')"
                @disable-locker="$emit('disable-locker')"
                @enable-locker="$emit('enable-locker')"
                @clear-penalty="$emit('clear-penalty')"
                @end-rental="$emit('end-rental')"
            />
        </main>

        <!-- <AdminLockerDetailsModal
            :show="showDetails"
            :details="selectedLockerDetails"
            @close="$emit('close-details')"
            @force-unlock="$emit('force-unlock')"
            @disable-locker="$emit('disable-locker')"
            @enable-locker="$emit('enable-locker')"
            @clear-penalty="$emit('clear-penalty')"
            @end-rental="$emit('end-rental')"
        /> -->
    </div>
</template>
