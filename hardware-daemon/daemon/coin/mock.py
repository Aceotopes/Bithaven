import time
import threading
import random


class MockCoinReader:
    def __init__(self, on_pulse):
        self.on_pulse = on_pulse
        self.running = False

    def start(self):
        self.running = True
        threading.Thread(target=self._simulate, daemon=True).start()

    def stop(self):
        self.running = False

    # MODE A - Normal Realistic Behavior Test
    def _simulate(self):
        while self.running:

            # random delay between coin insertions
            time.sleep(random.uniform(1, 3))

            coin = random.choice([1, 5, 10])
            print(f"[MOCK COIN] inserting ₱{coin}")

            # emit pulses equal to coin value
            for _ in range(coin):
                self.on_pulse()

                # simulate realistic pulse spacing
                time.sleep(random.uniform(0.05, 0.12))


    # MODE B - Noise Test (random pulses without coin insertions)
    def _simulate_noise(self):
        while self.running:
            time.sleep(random.uniform(0.5, 2))

            print("\n[MOCK NOISE] random pulse")
            self.on_pulse()


    # MODE C - Stress Test (valid coins + noise + rapid bursts)
    def _simulate_stress(self):
        while self.running:
            time.sleep(0.5)

        # random event
            event_type = random.choice(["valid", "noise", "burst"])

            if event_type == "valid":
                coin = random.choice([1, 5, 10])
                print(f"\n[STRESS] Valid ₱{coin}")

                for _ in range(coin):
                    self.on_pulse()
                    time.sleep(random.uniform(0.04, 0.09))

            elif event_type == "noise":
                print("\n[STRESS] Noise spike")
                for _ in range(random.randint(1, 3)):
                    self.on_pulse()
                    time.sleep(random.uniform(0.001, 0.01))

            elif event_type == "burst":
                print("\n[STRESS] Rapid burst insert")
                for _ in range(random.randint(5, 12)):
                    self.on_pulse()
                    time.sleep(random.uniform(0.02, 0.05))
