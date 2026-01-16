import { KIOSK_STATES } from "@/kiosk/constants/kioskStates";

/**
 * useKioskFlow
 * -------------
 * Centralized kiosk screen navigation.
 *
 * Screens must NEVER change kioskState directly.
 */
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

    return {
        goToIdle,
        goToScan,
        goToStudentDashboard,
        goToLockerSelect,
        goToPayment,
        goToProcessing,
        goToActiveSession,
        goToDone,
        goToError,
    };
}
