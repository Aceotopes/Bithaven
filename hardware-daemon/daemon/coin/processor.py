import time
import threading


# ==========================================
# HARDWARE TIMINGS
# ==========================================
PULSE_DEBOUNCE_MS = 30        # Ignore pulses closer than this
COIN_TIMEOUT_MS = 250        # Silence window to finalize coin
MAX_PULSES = 15              # Safety cutoff to prevent runaway pulses

# Pulse-to-amount mapping
PULSE_VALUE_MAP = {
    1: 1,
    5: 5,
    10: 10,
}


class CoinProcessor:
    def __init__(self, on_coin):
        self.on_coin = on_coin
        self.pulses = 0
        self.last_pulse_time = None
        self.lock = threading.Lock()
        self.timer = None

    # ==========================================
    #        Called on every raw pulse
    # ==========================================
    def pulse(self):
        now = time.time()

        with self.lock:

            # Debounce protection
            if self.last_pulse_time:
                delta_ms = (now - self.last_pulse_time) * 1000

                print(f"[PROCESSOR] Pulse Δ = {delta_ms:.2f} ms")

                if delta_ms < PULSE_DEBOUNCE_MS:
                    print("[PROCESSOR] Debounced")
                    return

            self.last_pulse_time = now
            self.pulses += 1

            print(f"[PROCESSOR] Pulse count = {self.pulses}")

            # Safety cutoff
            if self.pulses > MAX_PULSES:
                print("[PROCESSOR] MAX_PULSES exceeded — resetting")
                self._reset()
                return

            # Reset finalize timer
            if self.timer:
                self.timer.cancel()

            self.timer = threading.Timer(
                COIN_TIMEOUT_MS / 1000,
                self._finalize
            )
            self.timer.start()

    # ==========================================
    #       Finalize coin after silence
    # ==========================================
    def _finalize(self):
        with self.lock:
            pulses = self.pulses
            self._reset()

        print(f"[PROCESSOR] Finalizing with {pulses} pulses")

        amount = self._resolve_amount(pulses)

        if amount:
            print(f"[PROCESSOR] RESOLVED ₱{amount}")
            self.on_coin(amount)
        else:
            print(f"[PROCESSOR] Invalid pulse count: {pulses}")

    # ==========================================
    #       Resolve pulses → coin value
    # ==========================================
    def _resolve_amount(self, pulses):
        return PULSE_VALUE_MAP.get(pulses)

    # ==========================================
    #           Reset state
    # ==========================================
    def _reset(self):
        self.pulses = 0
        self.last_pulse_time = None

        if self.timer:
            self.timer.cancel()
            self.timer = None
