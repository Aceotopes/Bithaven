<script setup>
import { KIOSK_STATES } from "./constants/kioskStates";
import { useKioskFlow } from "./composables/useKioskFlow";
import { useKioskSession } from "./composables/useKioskSessions";
import { useLockerRental } from "./composables/useLockerRental";
import { usePenalty } from "./composables/usePenalty";
import { useKioskActions } from "./composables/useKioskActions";
import { reactive } from "vue";

import IdleScreen from "./screens/IdleScreen.vue";
import MainScreen from "./screens/MainScreen.vue";
import LockerSelectScreen from "./screens/LockerSelectScreen.vue";
import PaymentScreen from "./screens/PaymentScreen.vue";
// import LockerSelectScreen from './screens/LockerSelectScreen.vue' //                             temporarily commented out for testing

import { ref } from "vue";

const kioskState = reactive({
    student: null, // logged-in student
    rentalState: "NO_RENTAL", // NO_RENTAL | ACTIVE_RENTAL | EXPIRED_RENTAL
    locker: null, // active locker info
    penalty: null, // penalty info if expired
});

const flow = useKioskFlow(); // kiosk flow state manager
const session = useKioskSession(kioskState); // kiosk session state manager
const penalty = usePenalty(kioskState); // penalty manager
const actions = useKioskActions(kioskState); // kiosk action handlers

const rental = useLockerRental(kioskState, () => {
    penalty.applyPenalty();
}); // locker rental manager

const pendingRental = ref({
    locker: null,
    duration: null,
});

// ===================== mock student data for testing =====================
const mockStudent = {
    studentNumber: "22-150570",
    fullName: "Ace Argee F. Vizcarra",
    yearLevel: "IV",
    department: "Computer Engineering",
    photo: null, // placeholder
};
// ========================================================================

// ===================== session start handler =====================
function handleStartScan() {
    session.startSession(mockStudent);
    console.log("Session started with student:", session.state.student);
    // handler for starting scan from idle screen
    flow.goToStudentDashboard();
}
// ================================================================

// ===================== locker selection (back and confirm)handlers =====================
function handleLockerSelectBack() {
    flow.goToStudentDashboard();
}

function handleLockerSelectConfirm(payload) {
    // TEMP: validate flow only
    pendingRental.value = payload;
    console.log("Locker selection confirmed:", payload);
    flow.goToPayment();
}
// =======================================================================================
function handlEndSession() {
    session.clearSession();
    console.log("Session ended.");
    flow.goToIdle();
}

function handleRentLocker() {
    flow.goToLockerSelect();
}

function handlePaymentCancel() {
    // Optional: clear pending rental intent
    pendingRental.value = {
        locker: null,
        duration: null,
    };

    flow.goToLockerSelect();
}

function handlePaymentComplete() {
    const { locker, duration } = pendingRental.value;

    if (!locker || !duration) return;

    // Register rental officially
    rental.rentLocker(locker, duration);

    // Clear UI intent
    pendingRental.value = {
        locker: null,
        duration: null,
    };

    // Return kiosk to idle
    flow.goToIdle();
}

// ===================== debugging info for checkpoint 10 =====================
// console.log("kioskState:", flow.kioskState, typeof flow.kioskState);
// console.log("IDLE:", KIOSK_STATES.IDLE, typeof KIOSK_STATES.IDLE);
// console.log("strict equal:", flow.kioskState.value === KIOSK_STATES.IDLE);
// ============================================================================
</script>

<template>
    <!-- ============ debugging info for checkpoint 10 ============ -->
    <!-- <div
            class="fixed top-2 left-2 z-50 bg-red-600 text-white px-4 py-2 text-lg"
        >
            kioskState: {{ flow.kioskState }}
        </div>
        <div class="fixed top-32 left-2 z-50 bg-green-600 text-white px-4 py-2">
            raw kioskState: {{ flow.kioskState }}
            <br />
            idle enum: {{ KIOSK_STATES.IDLE }}
            <br />
            equal: {{ flow.kioskState.value === KIOSK_STATES.IDLE }}
        </div> -->
    <!-- ========================================================== -->

    <transition name="fade" mode="out-in">
        <IdleScreen
            v-if="flow.kioskState.value === KIOSK_STATES.IDLE"
            @start-scan="handleStartScan"
        />

        <MainScreen
            v-else-if="flow.kioskState.value === KIOSK_STATES.STUDENT_DASHBOARD"
            :student="kioskState.student"
            :rentalState="kioskState.rentalState"
            :locker="kioskState.locker"
            :penalty="kioskState.penalty"
            :canRent="actions.canRent.value"
            :canEndRental="actions.canEndRental.value"
            :canSettlePenalty="actions.canSettlePenalty.value"
            :canEndSession="actions.canEndSession.value"
            @end-session="handlEndSession"
            @rent-locker="handleRentLocker"
        />

        <LockerSelectScreen
            v-else-if="flow.kioskState.value === KIOSK_STATES.LOCKER_SELECT"
            @back="handleLockerSelectBack"
            @confirm="handleLockerSelectConfirm"
        />

        <PaymentScreen
            v-else-if="flow.kioskState.value === KIOSK_STATES.PAYMENT"
            :locker="pendingRental.locker"
            :duration="pendingRental.duration"
            @cancel="handlePaymentCancel"
            @complete="handlePaymentComplete"
        />
    </transition>
</template>

<style>
/* transition animation from 1 screen to another */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
