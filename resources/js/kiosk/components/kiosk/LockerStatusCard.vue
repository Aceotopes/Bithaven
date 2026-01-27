<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
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

const props = defineProps({
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

const now = ref(Date.now());
let tickTimer = null;

function formatTime(timestamp) {
    if (!timestamp) return "—";

    return new Date(timestamp).toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
    });
}

onMounted(() => {
    tickTimer = setInterval(() => {
        now.value = Date.now();
    }, 1000);
});

onBeforeUnmount(() => {
    if (tickTimer) {
        clearInterval(tickTimer);
        tickTimer = null;
    }
});

const exceededMs = computed(() => {
    if (!props.locker || typeof props.locker.endTime !== "number") {
        return 0;
    }

    return Math.max(0, now.value - props.locker.endTime);
});

function formatDuration(ms) {
    const totalSeconds = Math.floor(ms / 1000);
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;

    return [h, m, s].map((v) => String(v).padStart(2, "0")).join(":");
}

const exceededTimeFormatted = computed(() => {
    return formatDuration(exceededMs.value);
});
</script>

<template>
    <section
        class="relative w-full bg-white/90 backdrop-blur-xl border border-black/10 rounded-[28px] px-20 py-16 shadow-[0_30px_80px_rgba(0,0,0,0.2)]"
    >
        <!-- =========================-->
        <!-- NO ACTIVE RENTAL STATE -->
        <!-- ========================= -->
        <template v-if="rentalState === 'NO_RENTAL'">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <p
                    class="text-[20px] tracking-[0.45em] uppercase text-gray-500 font-semibold"
                >
                    No Active Rental
                </p>

                <p class="text-[20px] tracking-wide text-gray-400">
                    Locker
                    <span class="font-mono font-semibold"> — </span>
                </p>
            </div>

            <div class="my-12 h-px bg-black/10"></div>

            <!-- Center Message -->
            <div class="text-center py-16">
                <div
                    class="mx-auto mb-10 w-28 h-28 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-4xl font-mono"
                >
                    —
                </div>

                <p class="text-[34px] font-semibold text-gray-800">
                    No Locker Assigned
                </p>

                <p class="mt-6 text-[22px] text-gray-500 max-w-[680px] mx-auto">
                    You do not currently have an active locker rental. Please
                    use the actions below to rent a locker.
                </p>
            </div>
        </template>

        <!-- ============================= -->
        <!-- ACTIVE RENTAL STATE -->
        <!-- ============================= -->
        <template v-else-if="rentalState === 'ACTIVE_RENTAL'">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <p
                    class="text-[20px] tracking-[0.45em] uppercase text-emerald-600 font-semibold"
                >
                    Rental Active
                </p>

                <p class="text-[20px] tracking-wide text-gray-600">
                    Locker
                    <span class="font-mono font-semibold text-gray-900">
                        #{{ locker?.number }}
                    </span>
                </p>
            </div>

            <div class="my-12 h-px bg-black/10"></div>

            <!-- Countdown -->
            <div class="text-center">
                <p
                    class="font-mono text-[120px] tracking-[0.3em] text-emerald-600 leading-none"
                >
                    {{ locker?.timeRemaining }}
                </p>

                <p class="mt-4 text-[20px] text-gray-500 tracking-wide">
                    Time Remaining
                </p>
            </div>

            <!-- Metadata -->
            <div
                class="mt-16 grid grid-cols-2 gap-24 text-center text-gray-700"
            >
                <div>
                    <p
                        class="text-[14px] tracking-widest uppercase text-gray-500"
                    >
                        Start Time
                    </p>
                    <p class="mt-3 font-mono text-[26px] text-gray-900">
                        {{ formatTime(locker?.startTime) }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-[14px] tracking-widest uppercase text-gray-500"
                    >
                        End Time
                    </p>
                    <p class="mt-3 font-mono text-[26px] text-gray-900">
                        {{ formatTime(locker?.endTime) }}
                    </p>
                </div>
            </div>
        </template>

        <!-- ============================= -->
        <!-- EXPIRED / PENALTY STATE -->
        <!-- ============================= -->
        <template v-else-if="rentalState === 'EXPIRED_RENTAL'">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <p
                    class="text-[20px] tracking-[0.45em] uppercase text-amber-600 font-semibold"
                >
                    Rental Expired
                </p>

                <p class="text-[20px] tracking-wide text-gray-600">
                    Locker
                    <span class="font-mono font-semibold text-gray-900">
                        #{{ locker?.number }}
                    </span>
                </p>
            </div>

            <div class="my-12 h-px bg-black/10"></div>

            <!-- Expired Message -->
            <div class="text-center py-12">
                <div
                    class="mx-auto mb-8 w-28 h-28 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 text-[48px] font-mono"
                >
                    !
                </div>

                <p class="text-[36px] font-semibold text-gray-900">
                    Rental Time Exceeded
                </p>

                <!-- Exceeded Timer -->
                <div class="mt-10">
                    <p
                        class="font-mono text-[120px] tracking-[0.3em] text-amber-600 leading-none"
                    >
                        {{ exceededTimeFormatted }}
                    </p>

                    <p class="mt-4 text-[20px] text-gray-500 tracking-wide">
                        Exceeded Time
                    </p>
                </div>

                <p class="mt-6 text-[22px] text-gray-600 max-w-[720px] mx-auto">
                    The locker rental period has ended. A penalty is now active
                    and must be settled to unlock the locker.
                </p>

                <p class="mt-3 text-[18px] text-gray-500">
                    Penalty charges continue until payment is completed.
                </p>
            </div>

            <!-- Penalty Summary -->
            <div
                class="mt-12 grid grid-cols-2 gap-24 text-center text-gray-700"
            >
                <div>
                    <p
                        class="text-[14px] tracking-widest uppercase text-gray-500"
                    >
                        Penalty Amount
                    </p>
                    <p
                        class="mt-3 font-mono text-[32px] text-amber-700 font-semibold"
                    >
                        ₱ {{ penaltyAmount }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-[14px] tracking-widest uppercase text-gray-500"
                    >
                        Status
                    </p>
                    <p class="mt-3 text-[26px] font-semibold text-amber-700">
                        Payment Required
                    </p>
                </div>
            </div>
        </template>
    </section>
</template>
