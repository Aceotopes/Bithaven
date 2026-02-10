from abc import ABC, abstractmethod

class RelayController(ABC):
    @abstractmethod
    def unlock(self, locker_id: int):
        pass

    @abstractmethod
    def lock(self, locker_id: int):
        pass