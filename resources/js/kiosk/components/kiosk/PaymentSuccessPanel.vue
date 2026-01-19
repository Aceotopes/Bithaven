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
            class="text-[18px] tracking-[0.4em] uppercase text-emerald-600 font-semibold"
        >
            Payment Successful
        </p>

        <!-- Divider -->
        <div class="my-8 h-px bg-black/10"></div>

        <!-- Icon -->
        <div
            class="mx-auto mb-8 w-28 h-28 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-5xl font-mono"
        >
            ✓
        </div>

        <!-- Message -->
        <p class="text-[30px] font-semibold text-gray-900">Payment Received</p>

        <p class="mt-4 text-[20px] text-gray-600 max-w-[600px] mx-auto">
            Your payment has been successfully processed. Your locker is now
            being prepared.
        </p>

        <p class="mt-6 text-[16px] text-gray-500 tracking-wide">
            Please wait while the system completes the setup.
        </p>
    </section>
</template>
