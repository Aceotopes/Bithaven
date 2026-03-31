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

    daemonStatus: {
        type: String,
        default: "ONLINE",
    },
});

const rfid = useRFIDService();

const loaded = ref(false);
const showPopup = ref(false);
const popupType = ref(null); // "success" | "error"
const countdown = ref(5);
const scannedUid = ref(null);

const popupTitle = ref("");
const popupMessage = ref("");
const popupCountdown = ref(null);
const showPopupAction = ref(false);
const popupActionLabel = ref("Continue");

let countdownTimer = null;

// MANUAL UID ENTRY (for testing/demo purposes)
const manualUid = ref("");
// function submitManualUid() {
//     if (!manualUid.value.trim()) return;

//     const uid = manualUid.value.trim();

//     console.log("Manual UID entered:", uid);

//     emit("start-scan", uid);

//     manualUid.value = "";
// }

function submitManualUid() {
    if (props.daemonStatus === "OFFLINE") return;

    if (!manualUid.value.trim()) return;

    const uid = manualUid.value.trim();
    console.log("Manual UID entered:", uid);
    emit("start-scan", uid);
    manualUid.value = "";
}

function triggerPopup({
    type,
    title,
    message = "",
    countdown = null,
    showAction = false,
    actionLabel = "Continue",
}) {
    popupType.value = type;
    popupTitle.value = title;
    popupMessage.value = message;
    popupCountdown.value = countdown;
    showPopupAction.value = showAction;
    popupActionLabel.value = actionLabel;
    showPopup.value = true;
}

/* -----------------------------
   Popup helpers
------------------------------ */
function handlePopupClose() {
    completeScan();
}

function completeScan() {
    clearInterval(countdownTimer);
    showPopup.value = false;
    emit("success-complete");
}

// function startCountdown() {
//     countdown.value = 5;

//     countdownTimer = setInterval(() => {
//         countdown.value--;
//         if (countdown.value === 0) {
//             clearInterval(countdownTimer);
//             emit("success-complete");
//         }
//     }, 1000);
// }

function startCountdown() {
    popupCountdown.value = 5;

    countdownTimer = setInterval(() => {
        popupCountdown.value--;

        if (popupCountdown.value === 0) {
            completeScan();
        }
    }, 1000);
}

/* -----------------------------
   React to backend result
------------------------------ */
// watch(
//     () => props.scanResult,
//     (result) => {
//         if (!result) return;

//         clearInterval(countdownTimer);

//         if (result.status === "success") {
//             popupType.value = "success";
//             scannedUid.value = result.uid;
//             showPopup.value = true;
//             startCountdown();
//         } else {
//             popupType.value = "error";
//             showPopup.value = true;
//         }
//     }
// );
watch(
    () => props.scanResult,
    (result) => {
        if (!result) return;

        clearInterval(countdownTimer);

        switch (result.status) {
            case "success":
                triggerPopup({
                    type: "success",
                    title: "ACCESS CONFIRMED",
                    countdown: 5,
                    showAction: true,
                });

                startCountdown();
                break;

            case "error":
                triggerPopup({
                    type: "error",
                    title: "ACCESS NOT AVAILABLE",
                });
                break;

            // Admin
            case "admin":
                triggerPopup({
                    type: "admin",
                    title: "ADMIN CARD DETECTED",
                    message: "Accessing administrative controls...",
                    countdown: 5,
                    showAction: true,
                });
                startCountdown();
                break;

            // Suspended
            case "suspended":
                triggerPopup({
                    type: "warning",
                    title: "ACCESS SUSPENDED",
                    message:
                        "Your ID has been suspended. Please contact administration.",
                });
                break;
        }
    }
);

watch(
    () => props.daemonStatus,
    (status) => {
        if (status === "OFFLINE") {
            triggerPopup({
                type: "daemon_offline",
                title: "SYSTEM OFFLINE",
                message: "Kiosk is currently unavailable.",
                showAction: false,
            });
        } else {
            showPopup.value = false;
        }
    },
    { immediate: true }
);

/* -----------------------------
   Lifecycle
------------------------------ */
onMounted(() => {
    requestAnimationFrame(() => {
        loaded.value = true;
    });

    rfid.enable((uid) => {
        if (props.daemonStatus === "OFFLINE") {
            console.log("Scan blocked: daemon offline");
            return;
        }

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
            <!-- <p class="text-white text-3xl font-semibold tracking-wide">
                Tap your ID to begin
            </p> -->
            <div class="flex gap-2">
                <input
                    v-model="manualUid"
                    type="text"
                    placeholder="Enter UID (testing)"
                    class="px-4 py-2 rounded bg-white text-black text-6xl"
                    @keyup.enter="submitManualUid"
                />

                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded"
                    @click="submitManualUid"
                >
                    Scan
                </button>
            </div>
        </div>

        <!-- Status Popup -->
        <!-- <StatusPopup
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
        /> -->
        <StatusPopup
            :show="showPopup"
            :type="popupType"
            :title="popupTitle"
            :message="popupMessage"
            :countdown="popupCountdown"
            :showAction="showPopupAction"
            :actionLabel="popupActionLabel"
            @action="handlePopupClose"
            @close="handlePopupClose"
        />
    </div>
</template>
