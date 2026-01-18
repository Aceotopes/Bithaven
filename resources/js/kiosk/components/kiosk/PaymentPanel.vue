<script setup>
// UI-only panel
const props = defineProps({
    locker: Number,
    duration: Number,
});

const emit = defineEmits(["cancel"]);
</script>

<template>
    <section
        class="relative max-w-[920px] mx-auto bg-white/90 backdrop-blur-xl border border-black/10 rounded-[24px] px-14 py-12 shadow-[0_24px_60px_rgba(0,0,0,0.18)]"
    >
        <!-- ========================= -->
        <!-- Top System Strip -->
        <!-- ========================= -->
        <div class="flex justify-between items-center">
            <p
                class="text-[18px] tracking-[0.4em] uppercase text-emerald-600 font-semibold"
            >
                Payment Required
            </p>

            <p class="text-[18px] tracking-wide text-gray-500">
                Locker
                <span class="font-mono font-semibold text-gray-900">{{
                    locker
                }}</span>
            </p>
        </div>

        <!-- Divider -->
        <div class="my-8 h-px bg-black/10"></div>

        <!-- ========================= -->
        <!-- Rental Summary -->
        <!-- ========================= -->
        <div class="grid grid-cols-2 gap-20 text-center text-gray-700">
            <div>
                <p class="text-sm tracking-widest uppercase text-gray-500">
                    Duration
                </p>
                <p class="mt-2 font-mono text-[26px] text-gray-900">
                    {{ duration }} Hour{{ duration > 1 ? "s" : "" }}
                </p>
            </div>

            <div>
                <p class="text-sm tracking-widest uppercase text-gray-500">
                    Rate
                </p>
                <p class="mt-2 font-mono text-[26px] text-gray-900">₱20.00</p>
            </div>
        </div>

        <!-- ========================= -->
        <!-- Amount Due (HERO) -->
        <!-- ========================= -->
        <div class="mt-12 text-center">
            <p class="text-sm tracking-widest uppercase text-gray-500">
                Amount to Pay
            </p>

            <p
                class="mt-4 font-mono text-[96px] text-emerald-600 tracking-wide"
            >
                ₱20
            </p>
        </div>

        <!-- ========================= -->
        <!-- Payment Indicator -->
        <!-- ========================= -->
        <div class="mt-10 text-center">
            <div
                class="mx-auto mb-6 w-24 h-24 rounded-full border-2 border-dashed border-emerald-300 flex items-center justify-center text-emerald-600 text-3xl font-mono"
            >
                ₱
            </div>

            <p class="text-[22px] font-semibold text-gray-800">
                Insert Coins to Continue
            </p>

            <p class="mt-3 text-[18px] text-gray-500 max-w-[560px] mx-auto">
                Please insert the exact amount to proceed. The locker will be
                assigned once payment is complete.
            </p>
        </div>

        <!-- ========================= -->
        <!-- Action -->
        <!-- ========================= -->
        <div class="mt-12 flex justify-center">
            <button
                class="h-20 px-12 rounded-2xl bg-gray-200 text-gray-700 text-[22px] font-semibold transition active:scale-[0.97]"
                @click="emit('cancel')"
            >
                Cancel Payment
            </button>
        </div>
    </section>
</template>
