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
        // if (!rental) return;
        console.group("⏱ hydrateRental");
        console.log("incoming rental:", rental);

        stop();

        const now = Date.now();
        const remainingMs = rental.endTime - now;

        console.group("⏱ hydrateRental");
        console.log("incoming rental:", rental);

        state.locker = {
            rentalId: rental.id,
            number: rental.lockerNumber,
            startTime: rental.startTime,
            endTime: rental.endTime,
            timeRemaining:
                remainingMs > 0 ? formatDuration(remainingMs) : "00:00:00",
        };

        state.rentalState =
            remainingMs > 0 ? "ACTIVE_RENTAL" : "EXPIRED_RENTAL";

        console.log("state.rentalState set to:", state.rentalState);

        if (remainingMs > 0) {
            // ACTIVE RENTAL
            // state.rentalState = "ACTIVE_RENTAL";

            start(
                rental.endTime,
                (remaining) => {
                    state.locker.timeRemaining = formatDuration(remaining);
                },
                expireRental
            );
            console.log("▶️ timer started");
        } else {
            console.log("⚠️ timer NOT started (expired)");
            // EXPIRED RENTAL
            // state.rentalState = "EXPIRED_RENTAL";
            // ❌ DO NOT start countdown timer
            // exceeded timer is handled by LockerStatusCard
        }
        console.groupEnd();
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

        const rentalId = state.locker?.rentalId;

        if (!rentalId) {
            console.warn("Expire fired without rentalId — ignoring");
            return;
        }

        state.rentalState = "EXPIRED_RENTAL";

        onExpire?.(rentalId);
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
