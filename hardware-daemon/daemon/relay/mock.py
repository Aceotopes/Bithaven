class MockRelayController:
    def unlock(self, locker_id: int):
        print(f"[MOCK RELAY] Unlock locker {locker_id}")

    def lock(self, locker_id: int):
        print(f"[MOCK RELAY] Lock locker {locker_id}")