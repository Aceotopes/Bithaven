import time

class MockRelayController:

    def unlock(self, locker_id: int) -> bool:
        print(f"[RELAY] Energizing locker {locker_id}")

        # simulate real energize duration
        time.sleep(1.2)

        print(f"[RELAY] Locker {locker_id} UNLOCKED")

        return True 

    def lock(self, locker_id: int) -> bool:
        print(f"[RELAY] Locker {locker_id} LOCKED")
        return True
