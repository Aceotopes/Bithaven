<script setup>
import { ref, onMounted } from "vue";
import idleVideo from "@/kiosk/assets/idle/BithavenIdleLoop.mp4";

const emit = defineEmits(["next"]);
const loaded = ref(false);

onMounted(() => {
    requestAnimationFrame(() => {
        loaded.value = true;
    });
});
</script>

<template>
    <div
        class="relative w-full h-full bg-black transition-opacity duration-700"
        :class="loaded ? 'opacity-100' : 'opacity-0'"
        @click="emit('next')"
    >
        <!-- Background Video -->
        <video
            autoplay
            muted
            loop
            playsinline
            class="w-full h-full object-contain"
        >
            <source :src="idleVideo" type="video/mp4" />
        </video>

        <!-- Overlay sample-->
        <div
            class="relative z-10 w-full h-full flex flex-col items-center justify-end pb-24 bg-black/30"
        >
            <p class="text-white text-3xl font-semibold tracking-wide">
                Tap your ID to begin
            </p>
        </div>
    </div>
</template>
