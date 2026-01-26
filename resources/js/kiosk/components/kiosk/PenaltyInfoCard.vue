<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";

const exceededTime = ref("00:00:00");
let timer = null;

const props = defineProps({
    lockerNumber: {
        type: Number,
        required: true,
    },
    exceededDuration: {
        type: String, // e.g. "1 hour 30 minutes"
        required: true,
    },
    penaltyBreakdown: {
        type: Array,
        required: true,
        /*
          Example:
          [
            { label: "Initial exceed", amount: 5 },
            { label: "30 minutes", amount: 5 },
            { label: "1 hour", amount: 10 },
          ]
        */
    },
    totalAmount: {
        type: Number,
        required: true,
    },
    endTime: {
        type: Number,
        required: true,
    },
});

function formatDuration(ms) {
    const totalSeconds = Math.max(0, Math.floor(ms / 1000));
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;

    return [h, m, s].map((v) => String(v).padStart(2, "0")).join(":");
}

onMounted(() => {
    timer = setInterval(() => {
        const exceededMs = Date.now() - props.endTime;
        exceededTime.value = formatDuration(exceededMs);
    }, 1000);
});

onBeforeUnmount(() => {
    clearInterval(timer);
});
</script>

<template>
    <section
        class="mb-10 relative max-w-[920px] mx-auto bg-amber-50/90 backdrop-blur-xl border border-amber-300/40 rounded-[24px] px-14 py-10 shadow-[0_24px_60px_rgba(0,0,0,0.12)]"
    >
        <!-- Header -->
        <div class="flex justify-between items-center">
            <p
                class="text-[20px] tracking-[0.35em] uppercase text-amber-700 font-semibold"
            >
                Penalty Notice
            </p>

            <p class="text-[20px] text-gray-700">
                Locker
                <span class="font-mono font-semibold text-gray-900">
                    #{{ lockerNumber }}
                </span>
            </p>
        </div>

        <div class="my-6 h-px bg-amber-300/40"></div>

        <!-- Explanation -->
        <p class="text-[22px] text-gray-700 leading-relaxed">
            Your locker rental exceeded the allowed time by
            <span class="font-semibold text-gray-900">
                {{ exceededDuration }} </span
            >. A penalty has been applied according to the system rules below.
        </p>
        <p class="mt-4 text-[20px] text-gray-600 leading-relaxed">
            Penalty charges will continue to accumulate while the locker remains
            locked. Please complete payment promptly to avoid additional charges
            and regain access to your locker.
        </p>
        <!-- <div class="mt-6 text-center">
            <p class="text-sm tracking-widest uppercase text-gray-500">
                Exceeded Time
            </p>

            <p class="mt-2 font-mono text-[40px] text-amber-700 tracking-wide">
                {{ exceededTime }}
            </p>

            <p class="mt-2 text-[16px] text-gray-500">
                Penalty increases as time passes
            </p>
        </div> -->

        <!-- Breakdown -->
        <div class="mt-8 space-y-4">
            <div
                v-for="(item, index) in penaltyBreakdown"
                :key="index"
                class="flex justify-between text-[20px]"
            >
                <span class="text-gray-600">
                    {{ item.label }}
                </span>

                <span class="font-mono font-semibold text-gray-900">
                    ₱{{ item.amount }}
                </span>
            </div>
        </div>

        <div class="my-6 h-px bg-amber-300/40"></div>

        <!-- Total -->
        <div class="flex justify-between items-center">
            <p
                class="text-[22px] tracking-wide uppercase text-amber-800 font-semibold"
            >
                Total Penalty
            </p>

            <p class="text-[28px] font-mono font-bold text-amber-800">
                ₱{{ totalAmount }}
            </p>
        </div>
    </section>
</template>
