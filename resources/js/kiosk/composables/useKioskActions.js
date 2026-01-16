import { computed } from "vue";

/**
 * useKioskActions
 * ----------------
 * Exposes derived UI permissions based on kiosk state.
 *
 * This composable:
 * - contains NO business logic
 * - never mutates state
 * - is safe for direct UI consumption
 */
export function useKioskActions(state) {
    /**
     * Can rent a locker
     * - session must exist
     * - no active or expired rental
     */
    const canRent = computed(() => {
        return !!state.student && state.rentalState === "NO_RENTAL";
    });

    /**
     * Can end an active rental
     */
    const canEndRental = computed(() => {
        return state.rentalState === "ACTIVE_RENTAL";
    });

    /**
     * Can settle an expired rental penalty
     */
    const canSettlePenalty = computed(() => {
        return (
            state.rentalState === "EXPIRED_RENTAL" &&
            !!state.penalty &&
            !state.penalty.isPaid
        );
    });

    /**
     * Can end the entire session
     * (optional, but often useful)
     */
    const canEndSession = computed(() => {
        return !!state.student;
    });

    return {
        canRent,
        canEndRental,
        canSettlePenalty,
        canEndSession,
    };
}
