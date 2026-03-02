import { ref } from "vue";
import { KIOSK_STATES } from "@/kiosk/constants/kioskStates";

/**
 * useKioskFlow
 * -------------
 * Centralized kiosk screen navigation.
 * SINGLE source of truth for kioskState.
 */
const kioskState = ref(KIOSK_STATES.IDLE);

export function useKioskFlow(state) {
    function goToIdle() {
        state.kioskState = KIOSK_STATES.IDLE;
    }

    function goToScan() {
        state.kioskState = KIOSK_STATES.SCAN;
    }

    function goToStudentDashboard() {
        state.kioskState = KIOSK_STATES.STUDENT_DASHBOARD;
    }

    function goToLockerSelect() {
        state.kioskState = KIOSK_STATES.LOCKER_SELECT;
    }

    function goToPayment() {
        state.kioskState = KIOSK_STATES.PAYMENT;
    }

    function goToProcessing() {
        state.kioskState = KIOSK_STATES.PROCESSING;
    }

    function goToActiveSession() {
        state.kioskState = KIOSK_STATES.ACTIVE_SESSION;
    }

    function goToDone() {
        state.kioskState = KIOSK_STATES.DONE;
    }

    function goToError() {
        state.kioskState = KIOSK_STATES.ERROR;
    }

    function goToAdminAccess() {
        state.kioskState = KIOSK_STATES.ADMIN_ACCESS;
    }

    return {
        kioskState,
        goToIdle,
        goToScan,
        goToStudentDashboard,
        goToLockerSelect,
        goToPayment,
        goToProcessing,
        goToActiveSession,
        goToDone,
        goToError,
        goToAdminAccess,
    };
}
