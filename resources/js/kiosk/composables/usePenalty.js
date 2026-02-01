import { ref, computed, onMounted } from "vue";

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

/* ------------------------------------
 * Penalty Logic (FRONTEND MOCK ONLY)
 * ------------------------------------ */
// export function usePenalty(state) {
//     const now = ref(Date.now());
//     let timer = null;

//     function startPenaltyClock() {
//         if (timer) return;

//         timer = setInterval(() => {
//             now.value = Date.now();
//         }, 1000); // 1 second resolution
//     }

//     function stopPenaltyClock() {
//         if (timer) {
//             clearInterval(timer);
//             timer = null;
//         }
//     }
//     /**
//      * Apply penalty when rental expires.
//      * Called ONCE at expiry.
//      */
//     function applyPenalty() {
//         if (state.penalty) return;

//         state.penalty = {
//             startedAt: Date.now(), // when penalty started
//             isPaid: false,
//         };
//         startPenaltyClock();
//     }

//     /**
//      * Settle the active penalty.
//      */
//     function settlePenalty() {
//         if (
//             state.rentalState !== "EXPIRED_RENTAL" ||
//             !state.penalty ||
//             state.penalty.isPaid
//         )
//             return;
//         stopPenaltyClock();

//         state.penalty.isPaid = true;

//         // Clear rental
//         state.locker = null;
//         state.penalty = null;
//         state.rentalState = "NO_RENTAL";
//     }

//     /**
//      * Derived penalty amount (REAL TIME)
//      */
//     const penaltyAmount = computed(() => {
//         if (
//             !state.penalty ||
//             !state.locker ||
//             typeof state.locker.endTime !== "number"
//         ) {
//             return 0;
//         }

//         const exceededMs = now.value - state.locker.endTime;

//         if (exceededMs <= 0) return 0;

//         return calculatePenalty(exceededMs);
//     });

//     /**
//      * UI permission
//      */
//     const canSettlePenalty = computed(() => {
//         return (
//             state.rentalState === "EXPIRED_RENTAL" &&
//             !!state.penalty &&
//             !state.penalty.isPaid
//         );
//     });

//     function getPenaltySnapshot() {
//         if (!state.penalty || !state.locker) return null;

//         // Freeze exceeded time ONCE
//         const exceededMs = Date.now() - state.locker.endTime;

//         const amount = calculatePenalty(exceededMs);

//         return {
//             amount,
//             exceededDuration: formatDuration(exceededMs),
//             breakdown: buildPenaltyBreakdown(exceededMs),
//             frozenAt: Date.now(),
//         };
//     }
//     return {
//         applyPenalty,
//         settlePenalty,
//         canSettlePenalty,
//         penaltyAmount,
//         getPenaltySnapshot,
//     };
// }

export function usePenalty(state) {
    /**
     * Read-only: current penalty amount
     * Comes from backend via hydration
     */
    const penaltyAmount = computed(() => {
        return state.penalty?.amount ?? 0;
    });

    /**
     * UI permission
     */
    const canSettlePenalty = computed(() => {
        return (
            state.rentalState === "EXPIRED_RENTAL" &&
            !!state.penalty &&
            state.penalty.status === "ACTIVE"
        );
    });

    /**
     * Clear penalty AFTER backend confirms payment
     */
    function clearPenalty() {
        state.penalty = null;
        state.locker = null;
        state.rentalState = "NO_RENTAL";
    }

    return {
        penaltyAmount,
        canSettlePenalty,
        clearPenalty,
    };
}

/* --------------------
 * Utilities (FRONTEND MOCK ONLY)
 * -------------------- */
// function calculatePenalty(exceededMs) {
//     const exceededMinutes = Math.floor(exceededMs / 60000);

//     let penalty = 5; // base penalty on expiry

//     // every additional 30 minutes → +5
//     const halfHours = Math.floor(exceededMinutes / 1);
//     penalty += halfHours * 5;

//     // every full hour → +10
//     const fullHours = Math.floor(exceededMinutes / 60);
//     penalty += fullHours * 10;

//     return penalty;
// }

// function buildPenaltyBreakdown(ms) {
//     const minutes = Math.floor(ms / 60000);
//     const breakdown = [];

//     breakdown.push({ label: "Initial expiry", amount: 5 });

//     const halfHours = Math.floor(minutes / 30);
//     for (let i = 1; i <= halfHours; i++) {
//         breakdown.push({ label: `+30 mins`, amount: 5 });
//     }

//     const hours = Math.floor(minutes / 60);
//     for (let i = 1; i <= hours; i++) {
//         breakdown.push({ label: `+1 hour`, amount: 10 });
//     }

//     return breakdown;
// }
// function formatDuration(ms) {
//     const totalSeconds = Math.max(0, Math.floor(ms / 1000));

//     const hours = Math.floor(totalSeconds / 3600);
//     const minutes = Math.floor((totalSeconds % 3600) / 60);
//     const seconds = totalSeconds % 60;

//     if (hours > 0) {
//         return `${hours}h ${minutes}m`;
//     }

//     if (minutes > 0) {
//         return `${minutes}m ${seconds}s`;
//     }

//     return `${seconds}s`;
// }
