import RPi.GPIO as GPIO
from coin.processor import CoinProcessor

COIN_PIN = 20  # change if needed

class GPIOCoinReader:

    def __init__(self, on_coin):
        self.processor = CoinProcessor(on_coin)

        GPIO.setmode(GPIO.BCM)
        GPIO.setup(COIN_PIN, GPIO.IN, pull_up_down=GPIO.PUD_UP)

        # Detect falling edge (coin pulse)
        GPIO.add_event_detect(
            COIN_PIN,
            GPIO.FALLING,
            callback=self._pulse_detected,
            bouncetime=5  # minimal debounce (software handles main logic)
        )

        print(f"[COIN] GPIO initialized on pin {COIN_PIN}")


    def _pulse_detected(self, channel):
        print("[GPIO] Pulse detected")
        self.processor.pulse()

    def cleanup(self):
        GPIO.cleanup()