<script setup>
//import { ref } from "vue";
// TEMP UI STATE (change this manually for now)
// isPaymentRequired = true if rental expired and penalty must be paid for logic later
// --------------------------------------------

// ============ Mock data (UI only) =======================
// 'NO_RENTAL'
// 'ACTIVE_RENTAL'
// 'EXPIRED_RENTAL'

// const rentalStatus = ref("NO_RENTAL");
// const locker = {
//     number: "023",
//     timeRemaining: "01:42:18",
//     startTime: "01:30 PM",
//     endTime: "03:30 PM",
//     penaltyAmount: "₱50.00",
// };
// ========================================================

defineProps({
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
    penaltyAmount: {
        type: Number,
        required: true,
    },
});

function formatTime(timestamp) {
    if (!timestamp) return "—";

    return new Date(timestamp).toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
    });
}
</script>

<template>
    <section
        class="relative max-w-[920px] mx-auto bg-white/90 backdrop-blur-xl border border-black/10 rounded-[24px] px-14 py-12 shadow-[0_24px_60px_rgba(0,0,0,0.18)]"
    >
        <!-- =========================-->
        <!--    NO ACTIVE RENTAL STATE   -->
        <!-- ========================= -->
        <template v-if="rentalState === 'NO_RENTAL'">
            <div class="flex justify-between items-center">
                <p
                    class="text-[18px] tracking-[0.4em] uppercase text-gray-500 font-semibold"
                >
                    No Active Rental
                </p>

                <p class="text-[18px] tracking-wide text-gray-400">
                    Locker
                    <span class="font-mono font-semibold"> — </span>
                </p>
            </div>

            <!-- Divider -->
            <div class="my-8 h-px bg-black/10"></div>

            <!-- Center Message -->
            <div class="text-center py-10">
                <!-- Placeholder Icon -->
                <div
                    class="mx-auto mb-8 w-24 h-24 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-3xl font-mono"
                >
                    —
                </div>

                <p class="text-[28px] font-semibold text-gray-800">
                    No Locker Assigned
                </p>

                <p class="mt-4 text-[20px] text-gray-500 max-w-[560px] mx-auto">
                    You currently do not have an active locker rental. Please
                    rent a locker below to begin.
                </p>
            </div>
        </template>

        <!-- ============================= -->
        <!--    ACTIVE RENTAL STATE -->
        <!-- ============================= -->
        <template v-else-if="rentalState === 'ACTIVE_RENTAL'">
            <div class="flex justify-between items-center">
                <p
                    class="text-[18px] tracking-[0.4em] uppercase text-emerald-600 font-semibold"
                >
                    Rental Active
                </p>

                <p class="text-[18px] tracking-wide text-gray-600">
                    Locker
                    <span class="font-mono font-semibold text-gray-900">
                        #{{ locker?.number }}
                    </span>
                </p>
            </div>

            <!-- Divider -->
            <div class="my-8 h-px bg-black/10"></div>

            <!-- Countdown Timer -->
            <div class="text-center">
                <p
                    class="font-mono text-[96px] tracking-[0.25em] text-emerald-600"
                >
                    {{ locker?.timeRemaining }}
                </p>

                <p class="mt-2 text-[18px] text-gray-500 tracking-wide">
                    Time Remaining
                </p>
            </div>

            <!-- Bottom Metadata -->
            <div
                class="mt-10 grid grid-cols-2 gap-20 text-center text-gray-600"
            >
                <div>
                    <p class="text-sm tracking-widest uppercase text-gray-500">
                        Start Time
                    </p>
                    <p class="mt-2 font-mono text-[22px] text-gray-900">
                        {{ formatTime(locker?.startTime) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm tracking-widest uppercase text-gray-500">
                        End Time
                    </p>
                    <p class="mt-2 font-mono text-[22px] text-gray-900">
                        {{ formatTime(locker?.endTime) }}
                    </p>
                </div>
            </div>
        </template>

        <!-- ============================= -->
        <!-- EXPIRED / PENALTY STATE -->
        <!-- ============================= -->
        <template v-else-if="rentalState === 'EXPIRED_RENTAL'">
            <!-- Top System Strip -->
            <div class="flex justify-between items-center">
                <p
                    class="text-[18px] tracking-[0.4em] uppercase text-amber-600 font-semibold"
                >
                    Rental Expired
                </p>

                <p class="text-[18px] tracking-wide text-gray-600">
                    Locker
                    <span class="font-mono font-semibold text-gray-900">
                        {{ locker?.number }}
                    </span>
                </p>
            </div>

            <!-- Divider -->
            <div class="my-8 h-px bg-black/10"></div>

            <!-- Expired Message -->
            <div class="text-center py-8">
                <!-- Warning Icon -->
                <div
                    class="mx-auto mb-6 w-24 h-24 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 text-4xl font-mono"
                >
                    !
                </div>

                <p class="text-[30px] font-semibold text-gray-900">
                    Rental Time Exceeded
                </p>

                <p class="mt-4 text-[20px] text-gray-600 max-w-[620px] mx-auto">
                    Your locker rental has exceeded the allowed time. A penalty
                    has been applied and must be settled before the locker can
                    be unlocked.
                </p>
            </div>

            <!-- Penalty Details -->
            <div class="mt-6 grid grid-cols-2 gap-20 text-center text-gray-700">
                <div>
                    <p class="text-sm tracking-widest uppercase text-gray-500">
                        Penalty Amount
                    </p>
                    <p
                        class="mt-2 font-mono text-[26px] text-amber-700 font-semibold"
                    >
                        ₱ {{ penaltyAmount }}
                    </p>
                </div>

                <div>
                    <p class="text-sm tracking-widest uppercase text-gray-500">
                        Status
                    </p>
                    <p class="mt-2 text-[22px] font-semibold text-amber-700">
                        Payment Required
                    </p>
                </div>
            </div>
        </template>
    </section>
</template>
