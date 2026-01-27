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
        <!-- Background: Geometric Gradient -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <!-- Base Gradient -->
            <div
                class="absolute inset-0 bg-gradient-to-br from-slate-100 via-slate-200 to-slate-300"
            ></div>

            <!-- Large Geometric Pattern -->
            <div
                class="absolute inset-0 bg-[linear-gradient( 135deg, rgba(0,0,0,0.04) 25%, transparent 25%, transparent 50%, rgba(0,0,0,0.04) 50%, rgba(0,0,0,0.04) 75%, transparent 75%, transparent )] bg-[size:240px_240px] opacity-25"
            ></div>

            <!-- Accent Glow -->
            <div
                class="absolute -top-[20%] left-[10%] w-[700px] h-[700px] bg-emerald-400/20 rounded-full blur-[180px]"
            ></div>
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
