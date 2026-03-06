<script setup>
import { ref } from "vue";
import processingAnimation from "@/kiosk/animations/processingcat.json";

const props = defineProps({
    locker: Number,
});

// const messages = [
//     "Preparing locker...",
//     "Checking system...",
//     "Unlocking compartment...",
// ];

// const currentMessage = ref(messages[0]);

// let index = 0;

// setInterval(() => {
//     index = (index + 1) % messages.length;
//     currentMessage.value = messages[index];
// }, 1800);
</script>

<template>
    <div
        class="fixed inset-0 z-[100] flex flex-col items-center justify-center pb-[20vh] bg-gradient-to-b from-slate-50 via-slate-100 to-slate-200 text-center px-12"
    >
        <!-- Animation -->
        <Vue3Lottie
            :animationData="processingAnimation"
            :height="680"
            :width="880"
        />

        <!-- Main Message -->
        <p
            class="text-[52px] font-bold tracking-wide text-gray-900 leading-tight"
        >
            Preparing Locker
        </p>

        <!-- Locker Context -->
        <p
            class="mt-4 text-[42px] font-mono font-semibold text-emerald-600 tracking-widest"
        >
            #{{ String(locker).padStart(2, "0") }}
        </p>

        <!-- Secondary Message -->
        <p class="mt-6 text-[28px] text-gray-500 max-w-[720px] leading-relaxed">
            Please wait while the system prepares your locker.
        </p>

        <!-- Footer Hint -->
        <p class="absolute bottom-16 text-[20px] text-gray-400 tracking-wide">
            Do not leave the kiosk while processing
        </p>
    </div>
</template>
