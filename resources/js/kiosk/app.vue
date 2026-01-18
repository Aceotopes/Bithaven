<script setup>
import { KIOSK_STATES } from "./constants/kioskStates";
import { useKioskFlow } from "./composables/useKioskFlow";
import { useKioskSession } from "./composables/useKioskSessions";

import IdleScreen from "./screens/IdleScreen.vue";
import MainScreen from "./screens/MainScreen.vue";
// import LockerSelectScreen from './screens/LockerSelectScreen.vue' //                             temporarily commented out for testing

const flow = useKioskFlow(); // kiosk flow state manager
const session = useKioskSession(); // kiosk session state manager

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

function handlEndSession() {
    session.clearSession();
    console.log("Session ended.");
    flow.goToIdle();
}

function handleRentLocker() {
    flow.goToLockerSelect();
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

        <!-- ======================= next to do  (priority)======================= -->
        <!-- <SelectLockerScreen v-if="currentState === KIOSK_STATES.SELECT_LOCKER" />                  temporarily commented out for testing -->

        <MainScreen
            v-else-if="flow.kioskState.value === KIOSK_STATES.STUDENT_DASHBOARD"
            :student="session.state.student"
            @end-session="handlEndSession"
            @rent-locker="handleRentLocker"
        />
        <!-- ===================================================================== -->
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
