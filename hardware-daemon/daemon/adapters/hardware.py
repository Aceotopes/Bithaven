from coin.mock import MockCoinReader
from relay.mock import MockRelayController
# from coin.test_1 import GPIOCoinReader
# from relay.test_1 import GPIORelayController

def get_coin_reader(on_coin):
    return MockCoinReader(on_coin)

def get_relay_controller():
    return MockRelayController()

# def get_coin_reader(on_coin):
#     return GPIOCoinReader(on_coin)

# def get_relay_controller():
#     return GPIORelayController()