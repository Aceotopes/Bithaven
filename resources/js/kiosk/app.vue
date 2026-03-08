<script setup>
import { ref, onMounted, computed, watch, onBeforeUnmount } from "vue";

import { KIOSK_STATES } from "./constants/kioskStates";
import { useKioskFlow } from "./composables/useKioskFlow";
import { useKioskSession } from "./composables/useKioskSessions";
import { useLockerRental } from "./composables/useLockerRental";
import { usePenalty } from "./composables/usePenalty";
import { useKioskActions } from "./composables/useKioskActions";
import { useIdleTimeout } from "./composables/useIdleTimeout";

import IdleWarningModal from "@/kiosk/components/kiosk/IdleWarningModal.vue";
import AdminConfirmModal from "@/kiosk/components/kiosk/AdminConfirmModal.vue";
import IdleScreen from "./screens/IdleScreen.vue";
import MainScreen from "./screens/MainScreen.vue";
import LockerSelectScreen from "./screens/LockerSelectScreen.vue";
import PaymentScreen from "./screens/PaymentScreen.vue";
import AdminAccessScreen from "./screens/AdminAccessScreen.vue";
import ProcessingScreen from "./screens/ProcessingScreen.vue";
import UnlockSuccessScreen from "./screens/UnlockSuccessScreen.vue";

import KioskToast from "@/kiosk/components/kiosk/KioskToast.vue";

import { useRFIDService } from "./services";

const showAdminConfirm = ref(false);
const pendingAdminAction = ref(null);

const toastMessage = ref(null);
const toastType = ref("success");

function showToast(message, type = "success") {
    toastMessage.value = message;
    toastType.value = type;

    setTimeout(() => {
        toastMessage.value = null;
    }, 3000);
}

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
// const isEndingRental = ref(false); // rental ending state
// const endCountdown = ref(3); // rental end countdown
// const TOTAL_LOCKERS = 15; //mock total lockers
const lockers = ref([]);

const unlockStage = ref(null);
const activeLockerNumber = ref(null);

const rfid = useRFIDService();
const scanResult = ref(null);
const paymentSession = ref(null);

// let endTimer = null;

const penaltySnapshot = ref(null);

const activeScanSession = ref(null);
const showAdminScanModal = ref(false);

const adminLockers = ref([]);
const selectedAdminLocker = ref(null);
const showAdminDetails = ref(false);

let scanPollTimer = null;
// let currentPollMode = null;

// =======================================
// locker rental manager for testing (no Backend)
// const rental = useLockerRental(session.state, {
//     onExpire: () => {
//         penalty.applyPenalty();
//     },
// });
// =======================================
function requestAdminAction(action) {
    pendingAdminAction.value = action;
    showAdminConfirm.value = true;
}

async function executeAdminAction() {
    showAdminConfirm.value = false;

    const action = pendingAdminAction.value;
    const lockerNumber = selectedAdminLocker.value?.locker?.number;
    const adminUid = scanResult.value?.rfid_uid;

    try {
        let res;

        switch (action) {
            case "forceUnlock":
                res = await fetch("/api/kiosk/admin/force-unlock", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        rfid_uid: adminUid,
                        locker_number: lockerNumber,
                        kiosk_id: "KIOSK-01",
                    }),
                });
                break;

            case "disableLocker":
                res = await fetch("/api/kiosk/admin/lockers/disable", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        admin_card_uid: adminUid,
                        locker_number: lockerNumber,
                    }),
                });
                break;

            case "enableLocker":
                res = await fetch("/api/kiosk/admin/lockers/enable", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        admin_card_uid: adminUid,
                        locker_number: lockerNumber,
                    }),
                });
                break;

            case "clearPenalty":
                res = await fetch("/api/kiosk/admin/clear-penalty", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        rfid_uid: adminUid,
                        penalty_id: selectedAdminLocker.value.penalty?.id,
                        kiosk_id: "KIOSK-01",
                    }),
                });
                break;

            case "endRental":
                res = await fetch("/api/kiosk/admin/end-rental", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        rental_id: selectedAdminLocker.value.rental?.id,
                        admin_card_uid: adminUid,
                    }),
                });
                break;
        }

        if (!res.ok) {
            const err = await res.json();
            throw new Error(err.error || "Action failed");
        }

        showToast("Action completed successfully.", "success");

        await fetchAdminLockers();

        if (lockerNumber) {
            await handleAdminSelectLocker(lockerNumber);
        }
    } catch (err) {
        showToast(err.message, "error");
    }
}
// =====================
//    ADMIN FUNCTIONS
// =====================
// ===================== scan session polling (with Backend) =====================
async function pollScanSession() {
    try {
        const res = await fetch("/api/kiosk/rfid/pending");
        if (!res.ok) return;

        const session = await res.json();

        if (session && session.status === "PENDING") {
            activeScanSession.value = session;
            showAdminScanModal.value = true;
        } else {
            activeScanSession.value = null;
            showAdminScanModal.value = false;

            stopScanPolling();
        }
    } catch (err) {
        console.error("Scan polling failed", err);
        stopScanPolling();
    }
}

