import { computed } from "vue";

/**
 * usePenalty
 * ----------
 * Owns penalty lifecycle logic.
 *
 * Responsibilities:
 * - apply penalty on expiry
 * - expose penalty state
 * - settle penalty
 *
 * Does NOT own:
 * - timers
 * - rentals
 * - payments (mock only)
 */
export function usePenalty(state) {
    /**
     * Apply penalty when rental expires.
     * Called ONCE at expiry.
     */
    function applyPenalty() {
        if (state.penalty) return;

        state.penalty = {
            startedAt: Date.now(), // when penalty started
            isPaid: false,
        };
    }

    /**
     * Settle the active penalty.
     */
    function settlePenalty() {
        if (
            state.rentalState !== "EXPIRED_RENTAL" ||
            !state.penalty ||
            state.penalty.isPaid
        )
            return;

        state.penalty.isPaid = true;

        // Clear rental
        state.locker = null;
        state.penalty = null;
        state.rentalState = "NO_RENTAL";
    }

    /**
     * Derived penalty amount (REAL TIME)
     */
    const penaltyAmount = computed(() => {
        if (
            !state.penalty ||
            !state.locker ||
            typeof state.locker.endTime !== "number"
        ) {
            return 0;
        }

        const exceededMs = Date.now() - state.locker.endTime;

        if (exceededMs <= 0) return 0;

        return calculatePenalty(exceededMs);
    });

    /**
     * UI permission
     */
    const canSettlePenalty = computed(() => {
        return (
            state.rentalState === "EXPIRED_RENTAL" &&
            !!state.penalty &&
            !state.penalty.isPaid
        );
    });

    function getPenaltySnapshot() {
        if (!state.penalty || !state.locker) return null;

        const exceededMs = Math.max(0, Date.now() - state.locker.endTime);

        return {
            exceededDuration: formatExceededTime(exceededMs),
            breakdown: buildPenaltyBreakdown(exceededMs),
            amount: calculatePenalty(exceededMs),
        };
    }

    return {
        applyPenalty,
        settlePenalty,
        canSettlePenalty,
        penaltyAmount,
        getPenaltySnapshot,
    };
}

/* --------------------
 * Utilities
 * -------------------- */

function calculatePenalty(exceededMs) {
    const exceededMinutes = Math.floor(exceededMs / 60000);

    let penalty = 5; // base penalty on expiry

    // every additional 30 minutes → +5
    const halfHours = Math.floor(exceededMinutes / 30);
    penalty += halfHours * 5;

    // every full hour → +10
    const fullHours = Math.floor(exceededMinutes / 60);
    penalty += fullHours * 10;

    return penalty;
}

function formatExceededTime(ms) {
    const minutes = Math.floor(ms / 60000);
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    if (hours === 0) return `${minutes} minute/s`;
    return `${hours}h ${remainingMinutes}m`;
}

function buildPenaltyBreakdown(ms) {
    const minutes = Math.floor(ms / 60000);
    const breakdown = [];

    breakdown.push({ label: "Initial expiry", amount: 5 });

    const halfHours = Math.floor(minutes / 30);
    for (let i = 1; i <= halfHours; i++) {
        breakdown.push({ label: `+30 mins`, amount: 5 });
    }

    const hours = Math.floor(minutes / 60);
    for (let i = 1; i <= hours; i++) {
        breakdown.push({ label: `+1 hour`, amount: 10 });
    }

    return breakdown;
}
