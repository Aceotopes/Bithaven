import time
import requests
from adapters.hardware import get_relay_controller

API_BASE = "http://127.0.0.1:8000/api/kiosk"
POLL_INTERVAL = 1

relay = get_relay_controller()


def fetch_pending_tokens():
    try:
        r = requests.get(f"{API_BASE}/unlock-tokens/pending")
        print(f"[DAEMON] Fetching: {API_BASE}/unlock-tokens/pending")
        r.raise_for_status()
        return r.json().get("tokens", [])
    except Exception as e:
        print("[DAEMON] Failed to fetch tokens:", e)
        return []


def confirm_token(token_id):
    try:
        requests.post(f"{API_BASE}/unlock-tokens/{token_id}/confirm")
    except Exception as e:
        print("[DAEMON] Failed to confirm token:", e)


def process_token(token):
    locker_id = token["locker_id"]
    token_id = token["id"]

    print(f"[DAEMON] Unlocking locker {locker_id}")

    success = relay.unlock(locker_id)

    if success:
        print("[DAEMON] Unlock success")
        confirm_token(token_id)
    else:
        print("[DAEMON] Unlock failed")


def main():
    print("[DAEMON] Running — backend-authoritative mode")

    while True:
        tokens = fetch_pending_tokens()

        for token in tokens:
            process_token(token)

        time.sleep(POLL_INTERVAL)


if __name__ == "__main__":
    main()
