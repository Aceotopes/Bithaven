# Hardware Daemon

This directory contains the hardware daemon responsible for:

-   Coin slot pulse processing
-   Relay / solenoid control
-   Hardware safety & debouncing
-   Offline buffering and retries

## Responsibilities

This daemon:

-   Interfaces with physical hardware (GPIO on Raspberry Pi)
-   Sends validated events to the Laravel backend via HTTP
-   Does NOT contain business logic (pricing, penalties, etc.)

## Development

The daemon can be run locally without hardware using mock adapters.

## Deployment

On production, this daemon runs on a Raspberry Pi as a system service
and communicates with the backend over the network.
