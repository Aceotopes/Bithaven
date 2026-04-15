<script setup>
defineProps({
    lockerNumber: {
        type: Number,
        required: true,
    },
    duration: {
        type: Number,
        required: true,
    },
    totalAmount: {
        type: Number,
        required: true,
    },
});
</script>

<template>
    <section
        class="mb-10 relative max-w-[920px] mx-auto bg-emerald-50/90 backdrop-blur-xl border border-emerald-300/40 rounded-[24px] px-14 py-10 shadow-[0_24px_60px_rgba(0,0,0,0.12)]"
    >
        <!-- Header -->
        <div class="flex justify-between items-center">
            <p
                class="text-[20px] tracking-[0.35em] uppercase text-emerald-700 font-semibold"
            >
                Locker Rental
            </p>

            <p class="text-[20px] text-gray-700">
                Locker
                <span class="font-mono font-semibold text-gray-900">
                    #{{ lockerNumber }}
                </span>
            </p>
        </div>

        <div class="my-6 h-px bg-emerald-300/40"></div>

        <!-- Instructions -->
        <p class="text-[22px] text-gray-700 leading-relaxed">
            You are about to rent this locker for
            <span class="font-semibold text-gray-900">
                {{ duration }} hour{{ duration > 1 ? "s" : "" }} </span
            >.
        </p>

        <p class="mt-4 text-[20px] text-gray-600 leading-relaxed">
            Please proceed with the payment using the kiosk’s payment interface.
            Once payment is confirmed, the locker will unlock automatically.
        </p>

        <!-- Summary -->
        <div class="my-6 h-px bg-emerald-300/40"></div>

        <div class="flex justify-between items-center">
            <p
                class="text-[22px] tracking-wide uppercase text-emerald-800 font-semibold"
            >
                Total Amount
            </p>

            <p class="text-[28px] font-mono font-bold text-emerald-800">
                ₱{{ totalAmount }}
            </p>
        </div>
        <!-- PAYMENT GUIDELINES -->
        <!-- PAYMENT GUIDELINES -->
        <div
            class="mt-6 rounded-2xl bg-white/70 border border-emerald-300/40 px-10 py-6"
        >
            <p
                class="text-[18px] tracking-widest uppercase text-emerald-700 font-semibold text-center"
            >
                Payment Guidelines
            </p>

            <ul
                class="mt-4 space-y-3 text-[18px] text-gray-700 leading-relaxed"
            >
                <li>
                    • Insert coins
                    <span class="font-semibold">one at a time</span>
                    and wait for the screen to update.
                </li>
                <li>
                    • Do <span class="font-semibold">not</span> insert another
                    coin until the previous amount is registered.
                </li>
                <li>
                    • Do <span class="font-semibold">not</span> leave the kiosk
                    while payment is in progress.
                </li>
                <li>
                    • Please insert the
                    <span class="font-semibold">exact</span> amount.
                </li>
                <li>
                    • Inserted coins
                    <span class="font-semibold">cannot be refunded</span>.
                </li>
            </ul>
        </div>

        <!-- Footer Hint -->
        <p class="mt-6 text-[16px] text-gray-500 text-center">
            Do not leave the kiosk until payment is completed.
        </p>
    </section>
</template>
