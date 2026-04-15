<script setup>
import SystemHeader from "@/kiosk/components/kiosk/SystemHeader.vue";
import StudentInfoCardV2 from "../components/kiosk/StudentInfoCardV2.vue";
import LockerStatusCard from "../components/kiosk/LockerStatusCard.vue";
import EndRentalConfirmModal from "@/kiosk/components/kiosk/EndRentalConfirmModal.vue";
import AccessLockerModal from "@/kiosk/components/kiosk/AccessLockerModal.vue";
import HowToUseLockerModal from "@/kiosk/components/kiosk/HowToUseLockerModal.vue";
import EndSessionConfirmModal from "@/kiosk/components/kiosk/EndSessionConfirmModal.vue";
import SystemFooter from "@/kiosk/components/kiosk/SystemFooter.vue";

import ProcessingScreen from "@/kiosk/screens/ProcessingScreen.vue";
import UnlockSuccessScreen from "@/kiosk/screens/UnlockSuccessScreen.vue";
import { ref, computed } from "vue";

const DEV_MODE = import.meta.env.DEV; // DEV TOOL FLAG

const unlockStage = ref(null);
const showAccessModal = ref(false);

function handleEndRental() {
    showEndRentalConfirm.value = false;

    unlockStage.value = "PROCESSING";

    // simulate unlock delay
    setTimeout(() => {
        unlockStage.value = "SUCCESS";
    }, 5000);
}

function handleAccessLocker() {
    showAccessModal.value = false;
    unlockMode.value = "ACCESS";
    emit("access-locker");
}

// ===================== End Session Modal Logic (TEMP) =====================
const showEndSessionConfirm = ref(false); // to show/hide end session confirmation modal

const actions = computed(() => {
    if (props.rentalState === "NO_RENTAL") {
        return ["RENT"];
    }

    if (props.rentalState === "ACTIVE_RENTAL") {
        return ["ACCESS", "END"];
    }

    if (props.rentalState === "EXPIRED_RENTAL") {
        return ["SETTLE"];
    }

    return [];
});

const unlockMode = ref(null);

function openEndSessionConfirm() {
    showEndSessionConfirm.value = true;
}

function cancelEndSession() {
    showEndSessionConfirm.value = false;
}

function confirmEndSession() {
    showEndSessionConfirm.value = false;
    emit("end-session");
}
// ========================================================================

const emit = defineEmits([
    "end-session",
    "rent-locker",
    "end-rental",
    "settle-penalty",
    "dismiss-howto",
    "access-locker",

    //DEV
    "dev-go-locker-select",
    "dev-go-payment",
    "dev-reset-session",
]);
const showEndRentalConfirm = ref(false);

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },
    rentalState: {
        type: String,
        required: true,
    },
    locker: {
        type: Object,
        default: null,
    },
    penalty: {
        type: Object,
        default: null,
    },
    canRent: {
        type: Boolean,
        required: true,
    },
    canEndRental: {
        type: Boolean,
        required: true,
    },
    canSettlePenalty: {
        type: Boolean,
        required: true,
    },
    canEndSession: {
        type: Boolean,
        required: true,
    },
    endCountdown: {
        type: Number,
    },
    penaltyAmount: {
        type: Number,
        required: true,
    },
    showHowTo: Boolean,
});
</script>

