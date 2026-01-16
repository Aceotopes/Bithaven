import { reactive } from "vue";

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
        // Student session
        student: null,

        // Rental placeholders (logic added later)
        rentalState: "NO_RENTAL", // NO_RENTAL | ACTIVE_RENTAL | EXPIRED_RENTAL
        locker: null,
        penalty: null,
    });

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
