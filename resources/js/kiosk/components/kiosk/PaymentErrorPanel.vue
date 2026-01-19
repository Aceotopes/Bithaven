<script setup>
// not used
// UI-only panel
</script>

<template>
    <section
        class="relative max-w-[920px] mx-auto bg-white/90 backdrop-blur-xl border border-black/10 rounded-[24px] px-14 py-12 shadow-[0_24px_60px_rgba(0,0,0,0.18)] text-center"
    >
        <!-- Status Strip -->
        <p
            class="text-[18px] tracking-[0.4em] uppercase text-amber-600 font-semibold"
        >
            Payment Failed
        </p>

        <!-- Divider -->
        <div class="my-8 h-px bg-black/10"></div>

        <!-- Icon -->
        <div
            class="mx-auto mb-8 w-28 h-28 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 text-5xl font-mono"
        >
            !
        </div>

        <!-- Message -->
        <p class="text-[30px] font-semibold text-gray-900">
            Payment Not Completed
        </p>

        <p class="mt-4 text-[20px] text-gray-600 max-w-[640px] mx-auto">
            We were unable to complete your payment. No locker has been
            assigned.
        </p>

        <p class="mt-6 text-[18px] text-gray-500">
            You may try again or cancel the transaction.
        </p>

        <!-- Actions -->
        <div class="mt-10 flex justify-center gap-8">
            <button
                class="h-20 px-12 rounded-2xl bg-gray-200 text-gray-700 text-[22px] font-semibold transition active:scale-[0.97]"
            >
                Cancel
            </button>

            <button
                class="h-20 px-12 rounded-2xl bg-amber-600 text-white text-[22px] font-semibold transition active:scale-[0.97]"
            >
                Try Again
            </button>
        </div>
    </section>
</template>