<template>
    <div
        class="relative min-h-screen w-full overflow-hidden bg-gradient-to-b from-slate-50 via-slate-100 to-slate-200"
    >
        <!-- Ambient Background -->
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

        <!-- Header -->
        <SystemHeader @end-session="openEndSessionConfirm" />

        <!-- Main Content Area -->
        <main
            class="relative z-10 w-full max-w-[1040px] mx-auto px-8 pt-10 space-y-12 pb-24"
        >
            <HowToUseLockerModal
                :show="showHowTo"
                @close="emit('dismiss-howto')"
            />
            <!-- <StudentInfoCard /> -->
            <StudentInfoCardV2 :student="student" />

            <!-- Locker Status state -->
            <LockerStatusCard
                :rentalState="rentalState"
                :locker="locker"
                :penalty="penalty"
                :penaltyAmount="penaltyAmount"
            />

            <!-- end session modal -->
            <EndSessionConfirmModal
                :show="showEndSessionConfirm"
                @cancel="cancelEndSession"
                @confirm="confirmEndSession"
            />

            <!-- Action Buttons -->
            <section
                class="relative w-full bg-white/90 backdrop-blur-xl border border-black/10 rounded-[28px] px-20 py-14 shadow-[0_30px_80px_rgba(0,0,0,0.2)]"
            >
                <!-- Panel Header -->
                <div class="flex justify-between items-center">
                    <p
                        class="text-[20px] tracking-[0.4em] uppercase text-gray-500 font-semibold"
                    >
                        System Actions
                    </p>

                    <p class="text-[18px] tracking-wide text-gray-400">
                        Control Panel
                    </p>
                </div>

                <!-- Divider -->
                <div class="my-12 h-px bg-black/10"></div>

                <!-- Action Buttons -->
                <!-- <div class="grid grid-cols-3 gap-16">
                    Rent Locker (Primary)
                    <button
                        class="h-28 rounded-3xl text-[26px] font-semibold transition-all duration-150 border active:scale-[0.97]"
                        :class="
                            canRent
                                ? 'bg-emerald-600 text-white border-emerald-600 shadow-[0_12px_30px_rgba(16,185,129,0.4)]'
                                : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed'
                        "
                        :disabled="!canRent"
                        @click="emit('rent-locker')"
                    >
                        Rent Locker
                    </button>

                    End Rental (Secondary)
                    <button
                        class="h-28 rounded-3xl text-[26px] font-semibold transition-all duration-150 border active:scale-[0.97]"
                        :class="
                            canEndRental
                                ? 'bg-blue-600 text-white border-blue-600 shadow-[0_12px_30px_rgba(37,99,235,0.4)]'
                                : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed'
                        "
                        :disabled="!canEndRental"
                        @click="showEndRentalConfirm = true"
                    >
                        End Rental
                    </button>

                    Settle Penalty (Attention)
                    <button
                        class="h-28 rounded-3xl text-[26px] font-semibold transition-all duration-150 border active:scale-[0.97]"
                        :class="
                            canSettlePenalty
                                ? 'bg-amber-600 text-white border-amber-600 shadow-[0_12px_30px_rgba(217,119,6,0.4)]'
                                : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed'
                        "
                        :disabled="!canSettlePenalty"
                        @click="$emit('settle-penalty')"
                    >
                        Settle Penalty
                    </button>
                </div> -->

                <div
                    class="grid gap-16"
                    :class="{
                        'grid-cols-1 max-w-[400px] mx-auto':
                            actions.length === 1,
                        'grid-cols-2': actions.length === 2,
                    }"
                >
                    <!-- RENT -->
                    <button
                        v-if="actions.includes('RENT')"
                        class="h-28 rounded-3xl text-[26px] font-semibold bg-emerald-600 text-white border-emerald-600 shadow-[0_12px_30px_rgba(16,185,129,0.4)] active:scale-[0.97]"
                        @click="emit('rent-locker')"
                    >
                        Rent Locker
                    </button>

                    <!-- ACCESS (NEW) -->
                    <button
                        v-if="actions.includes('ACCESS')"
                        class="h-28 rounded-3xl text-[26px] font-semibold bg-emerald-600 text-white border-indigo-600 shadow-[0_12px_30px_rgba(79,70,229,0.4)] active:scale-[0.97]"
                        @click="showAccessModal = true"
                    >
                        Quick Unlock
                    </button>

                    <!-- END RENTAL -->
                    <button
                        v-if="actions.includes('END')"
                        class="h-28 rounded-3xl text-[26px] font-semibold bg-rose-600 text-white border-red-600 shadow-[0_12px_30px_rgba(220,38,38,0.4)] active:scale-[0.97]"
                        @click="showEndRentalConfirm = true"
                    >
                        End Rental
                    </button>

                    <!-- SETTLE PENALTY -->
                    <button
                        v-if="actions.includes('SETTLE')"
                        class="h-28 rounded-3xl text-[26px] font-semibold bg-amber-600 text-white border-amber-600 shadow-[0_12px_30px_rgba(217,119,6,0.4)] active:scale-[0.97]"
                        @click="emit('settle-penalty')"
                    >
                        Settle Penalty
                    </button>
                </div>
            </section>

            <!-- @confirm="emit('end-rental')" -->
            <EndRentalConfirmModal
                :show="showEndRentalConfirm"
                :lockerNumber="locker?.number"
                @cancel="showEndRentalConfirm = false"
                @confirm="emit('end-rental')"
            />
            <AccessLockerModal
                :show="showAccessModal"
                :lockerNumber="locker?.number"
                @cancel="showAccessModal = false"
                @confirm="handleAccessLocker"
            />
        </main>
        <SystemFooter />

        <!-- <div
            v-if="isEndingRental"
            class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm flex items-center justify-center"
        >
            <div
                class="w-[620px] bg-white rounded-[32px] border border-black/10 shadow-[0_40px_100px_rgba(0,0,0,0.35)] px-14 py-12 text-center"
            >
                <div
                    class="mx-auto mb-8 w-24 h-24 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-[48px]"
                >
                    ✓
                </div>

                <p class="text-[36px] font-semibold text-gray-900">
                    Rental Ended
                </p>

                <p class="mt-4 text-[22px] text-gray-600">
                    Locker access has been released successfully.
                </p>

                <div class="mt-8 text-[20px] text-gray-500">
                    Returning to start screen in
                    <span class="font-mono font-semibold text-gray-900">
                        {{ endCountdown }}
                    </span>
                    seconds
                </div>
            </div>
        </div> -->

        <ProcessingScreen
            v-if="unlockStage === 'PROCESSING'"
            :locker="locker?.number"
        />

        <!-- mode="END_RENTAL" -->
        <!-- SUCCESS SCREEN -->
        <UnlockSuccessScreen
            v-if="unlockStage === 'SUCCESS'"
            :locker="locker?.number"
            :mode="unlockMode"
            @done="emit('end-session')"
        />
        <!-- ================= DEV PANEL ================= -->
        <div
            v-if="DEV_MODE"
            class="fixed bottom-20 right-6 z-[9999] w-[240px] rounded-2xl bg-black/80 backdrop-blur border border-white/10 shadow-xl px-5 py-4 text-white"
        >
            <p class="text-[12px] tracking-widest uppercase text-white/60 mb-3">
                DEV MODE
            </p>

            <div class="space-y-3">
                <button
                    class="w-full py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 transition text-sm font-semibold"
                    @click="emit('dev-go-locker-select')"
                >
                    Open Locker Selection
                </button>

                <button
                    class="w-full py-2 rounded-lg bg-amber-600 hover:bg-emerald-700 transition text-sm font-semibold"
                    @click="emit('dev-go-payment')"
                >
                    Open Locker Selection
                </button>

                <button
                    class="w-full py-2 rounded-lg bg-red-600 hover:bg-red-700 transition text-sm font-semibold"
                    @click="emit('dev-reset-session')"
                >
                    Reset Session
                </button>
            </div>
        </div>
        <!-- ============================================ -->
    </div>
</template>

<style>
/* ============== end session modal transition ============== */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

.modal-fade-enter-to,
.modal-fade-leave-from {
    opacity: 1;
    transform: scale(1);
}
/* ========================================================= */
</style>
