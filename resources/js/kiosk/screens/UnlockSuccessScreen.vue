<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import successAnimation from "@/kiosk/animations/successunlock.json";

const props = defineProps({
    locker: Number,
    mode: {
        type: String,
        default: "PAYMENT", // PAYMENT | END_RENTAL
    },
});

const emit = defineEmits(["done"]);

const countdown = ref(5);
const totalTime = 5;

const title = computed(() => {
    return props.mode === "END_RENTAL" ? "Rental Ended" : "Locker Opened";
});

const subtitle = computed(() => {
    return props.mode === "END_RENTAL"
        ? "You may now retrieve your belongings."
        : "You may now place your belongings.";
});

let timer = null;

onMounted(() => {
    timer = setInterval(() => {
        countdown.value--;

        if (countdown.value <= 0) {
            clearInterval(timer);
            emit("done");
        }
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <div
        class="fixed inset-0 z-[100] flex flex-col items-center bg-gradient-to-b from-slate-50 via-slate-100 to-slate-200 text-center px-12 pt-[18vh]"
    >
        <!-- Glow Accent -->
        <div
            class="absolute w-[520px] h-[520px] bg-emerald-400/20 blur-[140px] rounded-full"
        ></div>

        <!-- Animation -->
        <Vue3Lottie
            :animationData="successAnimation"
            :height="580"
            :width="550"
            :speed="0.8"
            :loop="false"
        />

        <!-- Title -->
        <p class="text-[54px] font-bold text-gray-900 mt-6 tracking-wide">
            {{ title }}
        </p>

        <!-- Locker Number -->
        <p
            class="text-[38px] font-mono font-semibold text-emerald-600 tracking-widest mt-4"
        >
            #{{ String(locker).padStart(2, "0") }}
        </p>

        <!-- Instruction -->
        <p class="text-[28px] text-gray-600 mt-8 max-w-[720px] leading-relaxed">
            {{ subtitle }}
        </p>

        <!-- Countdown -->
        <div class="mt-14 w-[420px]">
            <p class="text-[22px] text-gray-500 mb-4">
                Returning to start screen in
                <span class="font-semibold text-gray-800">
                    {{ countdown }}
                </span>
                seconds
            </p>

            <!-- Progress Bar -->
            <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                <div
                    class="h-full bg-emerald-500 transition-all duration-1000"
                    :style="{
                        width: (countdown / totalTime) * 100 + '%',
                    }"
                ></div>
            </div>
        </div>
    </div>
</template>
