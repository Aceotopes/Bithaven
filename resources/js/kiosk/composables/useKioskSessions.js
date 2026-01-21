import { reactive } from "vue";
import { KIOSK_STATES } from "../constants/kioskStates";
/**
 * useKioskSession
 * ----------------
 * Central session store for the kiosk.
 * Owns all session-bound state.
 *
 * UI MUST NOT duplicate this state.
 */

export function useKioskSession() {
    const state = reactive({
        kioskState: KIOSK_STATES.IDLE,

        // Student session
        student: null,

        // Rental lifecycle
        rentalState: "NO_RENTAL", // NO_RENTAL | ACTIVE_RENTAL | EXPIRED_RENTAL
        locker: null,

        // Penalty lifecycle
        penalty: null,
    });

    function startSession(studentData) {
        state.student = studentData;
    }

    function clearSession() {
        state.student = null;
        // IMPORTANT: DO NOT touch rental or penalty here
    }

    return {
        state,
        startSession,
        clearSession,
    };
}