const scanCountdown = computed(() => {
    if (!activeScanSession.value) return 0;

    const expiresAt = new Date(activeScanSession.value.expires_at).getTime();
    const now = Date.now();

    return Math.max(0, Math.ceil((expiresAt - now) / 1000));
});

function stopScanPolling() {
    if (scanPollTimer) {
        clearInterval(scanPollTimer);
        scanPollTimer = null;
        console.log("Scan polling stopped");
    }
}

function startScanPolling() {
    if (scanPollTimer) return;

    scanPollTimer = setInterval(pollScanSession, 1000);
    console.log("🟢 Scan polling started");
}

// function startSlowPolling() {
//     if (currentPollMode === "SLOW") return;

//     stopScanPolling();

//     scanPollTimer = setInterval(pollScanSession, 3000);
//     currentPollMode = "SLOW";

//     console.log("Scan polling: SLOW (4s)");
// }

// function startFastPolling() {
//     if (currentPollMode === "FAST") return;

//     stopScanPolling();

//     scanPollTimer = setInterval(pollScanSession, 1000);
//     currentPollMode = "FAST";

//     console.log("Scan polling: FAST (1s)");
// }
// ===============================================================================

// ===================== locker rental manager (with Backend) =====================
const rental = useLockerRental(session.state, {
    onExpire: async (rentalId) => {
        // Inform backend that rental expired
        await fetch(`/api/kiosk/rentals/${rentalId}/expire`, {
            method: "POST",
        });

        // Rehydrate state from backend
        await hydrateGlobalState();
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
async function handleStartScan(uid) {
    console.log("📟 RFID scanned:", uid);

    // -------------------------------------------------
    // ADMIN POLLING SESSION COMPLETION (KEEP THIS)
    // -------------------------------------------------
    await pollScanSession();

    if (activeScanSession.value) {
        try {
            await fetch(
                `/api/kiosk/rfid/${activeScanSession.value.id}/complete`,
                {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ rfid_uid: uid }),
                }
            );

            showAdminScanModal.value = false;
            activeScanSession.value = null;

            console.log("Admin scan completed");
            startScanPolling();
            return;
        } catch (err) {
            console.error("Failed to complete admin scan", err);
            return;
        }
    }

    // -------------------------------------------------
    // CHECK IF ADMIN CARD (DIRECT ADMIN TAP)
    // -------------------------------------------------
    try {
        const adminRes = await fetch("/api/kiosk/admin/scan", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                rfid_uid: uid,
                kiosk_id: "KIOSK-01",
            }),
        });

        const adminData = await adminRes.json();

        if (adminData.is_admin) {
            if (!adminData.active) {
                scanResult.value = {
                    status: "error",
                };
                return;
            }

            scanResult.value = {
                status: "admin",
                card: adminData.card,
                rfid_uid: uid,
            };
            return;
        }
    } catch (err) {
        console.error("Admin check failed", err);
    }

    // -------------------------------------------------
    // STUDENT CHECK
    // -------------------------------------------------
    try {
        const res = await fetch("/api/kiosk/scan", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ rfid_uid: uid }),
        });

        const data = await res.json();

        // suspended
        if (data.status === "suspended") {
            scanResult.value = { status: "suspended" };
            return;
        }

        // unknown card
        if (!res.ok) {
            scanResult.value = { status: "error" };
            return;
        }

        // success
        scanResult.value = {
            status: "success",
            student: data.student,
        };
    } catch (err) {
        console.error("RFID scan failed", err);
        scanResult.value = { status: "error" };
    }
}

async function fetchAdminLockers() {
    const res = await fetch("/api/kiosk/admin/lockers");
    const data = await res.json();
    adminLockers.value = data.lockers;
}

