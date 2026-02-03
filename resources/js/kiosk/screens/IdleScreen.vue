<script setup>
import { ref, onMounted, onBeforeMount, watch } from "vue";
import idleVideo from "@/kiosk/assets/idle/BithavenIdleLoop.mp4";
import StatusPopup from "@/kiosk/components/kiosk/StatusPopup.vue";
import { useRFIDService } from "@/kiosk/services/rfid.service";

const emit = defineEmits(["start-scan", "success-complete"]);

const props = defineProps({
    scanResult: {
        type: Object,
        default: null,
    },
});

const rfid = useRFIDService();

const loaded = ref(false);
const showPopup = ref(false);
const popupType = ref(null); // "success" | "error"
const countdown = ref(5);
const scannedUid = ref(null);

let countdownTimer = null;

/* -----------------------------
   Popup helpers
------------------------------ */
function handlePopupClose() {
    clearInterval(countdownTimer);
    showPopup.value = false;
}

function startCountdown() {
    countdown.value = 5;

    countdownTimer = setInterval(() => {
        countdown.value--;
        if (countdown.value === 0) {
            clearInterval(countdownTimer);
            emit("success-complete");
        }
    }, 1000);
}

/* -----------------------------
   React to backend result
------------------------------ */
watch(
    () => props.scanResult,
    (result) => {
        if (!result) return;

        clearInterval(countdownTimer);

        if (result.status === "success") {
            popupType.value = "success";
            scannedUid.value = result.uid;
            showPopup.value = true;
            startCountdown();
        } else {
            popupType.value = "error";
            showPopup.value = true;
        }
    }
);

/* -----------------------------
   Lifecycle
------------------------------ */
onMounted(() => {
    requestAnimationFrame(() => {
        loaded.value = true;
    });

    rfid.enable((uid) => {
        // IdleScreen ONLY detects RFID
        emit("start-scan", uid);
    });
});

onBeforeMount(() => {
    clearInterval(countdownTimer);
    rfid.disable();
});
</script>

<template>
    <div
        class="relative w-full h-full bg-black transition-opacity duration-700"
        :class="loaded ? 'opacity-100' : 'opacity-0'"
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

        <!-- Overlay -->
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
            @action="handlePopupClose"
            @close="handlePopupClose"
        />
    </div>
</template>
