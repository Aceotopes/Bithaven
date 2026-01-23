<script setup>
import SystemHeader from "@/kiosk/components/kiosk/SystemHeader.vue";
import StudentInfoCardV2 from "../components/kiosk/StudentInfoCardV2.vue";
import LockerStatusCard from "../components/kiosk/LockerStatusCard.vue";
import EndRentalConfirmModal from "@/kiosk/components/kiosk/EndRentalConfirmModal.vue";
import HowToUseLockerModal from "@/kiosk/components/kiosk/HowToUseLockerModal.vue";
import { ref } from "vue";

// ===================== End Session Modal Logic (TEMP) =====================
// const showEndSessionConfirm = ref(false);            commented out to fix unused var warning

// function cancelEndSession() {                        commented out to fix unused var warning
//     showEndSessionConfirm.value = false;
// }

//function confirmEndSession() {                        commented out to fix unused var warning
//  showEndSessionConfirm.value = false;
// TEMP: logic will be added later
// emit reset / go back to idle
//}
// ========================================================================

const emit = defineEmits([
    "end-session",
    "rent-locker",
    "end-rental",
    "settle-penalty",
    "dismiss-howto",
]);
const showEndRentalConfirm = ref(false);

defineProps({
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
    isEndingRental: {
        type: Boolean,
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
        <div class="absolute inset-0 pointer-events-none">
            <!-- Ambient Glow -->
            <div
                class="absolute -top-40 left-1/2 -translate-x-1/2 w-[720px] h-[720px] bg-emerald-400/25 rounded-full blur-3xl"
            />

            <!-- Engineering Grid -->
            <div
                class="absolute inset-0 bg-[linear-gradient(rgba(0,0,0,0.04)_1px,transparent_1px), linear-gradient(90deg,rgba(0,0,0,0.04)_1px,transparent_1px)] bg-[size:48px_48px]"
            />
        </div>

        <!-- Header -->
        <SystemHeader @end-session="showEndSessionConfirm = true" />

        <!-- Main Content Area -->
        <main
            class="relative z-10 max-w-[920px] mx-auto px-12 pt-10 space-y-10"
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

            <!-- Action Buttons -->
            <section
                class="max-w-[920px] mx-auto mt-10 bg-white/90 backdrop-blur-xl border border-black/10 rounded-[24px] px-14 py-10 shadow-[0_24px_60px_rgba(0,0,0,0.18)]"
            >
                <!-- Panel Header -->
                <div class="flex justify-between items-center">
                    <p
                        class="text-[16px] tracking-[0.4em] uppercase text-gray-500 font-semibold"
                    >
                        System Actions
                    </p>

                    <p class="text-[16px] tracking-wide text-gray-400">
                        Control Panel
                    </p>
                </div>

                <!-- Divider -->
                <div class="my-8 h-px bg-black/10"></div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-3 gap-10">
                    <!-- Rent Locker -->
                    <button
                        class="h-20 rounded-2xl text-[22px] font-semibold transition border"
                        :class="
                            canRent
                                ? 'bg-emerald-600 text-white border-emerald-600'
                                : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed'
                        "
                        :disabled="!canRent"
                        @click="emit('rent-locker')"
                    >
                        Rent Locker
                    </button>

                    <!-- End Rental -->
                    <button
                        class="h-20 rounded-2xl text-[22px] font-semibold transition border"
                        :class="
                            canEndRental
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed'
                        "
                        :disabled="!canEndRental"
                        @click="showEndRentalConfirm = true"
                    >
                        End Rental
                    </button>

                    <!-- Settle Penalty -->
                    <button
                        class="h-20 rounded-2xl text-[22px] font-semibold transition border"
                        :class="
                            canSettlePenalty
                                ? 'bg-amber-600 text-white border-amber-600'
                                : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed'
                        "
                        :disabled="!canSettlePenalty"
                        @click="$emit('settle-penalty')"
                    >
                        Settle Penalty
                    </button>
                </div>
            </section>

            <EndRentalConfirmModal
                :show="showEndRentalConfirm"
                :lockerNumber="locker?.number"
                @cancel="showEndRentalConfirm = false"
                @confirm="emit('end-rental')"
            />

            <!-- End Session Confirmation -->
            <!-- <div
                v-if="showEndSessionConfirm"
                class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center"
            >
                <div
                    class="w-[520px] bg-white rounded-2xl border border-black/10 shadow-[0_30px_80px_rgba(0,0,0,0.25)] px-10 py-8 text-center"
                >
                    Title
                    <p class="text-[26px] font-semibold text-gray-900">
                        End Current Session?
                    </p>

                    Message
                    <p class="mt-4 text-[18px] text-gray-600 leading-relaxed">
                        This will cancel the current session and return the
                        kiosk to the start screen. No actions will be saved.
                    </p>

                    Actions
                    <div class="mt-8 flex justify-center gap-6">
                        <button
                            class="px-6 py-3 rounded-xl bg-gray-200 text-gray-700 text-[18px] font-semibold hover:bg-gray-300 transition"
                            @click="cancelEndSession"
                        >
                            Cancel
                        </button>

                        <button
                            class="px-6 py-3 rounded-xl bg-gray-800 text-white text-[18px] font-semibold hover:bg-black transition"
                            @click="emit('end-session')"
                        >
                            End Session
                        </button>
                    </div>
                </div>
            </div> -->
        </main>
        <div
            v-if="isEndingRental"
            class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm flex items-center justify-center"
        >
            <!-- CONTAINER -->
            <div
                class="w-[620px] bg-white rounded-[32px] border border-black/10 shadow-[0_40px_100px_rgba(0,0,0,0.35)] px-14 py-12 text-center"
            >
                <!-- SUCCESS ICON -->
                <div
                    class="mx-auto mb-8 w-24 h-24 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-[48px]"
                >
                    ✓
                </div>

                <!-- PRIMARY MESSAGE -->
                <p class="text-[36px] font-semibold text-gray-900">
                    Rental Ended
                </p>

                <!-- SECONDARY MESSAGE -->
                <p class="mt-4 text-[22px] text-gray-600">
                    Locker access has been released successfully.
                </p>

                <!-- COUNTDOWN -->
                <div class="mt-8 text-[20px] text-gray-500">
                    Returning to start screen in
                    <span class="font-mono font-semibold text-gray-900">
                        {{ endCountdown }}
                    </span>
                    seconds
                </div>
            </div>
        </div>
    </div>
</template>
