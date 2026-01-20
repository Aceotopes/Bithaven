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

export function useKioskSession(state) {
    /**
     * Start a new student session.
     * Called after ID tap (mocked for now).
     */
    function startSession(studentData) {
        state.student = studentData;
    }

    /**
     * Clear the current session.
     * This is a full reset.
     */
    function clearSession() {
        state.kioskState = KIOSK_STATES.IDLE;
        state.student = null;
        state.rentalState = "NO_RENTAL";
        state.locker = null;
        state.penalty = null;
    }

    return {
        state,
        startSession,
        clearSession,
    };
}
