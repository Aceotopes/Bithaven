import time
import threading
import random

class MockCoinReader:
    def __init__(self, on_coin):
        self.on_coin = on_coin
        self.running = False

    def start(self):
        self.running = True
        threading.Thread(target=self._simulate, daemon=True).start()

    def stop(self):
        self.running = False

    def _simulate(self):
        while self.running:
            time.sleep(random.uniform(1, 2))

            # simulate coin values
            coin = random.choice([1, 5, 10])
            print(f"[MOCK COIN] Inserted ₱{coin}")

            # callback to daemon
            self.on_coin(coin)