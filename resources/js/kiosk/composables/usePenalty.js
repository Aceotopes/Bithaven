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
     * Apply a penalty when a rental expires.
     * This must be called exactly once.
     */
    function applyPenalty() {
        if (state.penalty) return; // prevent double-application

        state.penalty = {
            amount: calculatePenalty(),
            isPaid: false,
        };
    }

    /**
     * Settle the active penalty.
     * Mocked payment success.
     */
    function settlePenalty() {
        if (
            state.rentalState !== "EXPIRED_RENTAL" ||
            !state.penalty ||
            state.penalty.isPaid
        )
            return;

        state.penalty.isPaid = true;

        // release rental after payment
        state.locker = null;
        state.penalty = null;
        state.rentalState = "NO_RENTAL";
    }

    /**
     * Derived UI state
     */
    const canSettlePenalty = computed(() => {
        return (
            state.rentalState === "EXPIRED_RENTAL" &&
            !!state.penalty &&
            !state.penalty.isPaid
        );
    });

    return {
        applyPenalty,
        settlePenalty,
        canSettlePenalty,
    };
}

/* --------------------
 * Utilities
 * -------------------- */

function calculatePenalty() {
    // mocked flat fee for now
    return 50;
}
