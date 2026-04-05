import RPi.GPIO as GPIO
import time

# BCM mapping
LOCKER_PIN_MAP = {
    1: 23,
    2: 22,
    3: 27,
    4: 21,
    5: 19,
    6: 17,
    7: 18,
    8: 25,
    9: 5,
    10: 24,
    11: 4,
    12: 6,
    13: 12,
    14: 13,
    15: 16,
}

PULSE_DURATION = 0.5  # seconds (adjust based on your lock)

class GPIORelayController:

    def __init__(self):
        GPIO.setmode(GPIO.BCM)
        GPIO.setwarnings(False)

        # Initialize all pins
        for pin in LOCKER_PIN_MAP.values():
            GPIO.setup(pin, GPIO.OUT)
            GPIO.output(pin, GPIO.HIGH)  # default OFF (for active LOW relay)

        print("[RELAY] GPIO initialized")

    def unlock(self, locker_id: int) -> bool:
        if locker_id not in LOCKER_PIN_MAP:
            print(f"[RELAY] Invalid locker_id: {locker_id}")
            return False

        pin = LOCKER_PIN_MAP[locker_id]

        try:
            print(f"[RELAY] Triggering locker {locker_id} on GPIO {pin}")

            # ACTIVE LOW (most relay modules)
            GPIO.output(pin, GPIO.LOW)
            time.sleep(PULSE_DURATION)
            GPIO.output(pin, GPIO.HIGH)
            time.sleep(0.2)  # short delay to ensure relay resets

            print(f"[RELAY] Locker {locker_id} pulse complete")
            return True

        except Exception as e:
            print(f"[RELAY] Error unlocking locker {locker_id}: {e}")
            return False

    def cleanup(self):
        GPIO.cleanup()