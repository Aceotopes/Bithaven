/**
 * useTimer
 * --------
 * Generic countdown timer utility.
 * Knows nothing about rentals or UI.
 */
export function useTimer() {
    let timerId = null;

    function start(endTime, onTick, onExpire) {
        stop();

        timerId = setInterval(() => {
            const remaining = endTime - Date.now();

            if (remaining <= 0) {
                stop();
                onExpire();
            } else {
                onTick(remaining);
            }
        }, 1000);
    }

    function stop() {
        if (timerId !== null) {
            clearInterval(timerId);
            timerId = null;
        }
    }

    return {
        start,
        stop,
    };
}
