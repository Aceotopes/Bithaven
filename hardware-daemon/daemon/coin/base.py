from abc import ABC, abstractmethod

class CoinReader(ABC):
    @abstractmethod
    def start(self):
        pass

    @abstractmethod
    def stop(self):
        pass