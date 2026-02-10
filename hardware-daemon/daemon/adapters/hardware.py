from coin.mock import MockCoinReader
from relay.mock import MockRelayController

def get_coin_reader(on_coin):
    return MockCoinReader(on_coin)

def get_relay_controller():
    return MockRelayController()