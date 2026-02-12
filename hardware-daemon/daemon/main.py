import time
import threading
from coin.processor import CoinProcessor
from adapters.hardware import get_coin_reader, get_relay_controller

REQUIRED_AMOUNT = 30
LOCKER_ID = 1
UNLOCK_RETRIES = 2
UNLOCK_TIMEOUT = 3
AUTO_LOCK_DELAY = 2  # seconds before re-lock (optional)

current_amount = 0
transaction_active = True
unlock_in_progress = False


def on_coin_inserted(amount):
    global current_amount, transaction_active

    if not transaction_active:
        print("[DAEMON] Transaction completed. Ignoring coin.")
        return

    current_amount += amount

    print(f"[DAEMON] Total inserted: ₱{current_amount}")

    remaining = max(0, REQUIRED_AMOUNT - current_amount)
    print(f"[DAEMON] Remaining: ₱{remaining}\n")

    if current_amount >= REQUIRED_AMOUNT:
        process_unlock()


def process_unlock():
    global transaction_active, unlock_in_progress

    if unlock_in_progress:
        print("[DAEMON] Unlock already in progress. Ignoring duplicate trigger.")
        return

    unlock_in_progress = True

    print("\n[DAEMON] Required amount reached.")
    print("[DAEMON] Attempting unlock...")

    success = attempt_unlock()

    if success:
        print("[DAEMON] Unlock successful.")
        print("[DAEMON] Payment finalized.\n")

        transaction_active = False
        coin_reader.stop()

        # Auto lock after delay
        threading.Thread(target=auto_lock_after_delay, daemon=True).start()

    else:
        print("[DAEMON] Unlock failed after retries.")
        print("[DAEMON] Payment not finalized.\n")

        unlock_in_progress = False  # allow retry


def attempt_unlock():
    for attempt in range(UNLOCK_RETRIES + 1):
        print(f"[DAEMON] Unlock attempt {attempt + 1}")

        success = safe_unlock(LOCKER_ID, timeout=UNLOCK_TIMEOUT)

        if success:
            return True

        print("[DAEMON] Unlock attempt failed")

    return False


def safe_unlock(locker_id, timeout):
    result = [False]

    def target():
        result[0] = relay.unlock(locker_id)

    t = threading.Thread(target=target)
    t.start()
    t.join(timeout)

    if t.is_alive():
        print("[DAEMON] Unlock timeout — hardware not responding")
        return False

    return result[0]


def auto_lock_after_delay():
    print(f"[DAEMON] Auto-locking in {AUTO_LOCK_DELAY} seconds...")
    time.sleep(AUTO_LOCK_DELAY)
    relay.lock(LOCKER_ID)
    print("[DAEMON] Locker re-locked.")


def main():
    global relay, coin_reader

    processor = CoinProcessor(on_coin_inserted)
    coin_reader = get_coin_reader(processor.pulse)
    relay = get_relay_controller()

    coin_reader.start()

    print("[DAEMON] Running (coins hardened + unlock hardened)\n")

    try:
        while True:
            time.sleep(0.1)
    except KeyboardInterrupt:
        print("[DAEMON] Shutting down...")
        coin_reader.stop()


if __name__ == "__main__":
    main()
