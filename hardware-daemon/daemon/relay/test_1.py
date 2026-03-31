import RPi.GPIO as GPIO
import time


# temporary mapping of locker IDs to GPIO pins
LOCKER_PIN_MAP = {
    1: 9,
    2: 10,
    3: 11,
    4: 12,
    5: 13,
    6: 14,
    7: 15,
    8: 16,
    9: 8,
    10: 7,
    11: 6,
    12: 5,
    13: 4,
    14: 3,
    15: 2,
}


class RealRelayController:
    def __init__(self):
        GPIO.setmode(GPIO.BCM)
        GPIO.setwarnings(False)

        for pin in LOCKER_PIN_MAP.values():
            GPIO.setup(pin, GPIO.OUT)
            GPIO.output(pin, GPIO.HIGH)  # OFF

        print("[RELAY] Real controller initialized")

    def unlock(self, locker_id):
        pin = LOCKER_PIN_MAP.get(locker_id)

        if pin is None:
            print(f"[RELAY] Invalid locker: {locker_id}")
            return False

        try:
            print(f"[RELAY] Unlocking locker {locker_id} (GPIO {pin})")

            GPIO.output(pin, GPIO.LOW)   # ON
            time.sleep(0.5)
            GPIO.output(pin, GPIO.HIGH)  # OFF

            return True

        except Exception as e:
            print("[RELAY] Error:", e)
            return False