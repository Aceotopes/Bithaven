<script setup>
import { KIOSK_STATES } from "./constants/kioskStates";
import { useKioskFlow } from "./composables/useKioskFlow";
import { useKioskSession } from "./composables/useKioskSessions";
import { useLockerRental } from "./composables/useLockerRental";
import { usePenalty } from "./composables/usePenalty";
import { useKioskActions } from "./composables/useKioskActions";
import { useIdleTimeout } from "./composables/useIdleTimeout";

import { ref, onMounted } from "vue";

import IdleWarningModal from "@/kiosk/components/kiosk/IdleWarningModal.vue";
import IdleScreen from "./screens/IdleScreen.vue";
import MainScreen from "./screens/MainScreen.vue";
import LockerSelectScreen from "./screens/LockerSelectScreen.vue";
import PaymentScreen from "./screens/PaymentScreen.vue";

//const kioskState = reactive({
//student: null,                  // logged-in student
// rentalState: "NO_RENTAL",       // NO_RENTAL | ACTIVE_RENTAL | EXPIRED_RENTAL
//locker: null,                   // active locker info
// penalty: null,                  // penalty info if expired
//});

/* ===================================
        KIOSK STATE MANAGERS
   ===================================*/
const session = useKioskSession(); // kiosk session state manager

const flow = useKioskFlow(session.state); // kiosk flow state manager
const penalty = usePenalty(session.state); // penalty manager
const actions = useKioskActions(session.state); // kiosk action handlers
const isEndingRental = ref(false); // rental ending state
const endCountdown = ref(3); // rental end countdown
const TOTAL_LOCKERS = 15; //mock total lockers
const lockers = ref([]);

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

// ===================== locker statuses (mock data) =====================
// const lockers = computed(() =>
//     Array.from({ length: TOTAL_LOCKERS }, (_, i) => {
//         const number = i + 1;

//         return {
//             number,
//             status:
//                 session.state.locker?.number === number &&
//                 session.state.rentalState !== "NO_RENTAL"
//                     ? "OCCUPIED"
//                     : "AVAILABLE",
//         };
//     })
// );

const idle = useIdleTimeout({
    session,
    flow,
    timeoutMs: 60_000,
    warningMs: 10_000,
});

// =========== session start handler  (no Backend)========
// function handleStartScan() {
//     session.startSession(mockStudent);
//     console.log("Session started with student:", session.state.student);
//     // handler for starting scan from idle screen
//     flow.goToStudentDashboard();
// }
// =======================================================

//===================================
//session start handler (with Backend)
//===================================
async function handleStartScan() {
    try {
        const res = await fetch("/api/kiosk/scan", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                rfid_uid: "0851967331",
            }),
        });

        if (!res.ok) {
            throw new Error("Scan Failed");
        }

        const data = await res.json();

        //start session
        session.startSession(data.student);

        //check for active rental
        await recoverActiveRental();

        flow.goToStudentDashboard();
        console.log("Session started with student:", session.state.student);
    } catch (err) {
        console.error(err);
        return;
    }
}

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
    fetchLockerStatuses();
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

// =====================================
// payment complete handler (no backend)
// =====================================
// function handlePaymentComplete() {
//     if (paymentContext.value.mode === "RENTAL") {
//         rental.rentLocker(
//             paymentContext.value.locker,
//             paymentContext.value.duration
//         );
//     }

//     if (paymentContext.value.mode === "PENALTY") {
//         penalty.settlePenalty();
//     }

//     // cleanup
//     resetPaymentContext();
//     flow.goToIdle();
// }

// ===================== payment complete handler (with backend) =====================

async function handlePaymentComplete() {
    // ===================== RENTAL PAYMENT =====================
    if (paymentContext.value.mode === "RENTAL") {
        try {
            const response = await fetch("/api/kiosk/rentals/start", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    student_id: session.state.student.id,
                    locker_number: paymentContext.value.locker,
                    duration_hours: paymentContext.value.duration,
                }),
            });

            if (!response.ok) {
                const error = await response.json();
                console.error("Rental creation failed:", error);
                // TODO: show error UI later
                return;
            }

            const data = await response.json();

            console.log("Rental created:", data.rental);

            // update frontend rental state from backend response
            rental.hydrateRental({
                id: data.rental.id,
                lockerNumber: data.rental.locker_number,
                startTime: Date.parse(data.rental.start_time),
                endTime: Date.parse(data.rental.end_time),
            });
            await fetchLockerStatuses();
        } catch (err) {
            console.error("Network error:", err);
            return;
        }
    }

    // PENALTY PAYMENT
    if (paymentContext.value.mode === "PENALTY") {
        // Phase 3 will replace this with API
        penalty.settlePenalty();
    }

    // CLEANUP
    resetPaymentContext();
    flow.goToIdle();
}

