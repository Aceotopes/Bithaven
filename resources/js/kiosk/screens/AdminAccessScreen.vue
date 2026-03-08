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
            <!-- Base Light Surface -->
            <div
                class="absolute inset-0 bg-gradient-to-b from-white via-slate-50 to-slate-100"
            ></div>

            <!-- Top Cyan Atmosphere -->
            <div
                class="absolute -top-[5%] left-1/2 -translate-x-1/2 w-[1100px] h-[1100px] bg-cyan-300/15 rounded-full blur-[200px]"
            ></div>

            <!-- Mid Soft Light Column -->
            <div
                class="absolute top-[55%] left-1/2 -translate-x-1/2 w-[700px] h-[1200px] bg-white/40 rounded-full blur-[220px]"
            ></div>

            <!-- Bottom Cyan Accent -->
            <div
                class="absolute bottom-[-20%] left-1/2 -translate-x-1/2 w-[1000px] h-[1000px] bg-cyan-400/12 rounded-full blur-[220px]"
            ></div>

            <!-- Subtle Vertical Texture -->
            <div
                class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(0,0,0,0.03)_1px,transparent_1px)] bg-[size:100%_120px] opacity-25"
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
