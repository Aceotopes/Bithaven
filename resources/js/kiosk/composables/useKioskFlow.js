import { ref } from "vue";
import { KIOSK_STATES } from "@/kiosk/constants/kioskStates";

/**
 * useKioskFlow
 * -------------
 * Centralized kiosk screen navigation.
 * SINGLE source of truth for kioskState.
 */
const kioskState = ref(KIOSK_STATES.IDLE);

export function useKioskFlow() {
    function goToIdle() {
        kioskState.value = KIOSK_STATES.IDLE;
    }

    function goToScan() {
        kioskState.value = KIOSK_STATES.SCAN;
    }

    function goToStudentDashboard() {
        kioskState.value = KIOSK_STATES.STUDENT_DASHBOARD;
    }

    function goToLockerSelect() {
        kioskState.value = KIOSK_STATES.LOCKER_SELECT;
    }

    function goToPayment() {
        kioskState.value = KIOSK_STATES.PAYMENT;
    }

    function goToProcessing() {
        kioskState.value = KIOSK_STATES.PROCESSING;
    }

    function goToActiveSession() {
        kioskState.value = KIOSK_STATES.ACTIVE_SESSION;
    }

    function goToDone() {
        kioskState.value = KIOSK_STATES.DONE;
    }

    function goToError() {
        kioskState.value = KIOSK_STATES.ERROR;
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
    };
}
