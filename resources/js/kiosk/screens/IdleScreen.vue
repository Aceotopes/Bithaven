<script setup>
import { ref, onMounted, watch } from "vue";
import idleVideo from "@/kiosk/assets/idle/BithavenIdleLoop.mp4";
import StatusPopup from "../components/kiosk/StatusPopup.vue";

const emit = defineEmits(["next"]); // emitted when user taps to proceed
const loaded = ref(false); // fade in effect on mount
const showPopup = ref(false); // popup visibility
const popupType = ref("success"); // success or error

onMounted(() => {
    requestAnimationFrame(() => {
        loaded.value = true;
    });
});

// popup Handlers
function handleIdleTap() {
    // TEMP simulation
    popupType.value = Math.random() > 0.5 ? "success" : "error";
    showPopup.value = true;

    if (popupType.value === "success") {
        setTimeout(() => {
            showPopup.value = false;
            emit("next");
        }, 2400); // idlescreen delay
    }
}
function handlePopupClose() {
    showPopup.value = false;
}
</script>

<template>
    <div
        class="relative w-full h-full bg-black transition-opacity duration-700"
        :class="loaded ? 'opacity-100' : 'opacity-0'"
        @click="!showPopup && handleIdleTap()"
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

        <!-- Status Popup -->
        <StatusPopup
            :show="showPopup"
            :type="popupType"
            :title="
                popupType === 'success'
                    ? 'ACCESS CONFIRMED'
                    : 'ACCESS NOT AVAILABLE'
            "
            :message="
                popupType === 'success'
                    ? 'Your information has been verified. You will be redirected to the system shortly.'
                    : 'Your information is not currently enrolled in the system. Please contact the administrator for further assistance.'
            "
            @close="handlePopupClose"
        />
    </div>
</template>
