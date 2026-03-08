<script setup>
import SystemHeader from "@/kiosk/components/kiosk/SystemHeader.vue";
import PaymentPanel from "@/kiosk/components/kiosk/PaymentPanel.vue";
import PenaltyInfoCard from "@/kiosk/components/kiosk/PenaltyInfoCard.vue";
import CancelPaymentModal from "@/kiosk/components/kiosk/CancelPaymentModal.vue";
import EndSessionConfirmModal from "@/kiosk/components/kiosk/EndSessionConfirmModal.vue";
import RentalPaymentInfoCard from "@/kiosk/components/kiosk/RentalPaymentInfoCard.vue";
import SystemFooter from "@/kiosk/components/kiosk/SystemFooter.vue";

import ProcessingScreen from "@/kiosk/screens/ProcessingScreen.vue";
import UnlockSuccessScreen from "@/kiosk/screens/UnlockSuccessScreen.vue";

import { ref } from "vue";

const showCancelConfirm = ref(false); // to show/hide cancel payment confirmation modal
const currentStage = ref("PAYMENT");

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
        required: false,
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
    penaltySnapshot: {
        type: Object,
        default: null,
    },
    exceededDuration: {
        type: String,
        default: "",
    },
});

const showEndSessionConfirm = ref(false);

const emit = defineEmits([
    "cancel",
    "complete",
    "end-session",
    "session-updated",
]);

function handlePaymentComplete() {
    currentStage.value = "PROCESSING";

    // simulate unlock job processing
    setTimeout(() => {
        currentStage.value = "SUCCESS";
    }, 5000);
}
</script>

<template>
    <div
        class="relative min-h-screen w-full overflow-hidden bg-gradient-to-b from-slate-50 via-slate-100 to-slate-200"
    >
        <!-- Background: Geometric Gradient -->
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

        <SystemHeader @end-session="showEndSessionConfirm = true" />

        <EndSessionConfirmModal
            :show="showEndSessionConfirm"
            @cancel="showEndSessionConfirm = false"
            @confirm="emit('end-session')"
        />

        <main class="relative z-10 px-12 pt-10">
            <template v-if="currentStage === 'PAYMENT'">
                <!-- PENALTY CONTEXT -->
                <PenaltyInfoCard
                    v-if="mode === 'PENALTY' && penaltySnapshot"
                    :lockerNumber="locker"
                    :exceededDuration="penaltySnapshot.exceededDuration"
                    :breakdown="penaltySnapshot.breakdown"
                    :amount="Number(amount)"
                />

                <RentalPaymentInfoCard
                    v-if="mode === 'RENTAL'"
                    :lockerNumber="locker"
                    :duration="duration"
                    :totalAmount="amount"
                />
                <!-- @complete="emit('complete')" -->
                <PaymentPanel
                    :locker="locker"
                    :duration="duration"
                    :mode="mode"
                    :amountDue="amount"
                    :amountPaid="amountPaid"
                    :paymentStatus="paymentStatus"
                    @cancel="showCancelConfirm = true"
                    @complete="handlePaymentComplete"
                    @session-updated="emit('session-updated', $event)"
                />

                <CancelPaymentModal
                    :show="showCancelConfirm"
                    @close="showCancelConfirm = false"
                    @confirm="emit('cancel')"
                />
            </template>

            <!-- PROCESSING -->
            <ProcessingScreen
                v-if="currentStage === 'PROCESSING'"
                :locker="locker"
            />

            <!-- SUCCESS -->
            <UnlockSuccessScreen
                v-if="currentStage === 'SUCCESS'"
                :locker="locker"
                @done="emit('complete')"
            />
        </main>
        <SystemFooter />
    </div>
</template>
