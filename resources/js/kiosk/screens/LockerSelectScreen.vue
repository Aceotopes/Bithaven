<script setup>
import SystemHeader from "@/kiosk/components/kiosk/SystemHeader.vue";
import LockerSelectionPanel from "../components/kiosk/LockerSelectionPanel.vue";
import EndSessionConfirmModal from "@/kiosk/components/kiosk/EndSessionConfirmModal.vue";
import { ref } from "vue";
// UI-only screen
// Logic will be wired later

const props = defineProps({
    show: {
        type: Boolean,
        default: true,
    },
    lockers: {
        type: Array,
        default: () => [],
    },
});

const showEndSessionConfirm = ref(false);

const emit = defineEmits(["back", "confirm", "end-session"]);
</script>

<template>
    <div
        class="relative min-h-screen w-full overflow-hidden bg-gradient-to-b from-slate-50 via-slate-100 to-slate-200"
    >
        <!-- Ambient Background -->
        <div class="absolute inset-0 pointer-events-none">
            <div
                class="absolute -top-40 left-1/2 -translate-x-1/2 w-[720px] h-[720px] bg-emerald-400/20 rounded-full blur-3xl"
            />

            <div
                class="absolute inset-0 bg-[linear-gradient(rgba(0,0,0,0.04)_1px,transparent_1px), linear-gradient(90deg,rgba(0,0,0,0.04)_1px,transparent_1px)] bg-[size:48px_48px]"
            />
        </div>

        <!-- System Header -->
        <SystemHeader @end-session="showEndSessionConfirm = true" />

        <EndSessionConfirmModal
            :show="showEndSessionConfirm"
            @cancel="showEndSessionConfirm = false"
            @confirm="emit('end-session')"
        />

        <!-- Main Content -->
        <main class="relative z-10 w-full px-16 pt-12">
            <!-- Screen Title and Instruction -->

            <LockerSelectionPanel
                :lockers="lockers"
                @back="$emit('back')"
                @confirm="$emit('confirm', $event)"
            />
        </main>
    </div>
</template>
