/**
 * useLockerRental
 * ----------------
 * Handles locker rental lifecycle logic.
 *
 * Owns:
 * - rental state transitions
 * - locker assignment
 *
 * Does NOT own:
 * - session creation
 * - timers
 * - penalties
 * - UI state
 */
export function useLockerRental(state) {
    /**
     * Rent a locker to the active student.
     * Legal only when:
     * - a session exists
     * - no active rental exists
     */
    function rentLocker(lockerNumber) {
        if (!state.student) return;
        if (state.rentalState !== "NO_RENTAL") return;

        state.locker = {
            number: lockerNumber,
            startTime: Date.now(),
            endTime: null, // will be set when timers are introduced
            timeRemaining: null,
        };

        state.rentalState = "ACTIVE_RENTAL";
    }

    /**
     * End an active rental normally.
     * Legal only when rental is ACTIVE.
     */
    function endRental() {
        if (state.rentalState !== "ACTIVE_RENTAL") return;

        state.locker = null;
        state.rentalState = "NO_RENTAL";
    }

    return {
        rentLocker,
        endRental,
    };
}