async function handleAdminSelectLocker(lockerNumber) {
    const res = await fetch(`/api/kiosk/admin/lockers/${lockerNumber}`);
    selectedAdminLocker.value = await res.json();
    showAdminDetails.value = true;
}

async function handleIdleComplete() {
    const result = scanResult.value;
    if (!result) return;

    if (result.status === "admin") {
        await fetchAdminLockers();
        flow.goToAdminAccess();
        return;
    }

    if (result.status === "success") {
        session.startSession(result.student);
        recoverActiveRental();
        recoverActivePenalty();
        rfid.disable();
        flow.goToStudentDashboard();
        return;
    }

    // suspended & error → stay idle
}

// ===================== locker selection (back and confirm)handlers =====================
function handleLockerSelectBack() {
    flow.goToStudentDashboard();
}

async function unlockLocker(lockerNumber) {
    if (!lockerNumber) return;

    console.log("Authorizing locker:", lockerNumber);

    const res = await fetch(`/api/kiosk/lockers/${lockerNumber}/authorize`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
    });

    if (!res.ok) {
        const err = await res.json();
        console.error("Authorization failed:", err);
        return false;
    }

    console.log("Unlock Authorized. Waiting for daemon confirmation...");
    return true;
}

async function handleLockerSelectConfirm(payload) {
    // TEMP: validate flow only
    console.log("Locker selection confirmed:", payload);
    const { locker, duration } = payload;

    const res = await fetch("/api/kiosk/payment-sessions/start", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            kiosk_id: "KIOSK-01",
            student_id: session.state.student.id,
            context_type: "RENTAL",
            locker_id: locker,
            duration_hours: duration,
        }),
    });

    const data = await res.json();
    paymentSession.value = data.session;

    paymentContext.value = {
        mode: "RENTAL",
        amount: data.session.amount_due, // pricing logic (temporary)
        locker,
        duration,
        penalty: null,
    };

    flow.goToPayment();
}
// =======================================================================================

// display only penalty settlement handler
// function handleSettlePenalty() {
//     const penalty = session.state.penalty;
//     const locker = session.state.locker;

//     if (!penalty || !locker) return;

//     paymentContext.value = {
//         mode: "PENALTY",
//         penaltyId: penalty.id,
//         locker: locker.number,
//         amount: penalty.amount, // backend-authoritative
//         penalty: null, // no snapshot
//     };

//     flow.goToPayment();
// }

async function handleSettlePenalty() {
    const penaltyState = session.state.penalty;
    if (!penaltyState) return;

    // stop live penalty accumulation
    penalty.stopLivePenalty();

    const res = await fetch("/api/kiosk/payment-sessions/start", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            student_id: session.state.student.id,
            kiosk_id: "KIOSK-01",
            context_type: "PENALTY",
            penalty_id: penaltyState.id,
        }),
    });

    if (!res.ok) {
        console.error("Failed to start penalty payment session");

        penalty.startLivePenalty();
        return;
    }

    const data = await res.json();

    paymentSession.value = data.session;

    // backend-frozen snapshot
    penaltySnapshot.value = {
        amount: Number(data.penalty_snapshot.amount),
        breakdown: data.penalty_snapshot.breakdown ?? [],
        exceededDuration: data.penalty_snapshot.exceeded_duration ?? "00:00:00",
    };

    paymentContext.value = {
        mode: "PENALTY",
        locker: session.state.locker?.number,
        amount: data.session.amount_due,
        penaltyId: penaltyState.id,
    };

    flow.goToPayment();
}

function handlEndSession() {
    session.clearSession();
    console.log("Session ended.");
    flow.goToIdle();
}

function handleRentLocker() {
    if (session.state.penalty?.status === "ACTIVE") {
        return;
    }

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
    penaltySnapshot.value = null;
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
    console.log("PAYMENT START");
    console.log("PAYMENT COMPLETE → Authorizing unlock");
    console.log("locker:", session.state.locker);
    console.log("rentalState:", session.state.rentalState);
    console.log("paymentContext:", paymentContext.value);

    if (paymentContext.value.mode === "RENTAL") {
        await unlockLocker(paymentContext.value.locker);
        await hydrateGlobalState();
        await fetchLockerStatuses();
    }

    // ===================== PENALTY PAYMENT =====================
    if (paymentContext.value.mode === "PENALTY") {
        await unlockLocker(session.state.locker?.number);
        await hydrateGlobalState();
    }

    // CLEANUP
    resetPaymentContext();
    session.clearSession();
    flow.goToIdle();
}