async function recoverActiveRental() {
    if (!session.state.student) return;

    const res = await fetch(
        `/api/kiosk/rentals/active?student_id=${session.state.student.id}`
    );

    if (!res.ok) return;

    const { rental: activeRental } = await res.json();

    if (!activeRental) return;

    //rehydrate sesssion state
    rental.hydrateRental({
        id: activeRental.id,
        lockerNumber: activeRental.locker_number,
        startTime: Date.parse(activeRental.start_time),
        endTime: Date.parse(activeRental.end_time),
    });
    console.log("Backend start_time:", activeRental.start_time);
    console.log("Backend end_time: ", activeRental.end_time);

    console.log(
        "Parsed startTime: ",
        new Date(Date.parse(activeRental.start_time))
    );
    console.log(
        "Parsed endTime: ",
        new Date(Date.parse(activeRental.end_time))
    );

    console.log("JS now:", new Date(Date.now()));

    console.log(
        "remaining MS: ",
        Date.parse(activeRental.end_time) - Date.now()
    );
}

async function fetchLockerStatuses() {
    try {
        const res = await fetch("/api/kiosk/lockers/status");
        if (!res.ok) throw new Error("Failed to load lockers");

        const data = await res.json();
        lockers.value = data.lockers;
    } catch (err) {
        console.error("Locker status error:", err);
    }
}

// ===================== rental end handler (no backend) =====================
// function handleEndRental() {
//     rental.endRental();

//     // Show success overlay
//     isEndingRental.value = true;
//     endCountdown.value = 3;

//     endTimer = setInterval(() => {
//         endCountdown.value--;

//         if (endCountdown.value === 0) {
//             clearInterval(endTimer);
//             isEndingRental.value = false;
//             flow.goToIdle();
//         }
//     }, 1000);
// }

async function handleEndRental() {
    try {
        const rentalId = session.state.locker?.rentalId;
        if (!rentalId) return;

        await fetch(`/api/kiosk/rentals/${rentalId}/end`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
        });

        //stop local timer + clear rental state
        rental.endRental();
        await fetchLockerStatuses();

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
    } catch (err) {
        console.error("Failed to end rental", err);
        return;
    }
}

async function hydrateGlobalState() {
    await fetchLockerStatuses();
    await recoverActiveRental();
}

onMounted(() => {
    hydrateGlobalState();
});
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
    <!-- <div class="fixed top-2 left-2 bg-black text-white px-3 py-1 z-50">
        {{ session.state.kioskState }}
    </div> -->
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
            @dev-go-locker-select="flow.goToLockerSelect()"
            @dev-reset-session="handlEndSession()"
            @dev-go-payment="flow.goToPayment()"
        />

        <LockerSelectScreen
            v-else-if="session.state.kioskState === KIOSK_STATES.LOCKER_SELECT"
            :lockers="lockers"
            @back="handleLockerSelectBack"
            @confirm="handleLockerSelectConfirm"
            @end-session="handlEndSession"
        />

        <PaymentScreen
            v-else-if="session.state.kioskState === KIOSK_STATES.PAYMENT"
            :locker="paymentContext.locker"
            :duration="paymentContext.duration"
            :mode="paymentContext.mode"
            :amount="paymentContext.amount"
            :penalty="paymentContext.penalty"
            :lockerEndTime="session.state.locker?.endTime"
            :canEndSession="actions.canEndSession.value"
            @end-session="handlEndSession"
            @cancel="handlePaymentCancel"
            @complete="handlePaymentComplete"
        />
    </transition>
    <IdleWarningModal
        v-if="idle.showIdleWarning"
        :show="idle.showIdleWarning.value"
        :secondsLeft="idle.idleCountdown.value"
        @confirm="idle.confirmIdleNow"
        @continue="idle.dismissWarning"
    />
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
