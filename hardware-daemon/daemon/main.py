import time
from coin.processor import CoinProcessor
from adapters.hardware import get_coin_reader, get_relay_controller

REQUIRED_AMOUNT = 10  # simulate backend amount_due

current_amount = 0
payment_complete = False


def on_coin_inserted(amount):
    global current_amount, payment_complete

    if payment_complete:
        print("[DAEMON] Payment already completed. Ignoring coin.")
        return

    current_amount += amount
    remaining = REQUIRED_AMOUNT - current_amount

    print(f"[DAEMON] Coin accepted: ₱{amount}")
    print(f"[DAEMON] Total inserted: ₱{current_amount}")
    print(f"[DAEMON] Remaining: ₱{max(remaining, 0)}\n")

    if current_amount >= REQUIRED_AMOUNT:
        payment_complete = True
        print("[DAEMON] Payment complete!")
        relay.unlock(1)  # simulate unlock locker 1


def main():
    global relay

    processor = CoinProcessor(on_coin_inserted)
    coin_reader = get_coin_reader(processor.pulse)
    relay = get_relay_controller()

    coin_reader.start()

    print("[DAEMON] Running (mock + hardened)")

    try:
        while True:
            time.sleep(0.1)
    except KeyboardInterrupt:
        print("[DAEMON] Shutting down...")
        coin_reader.stop()


if __name__ == "__main__":
    main()
