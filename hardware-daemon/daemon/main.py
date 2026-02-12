import time
import threading
from coin.processor import CoinProcessor
from adapters.hardware import get_coin_reader, get_relay_controller

REQUIRED_AMOUNT = 30
LOCKER_ID = 1

current_amount = 0
transaction_active = True 


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
    global current_amount, transaction_active, coin_reader

    print("\n[DAEMON] Required amount reached.")
    print("[DAEMON] Unlocking locker...")

    success = safe_unlock(LOCKER_ID)

    if success:
        print("[DAEMON] Unlock successful.")
        print("[DAEMON] Payment finalized.\n")

        transaction_active = False  # stop transaction
        coin_reader.stop()          # stop accepting coins

    else:
        print("[DAEMON] Unlock failed.")
        print("[DAEMON] Payment not finalized.")


def safe_unlock(locker_id, timeout=3):
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


def main():
    global relay, coin_reader

    processor = CoinProcessor(on_coin_inserted)
    coin_reader = get_coin_reader(processor.pulse)
    relay = get_relay_controller()

    coin_reader.start()

    print("[DAEMON] Running (mock + hardened coins + guaranteed unlock)\n")

    try:
        while True:
            time.sleep(0.1)
    except KeyboardInterrupt:
        print("[DAEMON] Shutting down...")
        coin_reader.stop()


if __name__ == "__main__":
    main()
