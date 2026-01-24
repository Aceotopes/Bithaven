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
    const occupiedLockers = ref(new Set());

    /**
     * Rent a locker to the active student.
     * Legal only when:
     * - a session exists
     * - no active rental exists
     */
    function rentLocker(lockerNumber, durationHours) {
        if (!state.student) return;
        if (state.rentalState !== "NO_RENTAL") return;
        if (occupiedLockers.value.has(lockerNumber)) return;

        occupiedLockers.value.add(lockerNumber);

        const now = Date.now();
        const durationMs = durationHours * 10 * 1000;
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

    /**
     * End an active rental normally.
     * Legal only when rental is ACTIVE.
     */
    function endRental() {
        if (state.rentalState !== "ACTIVE_RENTAL") return;

        const lockerNumber = state.locker.number;

        stop();
        state.locker = null;
        state.rentalState = "NO_RENTAL";

        occupiedLockers.value.delete(lockerNumber);
    }

    function expireRental() {
        stop();

        if (state.locker) {
            state.locker.timeRemaining = "00:00:00";
            occupiedLockers.value.add(state.locker.number);
        }

        state.rentalState = "EXPIRED_RENTAL";
        // 🔔 Notify penalty system
        onExpire?.();
    }

    return {
        rentLocker,
        endRental,
        occupiedLockers,
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
