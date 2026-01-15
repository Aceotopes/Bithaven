<script setup>
import { ref, onMounted } from "vue";

const time = ref("");

function updateTime() {
    const now = new Date();
    time.value = now.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
    });
}

onMounted(() => {
    updateTime();
    setInterval(updateTime, 60000);
});
</script>

<template>
    <header
        class="relative z-20 w-full h-24 flex items-center bg-white/80 backdrop-blur-xl border-b border-black/10 px-8"
    >
        <!-- Logo -->
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-white font-bold"
            >
                B
            </div>
            <span class="sr-only">Bithaven</span>
        </div>

        <!-- Center Title -->
        <div class="absolute left-1/2 -translate-x-1/2 text-center">
            <h1
                class="text-[26px] font-semibold tracking-[0.12em] text-gray-900"
            >
                BITHAVEN SMART LOCKER
            </h1>
            <p
                class="mt-1 text-[12px] tracking-[0.25em] text-gray-500 uppercase"
            >
                A SAFE HAVEN FOR BELONGINGS, TIED TO COMPUTER BITS.
            </p>
        </div>

        <!-- Time -->
        <div class="ml-auto text-right">
            <p class="text-[22px] font-medium text-gray-800">
                {{ time }}
            </p>
            <p class="text-xs tracking-wide text-gray-500 uppercase">
                System Time
            </p>
        </div>
    </header>
</template>
