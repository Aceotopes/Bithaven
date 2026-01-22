<script setup>
import { ref, computed, watch, onBeforeUnmount } from "vue";

/* =============================
   PROPS & EMITS
============================= */
const emit = defineEmits(["cancel", "complete"]);

const props = defineProps({
    locker: Number,
    duration: Number,
    amount: Number,
});

/* =============================
   BASE STATE
============================= */
//const pricePerHour = 5; // mock pricing
const insertedAmount = ref(0);
const hasCompleted = ref(false);
const successCountdown = ref(3);
let countdownTimer = null;

/* =============================
   COMPUTED VALUES
============================= */
const amountDue = computed(() => {
    return props.amount || 0;
});

const isPaid = computed(() => {
    if (amountDue.value === 0) return false;
    return insertedAmount.value >= amountDue.value;
});

const progressRatio = computed(() => {
    if (amountDue.value === 0) return 0;
    return Math.min(insertedAmount.value / amountDue.value, 1);
});

const remainingAmount = computed(() =>
    Math.max(amountDue.value - insertedAmount.value, 0)
);

/* =============================
   WATCHERS
============================= */
watch(isPaid, (paid) => {
    if (!paid || hasCompleted.value) return;

    hasCompleted.value = true;
    successCountdown.value = 3;

    countdownTimer = setInterval(() => {
        successCountdown.value--;

        if (successCountdown.value === 0) {
            clearInterval(countdownTimer);
            countdownTimer = null;
            emit("complete");
        }
    }, 1000);
});

/* =============================
   METHODS
============================= */
function insertCoin(value) {
    insertedAmount.value += value;
}

/* =============================
   CLEANUP
============================= */
onBeforeUnmount(() => {
    if (countdownTimer) {
        clearInterval(countdownTimer);
        countdownTimer = null;
    }
});
</script>

<template>
    <section
        class="relative max-w-[920px] mx-auto bg-white/90 backdrop-blur-xl border border-black/10 rounded-[24px] px-14 py-12 shadow-[0_24px_60px_rgba(0,0,0,0.18)]"
    >
        <!-- ========================= -->
        <!-- SYSTEM STRIP -->
        <!-- ========================= -->
        <div class="flex justify-between items-center">
            <p
                class="text-[24px] tracking-[0.4em] uppercase text-gray-500 font-semibold"
            >
                Payment
            </p>

            <p class="text-[24px] text-gray-600">
                Locker
                <span class="font-mono font-semibold text-gray-900">
                    #{{ locker }}
                </span>
                · {{ duration }} Hour<span v-if="duration > 1">s</span>
            </p>
        </div>

        <!-- Divider -->
        <div class="my-8 h-px bg-black/10"></div>

        <div class="flex flex-col items-center text-center mb-8">
            <p class="text-[36px] font-semibold text-gray-800 tracking-wide">
                INSERT COINS INTO THE SLOT
            </p>
        </div>
        <!-- ========================= -->
        <!-- PROGRESS DISPLAY -->
        <!-- ========================= -->
        <div class="grid grid-cols-1 gap-6 items-center">
            <!-- Ring -->
            <div class="flex justify-center">
                <div class="relative w-[360px] h-[360px]">
                    <svg class="w-full h-full -rotate-90">
                        <!-- Background Track -->
                        <circle
                            cx="180"
                            cy="180"
                            r="150"
                            stroke="rgba(0,0,0,0.08)"
                            stroke-width="20"
                            fill="none"
                        />

                        <!-- Progress Arc -->
                        <circle
                            cx="180"
                            cy="180"
                            r="150"
                            stroke="#10b981"
                            stroke-width="20"
                            fill="none"
                            stroke-linecap="round"
                            :stroke-dasharray="2 * Math.PI * 150"
                            :stroke-dashoffset="
                                (1 - progressRatio) * 2 * Math.PI * 150
                            "
                            class="transition-all duration-500"
                        />
                    </svg>

                    <!-- Center Label -->
                    <div
                        class="absolute inset-0 flex flex-col items-center justify-center"
                    >
                        <p
                            class="text-[56px] font-bold text-emerald-600 font-mono leading-none"
                        >
                            {{ Math.round(progressRatio * 100) }}%
                        </p>

                        <p
                            class="mt-2 text-[22px] tracking-wide text-gray-500 uppercase"
                        >
                            Paid
                        </p>
                    </div>
                </div>
            </div>

            <!-- Numeric -->
            <div class="text-center">
                <p class="text-[120px] font-bold text-gray-900 font-mono">
                    ₱{{ insertedAmount }}
                    <span class="text-gray-400 text-[48px]">
                        / ₱{{ amountDue }}
                    </span>
                </p>

                <p class="mt-2 text-[28px] text-gray-500">
                    {{ remainingAmount }} peso<span v-if="remainingAmount !== 1"
                        >s</span
                    >
                    remaining
                </p>
            </div>

            <!-- MACHINE INSTRUCTION -->
            <div class="flex flex-col items-center text-center mt-4 space-y-2">
                <!-- MACHINE INSTRUCTION -->

                <!-- ACCEPTED COINS -->
                <p class="text-[24px] text-gray-600">Accepts: ₱1 • ₱5 • ₱10</p>

                <!-- WHAT HAPPENS NEXT -->
                <p class="text-[22px] text-gray-500 max-w-[720px]">
                    Locker will open automatically once payment is complete.
                </p>
            </div>
        </div>

        <!-- ========================= -->
        <!-- COIN INPUT (SIMULATION) -->
        <!-- ========================= -->
        <div class="mt-12 grid grid-cols-3 gap-6">
            <button
                class="h-20 rounded-2xl bg-gray-200 text-[22px] font-semibold active:scale-[0.97] transition"
                @click="insertCoin(1)"
            >
                +₱1
            </button>

            <button
                class="h-20 rounded-2xl bg-gray-200 text-[22px] font-semibold active:scale-[0.97] transition"
                @click="insertCoin(5)"
            >
                +₱5
            </button>

            <button
                class="h-20 rounded-2xl bg-gray-200 text-[22px] font-semibold active:scale-[0.97] transition"
                @click="insertCoin(10)"
            >
                +₱10
            </button>
        </div>

        <!-- ========================= -->
        <!-- CANCEL -->
        <!-- ========================= -->
        <div class="mt-12 text-center">
            <button
                class="h-16 px-14 rounded-xl bg-gray-300 text-gray-700 text-[20px] font-semibold transition active:scale-[0.97]"
                @click="emit('cancel')"
            >
                Cancel Payment
            </button>
        </div>

        <!-- ========================= -->
        <!-- SUCCESS OVERLAY -->
        <!-- ========================= -->
        <div
            v-if="isPaid"
            class="absolute inset-0 z-50 bg-white/90 backdrop-blur-sm flex items-center justify-center rounded-[24px]"
        >
            <div class="text-center animate-scale-in max-w-[520px]">
                <div
                    class="mx-auto mb-6 w-24 h-24 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-5xl"
                >
                    ✓
                </div>

                <p class="text-[30px] font-semibold text-gray-900">
                    Payment Successful
                </p>

                <p class="mt-4 text-[20px] text-gray-600 leading-relaxed">
                    Your locker is now being prepared and will open shortly.
                </p>

                <p class="mt-6 text-[18px] text-gray-500">
                    Returning to the start screen in
                    <span class="font-mono font-semibold text-gray-800">
                        {{ successCountdown }}
                    </span>
                    second<span v-if="successCountdown !== 1">s</span>.
                </p>
            </div>
        </div>
    </section>
</template>
