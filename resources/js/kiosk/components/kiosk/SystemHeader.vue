<script setup>
import { ref, onMounted } from "vue";
import BithavenLogo from "@/kiosk/assets/idle/BithavenLogo.svg";
import BithavenLogo1 from "@/kiosk/assets/idle/BithavenLogo1.svg";
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
        class="relative z-10 w-full h-30 flex items-center bg-white/80 backdrop-blur-xl border-b border-black/10 px-8"
    >
        <!-- Logo -->
        <div class="flex items-center gap-3">
            <div class="w-23 h-26">
                <img :src="BithavenLogo1" alt="BithavenLogo" />
            </div>
        </div>

        <!-- Center Title -->
        <div
            class="absolute left-1/2 -translate-x-1/2 flex items-center justify-center"
        >
            <img
                :src="BithavenLogo"
                alt="Bithaven"
                class="h-120 w-120 object-contain"
            />
        </div>

        <!-- Time + End Session-->
        <div class="ml-auto flex items-center gap-6">
            <!-- Time -->
            <!-- <div class="text-right">
                <p class="text-[22px] font-medium text-gray-800">
                    {{ time }}
                </p>
                <p class="text-xs tracking-wide text-gray-500 uppercase">
                    System Time
                </p>
            </div> -->

            <!-- Divider -->
            <div class="w-px h-10 bg-black/10"></div>

            <!-- End Session -->
            <button
                class="px-5 py-3 rounded-xl border border-red-200 bg-red-50 backdrop-blur text-red-400 text-[23px] font-semibold transition hover:bg-red-400 hover:text-white active:scale-95 active:bg-gray-100 active:shadow-inner focus:outline-none"
                @click.stop="emit('end-session')"
            >
                Logout
            </button>
        </div>
    </header>
</template>
