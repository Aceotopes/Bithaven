import { ref, watch, onBeforeUnmount } from "vue";
import { KIOSK_STATES } from "@/kiosk/constants/kioskStates";

export function useIdleTimeout({
    session,
    flow,
    timeoutMs = 60_000, // 60 sec idle time
    warningMs = 10_000, // 10 sec warning time
}) {
    const showWarning = ref(false);
    const countdown = ref(Math.floor(warningMs / 1000));

    let idleTimer = null;
    let countdownTimer = null;

    /* =========================
       Core Actions
    ========================= */

    function resetKiosk() {
        clearAllTimers();
        showWarning.value = false;

        console.log("Kiosk idle timeout reached. Resetting session.");
        session.clearSession();
        flow.goToIdle();
    }

    function clearAllTimers() {
        if (idleTimer) clearTimeout(idleTimer);
        if (countdownTimer) clearInterval(countdownTimer);

        idleTimer = null;
        countdownTimer = null;
    }

    /* =========================
       Idle Timer
    ========================= */

    function startIdleTimer() {
        if (!session.state.student) return;
        clearAllTimers();

        idleTimer = setTimeout(() => {
            startWarningPhase();
        }, timeoutMs - warningMs);
    }

    function handleActivity() {
        if (session.state.kioskState === KIOSK_STATES.IDLE) return;

        showWarning.value = false;
        startIdleTimer();
    }

    /* =========================
       Warning Countdown
    ========================= */
    function startWarningPhase() {
        if (!session.state.student) return;
        if (session.state.kioskState === KIOSK_STATES.IDLE) return;

        showWarning.value = true;
        countdown.value = Math.floor(warningMs / 1000);

        countdownTimer = setInterval(() => {
            countdown.value--;

            if (countdown.value <= 0) {
                resetKiosk();
            }
        }, 1000);
    }

    /* =========================
       Global Listeners
    ========================= */

    window.addEventListener("click", handleActivity);
    window.addEventListener("touchstart", handleActivity);
    window.addEventListener("keydown", handleActivity);

    watch(
        () => session.state.kioskState,
        (state) => {
            clearAllTimers();
            showWarning.value = false;

            if (state !== KIOSK_STATES.IDLE && session.state.student) {
                startIdleTimer();
            }
        },
        { immediate: true }
    );

    onBeforeUnmount(() => {
        clearAllTimers();
        window.removeEventListener("click", handleActivity);
        window.removeEventListener("touchstart", handleActivity);
        window.removeEventListener("keydown", handleActivity);
    });

    return {
        showIdleWarning: showWarning,
        idleCountdown: countdown,
        confirmIdleNow: resetKiosk,
        dismissWarning: handleActivity,
    };
}
