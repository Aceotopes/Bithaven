# import RPi.GPIO as GPIO
# from coin.processor import CoinProcessor

# COIN_PIN = 23 # change if needed

# class GPIOCoinReader:

#     def __init__(self, on_coin):
#         self.processor = CoinProcessor(on_coin)

#         GPIO.setmode(GPIO.BCM)
#         GPIO.setup(COIN_PIN, GPIO.IN, pull_up_down=GPIO.PUD_UP)

#         # Detect falling edge (coin pulse)
#         GPIO.add_event_detect(
#             COIN_PIN,
#             GPIO.FALLING,
#             callback=self._pulse_detected,
#             bouncetime=5  # minimal debounce (software handles main logic)
#         )

#         print(f"[COIN] GPIO initialized on pin {COIN_PIN}")


#     def _pulse_detected(self, channel):
#         print("[GPIO] Pulse detected")
#         self.processor.pulse()

#     def cleanup(self):
#         GPIO.cleanup()


from gpiozero import Button
from coin.processor import CoinProcessor

COIN_PIN = 23  # change if needed


class GPIOCoinReader:

    def __init__(self, on_coin):
        self.processor = CoinProcessor(on_coin)

        # Coin acceptor behaves like a button (active LOW pulse)
        self.coin = Button(
            COIN_PIN,
            pull_up=True,
            bounce_time=0.005  # 5ms debounce
        )

        # Trigger on falling edge (LOW pulse)
        self.coin.when_pressed = self._pulse_detected

        print(f"[COIN] GPIO initialized on pin {COIN_PIN}")

    def _pulse_detected(self):
        print("[GPIO] Pulse detected")
        self.processor.pulse()

    def cleanup(self):
        if hasattr(self, "coin"):
            self.coin.close()