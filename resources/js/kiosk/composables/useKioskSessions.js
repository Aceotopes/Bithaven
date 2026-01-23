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

        // How to use locker tutorial seen
        hasSeenHowTo: false,
    });

    function startSession(studentData) {
        state.student = studentData;
        state.hasSeenHowTo = false;
    }

    function clearSession() {
        state.student = null;
        state.hasSeenHowTo = false;
        // IMPORTANT: DO NOT touch rental or penalty here
    }

    return {
        state,
        startSession,
        clearSession,
    };
}
