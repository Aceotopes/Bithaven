<script setup>
import { KIOSK_STATES } from "./constants/kioskStates";
import { useKioskFlow } from "./composables/useKioskFlow";
import { useKioskSession } from "./composables/useKioskSessions";
import { useLockerRental } from "./composables/useLockerRental";
import { usePenalty } from "./composables/usePenalty";
import { useKioskActions } from "./composables/useKioskActions";
import { ref, reactive } from "vue";

import IdleScreen from "./screens/IdleScreen.vue";
import MainScreen from "./screens/MainScreen.vue";
import LockerSelectScreen from "./screens/LockerSelectScreen.vue";
import PaymentScreen from "./screens/PaymentScreen.vue";
// import LockerSelectScreen from './screens/LockerSelectScreen.vue' //                             temporarily commented out for testing

//const kioskState = reactive({
//student: null,                  // logged-in student
// rentalState: "NO_RENTAL",       // NO_RENTAL | ACTIVE_RENTAL | EXPIRED_RENTAL
//locker: null,                   // active locker info
// penalty: null,                  // penalty info if expired
//});

const session = useKioskSession(); // kiosk session state manager

const flow = useKioskFlow(session.state); // kiosk flow state manager
const penalty = usePenalty(session.state); // penalty manager
const actions = useKioskActions(session.state); // kiosk action handlers
const isEndingRental = ref(false);
const endCountdown = ref(3);
let endTimer = null;

const rental = useLockerRental(session.state, {
    onExpire: () => {
        penalty.applyPenalty();
    },
});
// const pendingRental = ref({
//     locker: null,
//     duration: null,
// });

const paymentContext = ref({
    mode: null, // 'RENTAL' | 'PENALTY'
    amount: null, // number
    locker: null, // locker number
    duration: null, // for RENTAL only
    penalty: null, // for PENALTY only
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
    console.log("Locker selection confirmed:", payload);
    const { locker, duration } = payload;

    paymentContext.value = {
        mode: "RENTAL",
        amount: duration * 5, // pricing logic (temporary)
        locker,
        duration,
        penalty: null,
    };

    flow.goToPayment();
}
// =======================================================================================

function handleSettlePenalty() {
    const snapshot = penalty.getPenaltySnapshot();
    if (!snapshot) return;

    paymentContext.value = {
        mode: "PENALTY",
        locker: session.state.locker.number,
        duration: null,
        amount: snapshot.amount,
        penalty: {
            exceededDuration: snapshot.exceededDuration,
            breakdown: snapshot.breakdown,
        },
    };

    flow.goToPayment();
}

function handlEndSession() {
    session.clearSession();
    console.log("Session ended.");
    flow.goToIdle();
}

function handleRentLocker() {
    flow.goToLockerSelect();
}

function resetPaymentContext() {
    paymentContext.value = {
        mode: null,
        amount: null,
        locker: null,
        duration: null,
        penalty: null,
    };
}

function handlePaymentCancel() {
    // Optional: clear pending rental intent
    // pendingRental.value = {
    //     locker: null,
    //     duration: null,
    // };

    // flow.goToLockerSelect();

    const mode = paymentContext.value.mode;

    resetPaymentContext();

    if (mode === "RENTAL") {
        flow.goToLockerSelect();
    } else {
        flow.goToStudentDashboard();
    }

    paymentContext.value = { type: null, amount: 0 };
}

function handlePaymentComplete() {
    if (paymentContext.value.mode === "RENTAL") {
        rental.rentLocker(
            paymentContext.value.locker,
            paymentContext.value.duration
        );
    }

    if (paymentContext.value.mode === "PENALTY") {
        penalty.settlePenalty();
    }

    // cleanup
    resetPaymentContext();
    flow.goToIdle();
}

function handleEndRental() {
    rental.endRental();

    // Show success overlay
    isEndingRental.value = true;
    endCountdown.value = 3;

    endTimer = setInterval(() => {
        endCountdown.value--;

        if (endCountdown.value === 0) {
            clearInterval(endTimer);
            isEndingRental.value = false;
            flow.goToIdle();
        }
    }, 1000);
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
    <div class="fixed top-2 left-2 bg-black text-white px-3 py-1 z-50">
        {{ session.state.kioskState }}
    </div>
    <transition name="fade" mode="out-in">
        <IdleScreen
            v-if="session.state.kioskState === KIOSK_STATES.IDLE"
            @start-scan="handleStartScan"
        />

        <MainScreen
            v-else-if="
                session.state.kioskState === KIOSK_STATES.STUDENT_DASHBOARD
            "
            :student="session.state.student"
            :rentalState="session.state.rentalState"
            :locker="session.state.locker"
            :penalty="session.state.penalty"
            :showHowTo="!session.state.hasSeenHowTo"
            :penaltyAmount="penalty.penaltyAmount.value"
            :canRent="actions.canRent.value"
            :canEndRental="actions.canEndRental.value"
            :canSettlePenalty="actions.canSettlePenalty.value"
            :canEndSession="actions.canEndSession.value"
            :isEndingRental="isEndingRental"
            :endCountdown="endCountdown"
            @end-session="handlEndSession"
            @rent-locker="handleRentLocker"
            @end-rental="handleEndRental"
            @settle-penalty="handleSettlePenalty"
            @dismiss-howto="session.state.hasSeenHowTo = true"
        />

        <LockerSelectScreen
            v-else-if="session.state.kioskState === KIOSK_STATES.LOCKER_SELECT"
            @back="handleLockerSelectBack"
            @confirm="handleLockerSelectConfirm"
        />

        <PaymentScreen
            v-else-if="session.state.kioskState === KIOSK_STATES.PAYMENT"
            :locker="paymentContext.locker"
            :duration="paymentContext.duration"
            :mode="paymentContext.mode"
            :amount="paymentContext.amount"
            :penalty="paymentContext.penalty"
            :lockerEndTime="session.state.locker?.endTime"
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
