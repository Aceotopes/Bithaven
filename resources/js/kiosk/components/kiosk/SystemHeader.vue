<script setup>
import { ref, onMounted } from "vue";
import BithavenLogo from "@/kiosk/assets/idle/BithavenLogo.png";
const time = ref("");
const emit = defineEmits(["end-session"]);

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
        class="relative z-10 w-full h-24 flex items-center bg-white/80 backdrop-blur-xl border-b border-black/10 px-8"
    >
        <!-- Logo -->
        <div class="flex items-center gap-3">
            <div class="w-30 h-30">
                <img
                    src="@/kiosk/assets/idle/BithavenLogo.png"
                    alt="BithavenLogo"
                />
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

        <!-- Time + End Session-->
        <div class="ml-auto flex items-center gap-6">
            <!-- Time -->
            <div class="text-right">
                <p class="text-[22px] font-medium text-gray-800">
                    {{ time }}
                </p>
                <p class="text-xs tracking-wide text-gray-500 uppercase">
                    System Time
                </p>
            </div>

            <!-- Divider -->
            <div class="w-px h-10 bg-black/10"></div>

            <!-- End Session -->
            <button
                class="px-5 py-2.5 rounded-xl border border-black/10 bg-white/70 backdrop-blur text-gray-600 text-[15px] font-semibold transition hover:bg-white hover:text-gray-800 active:scale-95 active:bg-gray-100 active:shadow-inner focus:outline-none"
                @click.stop="emit('end-session')"
            >
                End Session
            </button>
        </div>
    </header>
</template>