async function recoverActiveRental() {
    console.log("🔎 recoverActiveRental START");

    if (!session.state.student) {
        console.log("❌ no student, abort recoverActiveRental");
        return;
    }

    const res = await fetch(
        `/api/kiosk/rentals/active?student_id=${session.state.student.id}`
    );

    if (!res.ok) {
        console.log("❌ rentals/active request failed");
        return;
    }

    const { rental: activeRental } = await res.json();
    console.log("📦 backend rental:", activeRental);

    if (!activeRental) {
        console.log("ℹ️ no active rental found");
        rental.endRental(); // stop timer safely
        session.state.locker = null;
        session.state.rentalState = "NO_RENTAL";
        return;
    }

    console.log("📊 rental status:", activeRental.status);
    //rehydrate sesssion state
    rental.hydrateRental({
        id: activeRental.id,
        lockerNumber: activeRental.locker_number,
        startTime: Date.parse(activeRental.start_time),
        endTime: Date.parse(activeRental.end_time),
    });

    console.log("✅ hydrateRental called");
    // console.log("Backend start_time:", activeRental.start_time);
    // console.log("Backend end_time: ", activeRental.end_time);

    // console.log(
    //     "Parsed startTime: ",
    //     new Date(Date.parse(activeRental.start_time))
    // );
    // console.log(
    //     "Parsed endTime: ",
    //     new Date(Date.parse(activeRental.end_time))
    // );

    // console.log("JS now:", new Date(Date.now()));

    // console.log(
    //     "remaining MS: ",
    //     Date.parse(activeRental.end_time) - Date.now()
    // );
}

async function recoverActivePenalty() {
    if (!session.state.student) return;

    const res = await fetch(
        `/api/kiosk/penalties/active?student_id=${session.state.student.id}`
    );

    if (!res.ok) return;

    const { penalty } = await res.json();

    if (!penalty) {
        // CLEAR frontend penalty state
        session.state.penalty = null;
        // session.state.rentalState = "NO_RENTAL";
        // session.state.locker = null;
        // // If rental was expired, fully reset it
        // if (session.state.rentalState === "EXPIRED_RENTAL") {
        //     session.state.rentalState = "NO_RENTAL";
        //     session.state.locker = null;
        // }
        return;
    }

    session.state.penalty = {
        id: penalty.id,
        rentalId: penalty.rental_id,
        status: penalty.status,

        // backend authoritative
        amount: Number(penalty.amount),
        breakdown: penalty.breakdown,
        exceeded_duration: penalty.exceeded_duration,

        frozen_at: penalty.frozen_at ?? null,
        frozen_amount: penalty.frozen_amount ?? null,
    };
    session.state.rentalState = "EXPIRED_RENTAL";
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
        const lockerNumber = session.state.locker?.number;

        if (!rentalId) return;

        activeLockerNumber.value = lockerNumber;

        unlockStage.value = "PROCESSING";

        const startTime = Date.now();

        await fetch(`/api/kiosk/rentals/${rentalId}/end`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
        });

        await unlockLocker(lockerNumber);

        //stop local timer + clear rental state
        rental.endRental();
        await fetchLockerStatuses();

        //TEMPORARY TIMER TO SIMULATE PROCESSING TIME (ENSURE SUCCESS OVERLAY IS VISIBLE)
        const elapsed = Date.now() - startTime;
        const minDuration = 5000; // 5 seconds

        if (elapsed < minDuration) {
            await new Promise((resolve) =>
                setTimeout(resolve, minDuration - elapsed)
            );
        }

        unlockStage.value = "SUCCESS";

        // // Show success overlay
        // isEndingRental.value = true;
        // endCountdown.value = 3;

        // endTimer = setInterval(() => {
        //     endCountdown.value--;

        //     if (endCountdown.value === 0) {
        //         clearInterval(endTimer);
        //         isEndingRental.value = false;
        //         session.clearSession();
        //         flow.goToIdle();
        //     }
        // }, 1000);
    } catch (err) {
        console.error("Failed to end rental", err);
        unlockStage.value = null;
        return;
    }
}
function finishEndRental() {
    unlockStage.value = null;
    session.clearSession();
    flow.goToIdle();
}

function debugDump(label) {
    console.group(`🧪 DEBUG DUMP — ${label}`);
    console.log("kioskState:", session.state.kioskState);
    console.log("student:", session.state.student);
    console.log("rentalState:", session.state.rentalState);
    console.log("locker:", session.state.locker);
    console.log("penalty:", session.state.penalty);
    console.log("lockers list:", lockers.value);
    console.groupEnd();
}

