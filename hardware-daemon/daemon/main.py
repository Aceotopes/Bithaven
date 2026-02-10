from adapters.hardware import get_coin_reader, get_relay_controller

def on_coin_inserted(amount):
    print(f"[DAEMON] Coin received: ₱{amount}")
    # later: POST to Laravel /coins/insert

def main():
    coin_reader = get_coin_reader(on_coin_inserted)
    relay = get_relay_controller()

    coin_reader.start()

    print("[DAEMON] Hardware daemon running (mock mode)")
    input("Press ENTER to exit\n")

    coin_reader.stop()

if __name__ == "__main__":
    main()
