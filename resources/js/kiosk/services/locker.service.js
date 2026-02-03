export function useLockerService() {
    async function unlock(lockerNumber) {
        console.log(`[HARDWARE] Unlock locker ${lockerNumber}`);
        // later: GPIO / relay ON
    }

    async function lock(lockerNumber) {
        console.log(`[HARDWARE] Lock locker ${lockerNumber}`);
        // later: GPIO / relay OFF
    }

    return {
        unlock,
        lock,
    };
}
