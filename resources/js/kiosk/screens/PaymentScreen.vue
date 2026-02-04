<script setup>
import SystemHeader from "@/kiosk/components/kiosk/SystemHeader.vue";
import PaymentPanel from "@/kiosk/components/kiosk/PaymentPanel.vue";
import PenaltyInfoCard from "@/kiosk/components/kiosk/PenaltyInfoCard.vue";
import CancelPaymentModal from "@/kiosk/components/kiosk/CancelPaymentModal.vue";
import EndSessionConfirmModal from "@/kiosk/components/kiosk/EndSessionConfirmModal.vue";
import RentalPaymentInfoCard from "@/kiosk/components/kiosk/RentalPaymentInfoCard.vue";
import { ref } from "vue";

const showCancelConfirm = ref(false); // to show/hide cancel payment confirmation modal

const props = defineProps({
    locker: {
        type: Number,
        required: false,
    },
    duration: {
        type: Number,
        required: false,
    },
    mode: {
        type: String,
        required: true, // 'RENTAL' | 'PENALTY'
    },
    amount: {
        type: Number,
        required: false, // ❗ NOT always required anymore
    },
    penalty: {
        type: Object,
        default: null,
    },
    lockerEndTime: {
        type: Number,
        required: false,
    },
    show: {
        type: Boolean,
        default: true,
    },
    amountPaid: {
        type: Number,
        required: true,
    },
    paymentStatus: {
        type: String,
        required: true, // 'UNPAID' | 'PAID'
    },
});

const showEndSessionConfirm = ref(false);

const emit = defineEmits([
    "cancel",
    "complete",
    "end-session",
    "session-updated",
]);
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

        <SystemHeader @end-session="showEndSessionConfirm = true" />

        <EndSessionConfirmModal
            :show="showEndSessionConfirm"
            @cancel="showEndSessionConfirm = false"
            @confirm="emit('end-session')"
        />

        <main class="relative z-10 px-12 pt-10">
            <!-- PENALTY CONTEXT -->
            <PenaltyInfoCard
                v-if="mode === 'PENALTY'"
                :lockerNumber="locker"
                :amount="amount"
            />

            <RentalPaymentInfoCard
                v-if="mode === 'RENTAL'"
                :lockerNumber="locker"
                :duration="duration"
                :totalAmount="amount"
            />
            <PaymentPanel
                :locker="locker"
                :duration="duration"
                :mode="mode"
                :amountDue="amount"
                :amountPaid="amountPaid"
                :paymentStatus="paymentStatus"
                @cancel="showCancelConfirm = true"
                @complete="emit('complete')"
                @session-updated="emit('session-updated', $event)"
            />

            <CancelPaymentModal
                :show="showCancelConfirm"
                @close="showCancelConfirm = false"
                @confirm="emit('cancel')"
            />
        </main>
    </div>
</template>
