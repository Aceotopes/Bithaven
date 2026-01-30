import { useTimer } from "./useTimer";
import { ref } from "vue";

/**
 * useLockerRental
 * ----------------
 * Handles locker rental lifecycle logic.
 * Rental lifecycle + expiry handling.
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
export function useLockerRental(state, { onExpire }) {
    const { start, stop } = useTimer();

    /**
     * Rent a locker to the active student.
     * Legal only when:
     * - a session exists
     * - no active rental exists
     */
    function rentLocker(lockerNumber, durationHours) {
        if (!state.student) return;
        if (state.rentalState !== "NO_RENTAL") return;

        const now = Date.now();
        const durationMs = durationHours * 60 * 60 * 1000;
        const endTime = now + durationMs;

        state.locker = {
            number: lockerNumber,
            startTime: now,
            endTime,
            timeRemaining: formatDuration(durationMs),
        };

        state.rentalState = "ACTIVE_RENTAL";

        start(
            endTime,
            (remaining) => {
                state.locker.timeRemaining = formatDuration(remaining);
            },
            expireRental
        );
    }

    function hydrateRental(rental) {
        if (!rental) return;

        stop(); // Stop any existing timer

        const now = Date.now();
        const remainingMs = rental.endTime - now;

        state.locker = {
            number: rental.lockerNumber,
            startTime: rental.startTime,
            endTime: rental.endTime,
            timeRemaining: formatDuration(remainingMs),
        };

        state.rentalState = "ACTIVE_RENTAL";

        start(
            rental.endTime,
            (remaining) => {
                state.locker.timeRemaining = formatDuration(remaining);
            },
            expireRental
        );
    }

    /**
     * End an active rental normally.
     * Legal only when rental is ACTIVE.
     */
    function endRental() {
        if (state.rentalState !== "ACTIVE_RENTAL") return;

        stop();
        state.locker = null;
        state.rentalState = "NO_RENTAL";
    }

    function expireRental() {
        stop();

        if (state.locker) {
            state.locker.timeRemaining = "00:00:00";
        }

        state.rentalState = "EXPIRED_RENTAL";
        // 🔔 Notify penalty system
        onExpire?.();
    }

    return {
        rentLocker,
        endRental,
        hydrateRental,
    };
}

/* --------------------
 * Utilities
 * -------------------- */
function formatDuration(ms) {
    const totalSeconds = Math.max(0, Math.floor(ms / 1000));
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;

    return [h, m, s].map((v) => String(v).padStart(2, "0")).join(":");
}