async function hydrateGlobalState() {
    console.log("🌍 hydrateGlobalState START");
    await fetchLockerStatuses();
    await recoverActiveRental();
    await recoverActivePenalty();
    console.log("🌍 hydrateGlobalState END");
}

onMounted(async () => {
    console.log("🔁 APP MOUNTED");
    await hydrateGlobalState();
    debugDump("after hydrateGlobalState (mount)");
    // startSlowPolling();
});

onBeforeUnmount(() => {
    stopScanPolling();
});

watch(
    () => session.state.rentalState,
    (state) => {
        if (state === "EXPIRED_RENTAL") {
            penalty.startLivePenalty();
        } else {
            penalty.stopLivePenalty();
        }
    },
    { immediate: true }
);

// onMounted(async () => {
//     if (session.state.student) {
//         await hydrateGlobalState();
//     }
// });
// TODO (Phase 3): Add polling when admin dashboard can modify rentals

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
            @success-complete="handleIdleComplete"
            :scanResult="scanResult"
        />

        <AdminAccessScreen
            v-else-if="session.state.kioskState === KIOSK_STATES.ADMIN_ACCESS"
            :lockers="adminLockers"
            :selectedLockerDetails="selectedAdminLocker"
            :showDetails="showAdminDetails"
            @select-locker="handleAdminSelectLocker"
            @close-details="showAdminDetails = false"
            @exit-admin="flow.goToIdle()"
            @force-unlock="() => requestAdminAction('forceUnlock')"
            @disable-locker="() => requestAdminAction('disableLocker')"
            @enable-locker="() => requestAdminAction('enableLocker')"
            @clear-penalty="() => requestAdminAction('clearPenalty')"
            @end-rental="() => requestAdminAction('endRental')"
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
            :penaltyAmount="penalty.livePenaltyAmount.value"
            :canRent="actions.canRent.value"
            :canEndRental="actions.canEndRental.value"
            :canSettlePenalty="actions.canSettlePenalty.value"
            :canEndSession="actions.canEndSession.value"
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
            :amountPaid="
                paymentSession.value
                    ? Number(paymentSession.value.amount_paid)
                    : 0
            "
            :paymentStatus="
                paymentSession.value ? paymentSession.value.status : 'ACTIVE'
            "
            :penalty="paymentContext.penalty"
            :lockerEndTime="session.state.locker?.endTime"
            :canEndSession="actions.canEndSession.value"
            :penaltySnapshot="penaltySnapshot"
            @end-session="handlEndSession"
            @cancel="handlePaymentCancel"
            @complete="handlePaymentComplete"
            @session-updated="
                (s) => {
                    console.log('🟢 App received session:', s);
                    paymentSession.value = s;
                    console.log(
                        '🟢 paymentSession ref is now:',
                        paymentSession.value
                    );
                }
            "
        />
    </transition>
    <div>
        <!-- PROCESSING SCREEN -->
        <ProcessingScreen
            v-if="unlockStage === 'PROCESSING'"
            :locker="activeLockerNumber"
        />

        <!-- SUCCESS SCREEN -->
        <UnlockSuccessScreen
            v-if="unlockStage === 'SUCCESS'"
            :locker="activeLockerNumber"
            mode="END_RENTAL"
            @done="finishEndRental"
        />
    </div>
    <IdleWarningModal
        v-if="idle.showIdleWarning"
        :show="idle.showIdleWarning.value"
        :secondsLeft="idle.idleCountdown.value"
        @confirm="idle.confirmIdleNow"
        @continue="idle.dismissWarning"
    />
    <AdminConfirmModal
        :show="showAdminConfirm"
        message="Are you sure you want to proceed?"
        @cancel="showAdminConfirm = false"
        @confirm="executeAdminAction"
    />
    <KioskToast v-if="toastMessage" :message="toastMessage" :type="toastType" />
    <div
        v-if="showAdminScanModal"
        class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
    >
        <div class="bg-white p-8 rounded-xl text-center w-96">
            <h2 class="text-xl font-semibold mb-4">
                Admin is requesting RFID scan
            </h2>

            <p class="mb-4">Please tap your card now.</p>

            <p class="text-red-600 font-bold text-lg">
                {{ scanCountdown }} seconds remaining
            </p>
        </div>
    </div>
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
