<script setup>
import { ref, onMounted, onBeforeMount } from "vue";
import idleVideo from "@/kiosk/assets/idle/BithavenIdleLoop.mp4";
import StatusPopup from "../components/kiosk/StatusPopup.vue";

const emit = defineEmits(["next"]); // emitted when user taps to proceed
const loaded = ref(false); // fade in effect on mount
const showPopup = ref(false); // popup visibility
const popupType = ref("success"); // success or error
const countdown = ref(5); // countdown timer for popup auto-close

let countdownTimer = null;

onMounted(() => {
    requestAnimationFrame(() => {
        loaded.value = true;
    });
});

// popup Handlers
function handleIdleTap() {
    //idle screen tap handler function
    // TEMP simulation
    popupType.value = Math.random() > 0.5 ? "success" : "error";
    showPopup.value = true;

    if (popupType.value === "success") {
        startCountdown();
    }
}

function handlePopupClose() {
    //popup close handler function
    clearInterval(countdownTimer);
    showPopup.value = false;
}

function startCountdown() {
    //countdown function
    countdown.value = 5;

    countdownTimer = setInterval(() => {
        countdown.value--;

        if (countdown.value === 0) {
            proceed();
        }
    }, 1000);
}

function proceed() {
    clearInterval(countdownTimer);
    showPopup.value = false;
    emit("next");
}

onBeforeMount(() => {
    clearInterval(countdownTimer);
});
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
            :countdown="popupType === 'success' ? countdown : null"
            :showAction="popupType === 'success'"
            actionLabel="Continue"
            @action="proceed"
            @close="handlePopupClose"
        />
    </div>
</template>
