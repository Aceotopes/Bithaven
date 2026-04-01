<script setup>
import errorAnimation from "@/kiosk/animations/error.json";
const props = defineProps({
    message: {
        type: String,
        default: "Something went wrong",
    },
    locker: {
        type: [String, Number],
        default: null,
    },
});

const emit = defineEmits(["retry", "done"]);
</script>

<template>
    <div
        class="fixed inset-0 z-[100] flex flex-col items-center justify-center pb-[20vh] bg-gradient-to-b from-red-50 via-white to-red-100 text-center px-12"
    >
        <!-- Animation -->
        <Vue3Lottie
            :animationData="errorAnimation"
            :height="520"
            :width="520"
            :loop="false"
        />

        <!-- Main Message -->
        <p
            class="text-[52px] font-bold tracking-wide text-red-600 leading-tight"
        >
            Unlock Failed
        </p>

        <!-- Locker -->
        <p
            v-if="locker"
            class="mt-4 text-[42px] font-mono font-semibold text-gray-900 tracking-widest"
        >
            #{{ String(locker).padStart(2, "0") }}
        </p>

        <!-- Description -->
        <p class="mt-6 text-[28px] text-gray-600 max-w-[720px] leading-relaxed">
            {{ message }}
        </p>

        <!-- Actions -->
        <div class="mt-10 flex gap-6">
            <!-- Retry (Primary) -->
            <button
                class="px-8 py-4 text-[20px] font-semibold rounded-xl bg-red-600 text-white hover:bg-red-700 active:scale-95 transition"
                @click="$emit('retry')"
            >
                Try Again
            </button>

            <!-- Cancel (Secondary) -->
            <button
                class="px-8 py-4 text-[20px] font-semibold rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 active:scale-95 transition"
                @click="$emit('done')"
            >
                Cancel
            </button>
        </div>

        <!-- Footer Hint -->
        <p class="absolute bottom-16 text-[20px] text-gray-400 tracking-wide">
            If the issue persists, please contact support
        </p>
    </div>
</template>
