<script setup>
import { onMounted, onUnmounted } from "vue";
import SystemHeader from "@/kiosk/components/kiosk/SystemHeader.vue";
import AdminLockerPanel from "../components/kiosk/AdminLockerPanel.vue";
import AdminLockerDetailsModal from "../components/kiosk/AdminLockerDetailsModal.vue";
import EndSessionConfirmModal from "@/kiosk/components/kiosk/EndSessionConfirmModal.vue";
import IdleWarningModal from "@/kiosk/components/kiosk/IdleWarningModal.vue";
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    lockers: Array,
    selectedLockerDetails: Object,
    showDetails: Boolean,
});

const emit = defineEmits([
    "exit-admin",
    "select-locker",
    "close-details",
    "force-unlock",
    "disable-locker",
    "enable-locker",
    "clear-penalty",
    "end-rental",
    "show-toast",
]);

const isEmergencyUnlocking = ref(false);

const showEndSessionConfirm = ref(false);

const showIdleWarning = ref(false);
const idleSecondsLeft = ref(10); // countdown duration

let idleTimer = null;
let countdownTimer = null;

function openEndSessionConfirm() {
    showEndSessionConfirm.value = true;
}

function cancelEndSession() {
    showEndSessionConfirm.value = false;
}

function confirmEndSession() {
    showEndSessionConfirm.value = false;
    emit("exit-admin");
}

function startIdleTimer() {
    clearTimeout(idleTimer);

    idleTimer = setTimeout(() => {
        showIdleWarning.value = true;
        startCountdown();
    }, 60000); // 60s inactivity (adjust as needed)
}

function startCountdown() {
    idleSecondsLeft.value = 10;

    countdownTimer = setInterval(() => {
        idleSecondsLeft.value--;

        if (idleSecondsLeft.value <= 0) {
            clearInterval(countdownTimer);
            confirmIdleExit();
        }
    }, 1000);
}

function resetIdleTimer() {
    if (showIdleWarning.value) return; // don't reset during warning

    startIdleTimer();
}

function confirmIdleExit() {
    showIdleWarning.value = false;
    emit("exit-admin");
}

function continueSession() {
    showIdleWarning.value = false;
    clearInterval(countdownTimer);
    startIdleTimer();
}

async function handleEmergencyUnlock(pin) {
    if (isEmergencyUnlocking.value) return;

    isEmergencyUnlocking.value = true;

    try {
        await axios.post("/api/kiosk/admin/verify-pin", { pin });

        emit("show-toast", "Processing emergency unlock...", "warning");

        const res = await axios.post("/api/kiosk/admin/emergency-unlock");

        const batchId = res.data.batch_id;

        let completed = false;

        while (!completed) {
            await new Promise((r) => setTimeout(r, 1000));

            const status = await axios.get(
                `/api/kiosk/admin/unlock-jobs/batch/${batchId}`
            );

            completed = status.data.completed;
        }

        emit("show-toast", "All lockers successfully unlocked.", "success");
    } catch (err) {
        if (err.response?.status === 403) {
            emit(
                "show-toast",
                err.response?.data?.message || "Incorrect PIN.",
                "error"
            );
        } else if (err.response?.status === 409) {
            emit(
                "show-toast",
                "Emergency unlock already in progress.",
                "warning"
            );
        } else if (err.response?.status === 503) {
            emit("show-toast", "System offline.", "error");
        } else {
            emit("show-toast", "Emergency unlock failed.", "error");
        }
    } finally {
        isEmergencyUnlocking.value = false;
    }
}

onMounted(() => {
    startIdleTimer();

    window.addEventListener("click", resetIdleTimer);
    window.addEventListener("touchstart", resetIdleTimer);
    window.addEventListener("keydown", resetIdleTimer);
});

onUnmounted(() => {
    clearTimeout(idleTimer);
    clearInterval(countdownTimer);

    window.removeEventListener("click", resetIdleTimer);
    window.removeEventListener("touchstart", resetIdleTimer);
    window.removeEventListener("keydown", resetIdleTimer);
});
</script>

<template>
    <div
        class="relative min-h-screen w-full overflow-hidden bg-gradient-to-b from-slate-50 via-slate-100 to-slate-200"
    >
        <!-- Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <!-- Base Light Surface -->
            <div
                class="absolute inset-0 bg-gradient-to-b from-white via-slate-50 to-slate-100"
            ></div>

            <!-- Top Cyan Atmosphere -->
            <div
                class="absolute -top-[5%] left-1/2 -translate-x-1/2 w-[1100px] h-[1100px] bg-cyan-300/15 rounded-full blur-[200px]"
            ></div>

            <!-- Mid Soft Light Column -->
            <div
                class="absolute top-[55%] left-1/2 -translate-x-1/2 w-[700px] h-[1200px] bg-white/40 rounded-full blur-[220px]"
            ></div>

            <!-- Bottom Cyan Accent -->
            <div
                class="absolute bottom-[-20%] left-1/2 -translate-x-1/2 w-[1000px] h-[1000px] bg-cyan-400/12 rounded-full blur-[220px]"
            ></div>

            <!-- Subtle Vertical Texture -->
            <div
                class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(0,0,0,0.03)_1px,transparent_1px)] bg-[size:100%_120px] opacity-25"
            ></div>
        </div>

        <SystemHeader @end-session="openEndSessionConfirm" />

        <main class="relative z-10 w-full px-16 pt-12">
            <AdminLockerPanel
                :lockers="lockers"
                :selectedLockerDetails="selectedLockerDetails"
                @select-locker="$emit('select-locker', $event)"
                @force-unlock="$emit('force-unlock')"
                @disable-locker="$emit('disable-locker')"
                @enable-locker="$emit('enable-locker')"
                @clear-penalty="$emit('clear-penalty')"
                @end-rental="$emit('end-rental')"
                @emergency-unlock="handleEmergencyUnlock"
                :isEmergencyUnlocking="isEmergencyUnlocking"
            />
        </main>

        <EndSessionConfirmModal
            :show="showEndSessionConfirm"
            @cancel="cancelEndSession"
            @confirm="confirmEndSession"
        />

        <IdleWarningModal
            :show="showIdleWarning"
            :secondsLeft="idleSecondsLeft"
            @confirm="confirmIdleExit"
            @continue="continueSession"
        />
        <!-- <AdminLockerDetailsModal
            :show="showDetails"
            :details="selectedLockerDetails"
            @close="$emit('close-details')"
            @force-unlock="$emit('force-unlock')"
            @disable-locker="$emit('disable-locker')"
            @enable-locker="$emit('enable-locker')"
            @clear-penalty="$emit('clear-penalty')"
            @end-rental="$emit('end-rental')"
        /> -->
    </div>
</template>
