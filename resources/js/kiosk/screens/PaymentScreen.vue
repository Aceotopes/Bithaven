<script setup>
import SystemHeader from "@/kiosk/components/kiosk/SystemHeader.vue";
import PaymentPanel from "@/kiosk/components/kiosk/PaymentPanel.vue";

const props = defineProps({
    locker: Number,
    duration: Number,
});

const emit = defineEmits(["cancel"]);
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

        <SystemHeader />

        <main class="relative z-10 px-12 pt-10">
            <PaymentPanel
                :locker="locker"
                :duration="duration"
                @cancel="emit('cancel')"
            />
        </main>
    </div>
</template>
