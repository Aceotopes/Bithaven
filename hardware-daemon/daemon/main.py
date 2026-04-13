import time
import requests
import datetime
import threading

from adapters.hardware import get_relay_controller
from adapters.hardware import get_coin_reader

import signal
import sys

API_BASE = "http://127.0.0.1/api/kiosk"
idle_interval = 1     # when no jobs
max_idle_interval = 3
# active_interval = 1   # when processing jobs
current_interval = idle_interval

relay = get_relay_controller()

KIOSK_ID = "KIOSK-01"
HEARTBEAT_INTERVAL = 10
last_heartbeat = 0

coin_reader = None

def shutdown(sig, frame):
    print("[SYSTEM] Shutting down...")
    relay.cleanup()
    
    if coin_reader:
        coin_reader.cleanup()
    
    import RPi.GPIO as GPIO
    GPIO.cleanup()

    sys.exit(0)

# -------------------------------
# JOB FETCHING
# -------------------------------
def fetch_pending_jobs():
    try:
        r = requests.get(
            f"{API_BASE}/unlock-jobs/pending",
            timeout=3
        )
        r.raise_for_status()
        return r.json().get("jobs", [])
    except Exception as e:
        print("[DAEMON] Failed to fetch jobs:", e)
        return []
    
# def fetch_pending_jobs():
#     try:
#         r = requests.get(f"{API_BASE}/unlock-jobs/pending", timeout=3)
#         r.raise_for_status()

#         data = r.json()
#         # print("[DAEMON] RAW RESPONSE:", data)
#         # handle single job
#         job = data.get("job")

#         if job:
#             return [job]  # wrap in list

#         return []

#     except Exception as e:
#         print("[DAEMON] Failed to fetch jobs:", e)
#         return []
    


def mark_job_processing(job_id):
    try:
        r = requests.post(f"{API_BASE}/unlock-jobs/{job_id}/processing")
        if r.status_code == 409:
            print(f"[DAEMON] job {job_id} processing")
            return True
        r.raise_for_status()
        return True
    except Exception as e:
        print("[DAEMON] Failed to mark processing:", e)
        return False


def mark_job_succeeded(job_id):
    try:
        r = requests.post(
            f"{API_BASE}/unlock-jobs/{job_id}/status",
            json={"status": "SUCCEEDED"},
            timeout=3
        )
        r.raise_for_status()
        print(f"[DAEMON] Job {job_id} marked SUCCEEDED")
        return True
    except Exception as e:
        print("[DAEMON] Failed to mark succeeded:", e)
        return False


def mark_job_failed(job_id):
    try:
        r = requests.post(
            f"{API_BASE}/unlock-jobs/{job_id}/status",
            json={"status": "FAILED"},
            timeout=3
        )
        r.raise_for_status()
        print(f"[DAEMON] Job {job_id} marked FAILED")
        return True
    except Exception as e:
        print("[DAEMON] Failed to mark failed:", e)
        return False


# -------------------------------
# PROCESS JOB
# -------------------------------
def process_job(job):
    job_id = job["id"]
    locker_id = job["locker_id"]
    attempts = job.get("attempts", 0)

    #RETRY DELAY
    delay = min(2 ** attempts, 10)  # 1s, 2s, 4s (max 10s)
    if attempts > 0:
        print(f"[DAEMON] Retry #{attempts} for job {job_id} after {delay}s")
        time.sleep(delay)

    if job["status"] != "PENDING":
        print(f"[DAEMON] Skipping job {job['id']} with status {job['status']}")
        return

    if not mark_job_processing(job_id):
        print(f"[DAEMON] Failed to mark job {job_id} as processing - skipping")
        return

    print(f"[DAEMON] Processing job {job_id} (attempt {attempts + 1}) for locker {locker_id}")

    try:
        success = unlock_with_timeout(locker_id)
        # success = False
        print(f"[DAEMON] unlock() returned: {success}")

        if success:
            if not mark_job_succeeded(job_id):
                print(f"[DAEMON] CRITICAL: Job {job_id} succeeded but backend update failed")
        else:
            if not mark_job_failed(job_id):
                print(f"[DAEMON] CRITICAL: Job {job_id} failed but backend update failed")

    except Exception as e:
        print(f"[DAEMON] Exception during unlock: {e}")
        mark_job_failed(job_id)


# -------------------------------
# UNLOCK WITH TIMEOUT 
# -------------------------------
def unlock_with_timeout(locker_id, timeout=3):
    result = {"success": False}

    def target():
        try:
            result["success"] = relay.unlock(locker_id)
        except Exception as e:
            print(f"[DAEMON] Unlock exception: {e}")

    thread = threading.Thread(target=target)
    thread.start()
    thread.join(timeout)

    if thread.is_alive():
        print("[DAEMON] Unlock timeout!")
        return False

    return result["success"]


# -------------------------------
# HEARTBEAT
# -------------------------------
def send_heartbeat():
    try:
        requests.post(
            f"{API_BASE}/daemon/heartbeat",
            json={"kiosk_id": KIOSK_ID},
            timeout=2
        )
        print("[DAEMON] Heartbeat sent")
    except Exception as e:
        print("[DAEMON] Heartbeat failed:", e)


# -------------------------------
# EXPIRED TOKEN CHECK
# -------------------------------
def is_expired(job):
    if not job.get("token") or not job["token"].get("expires_at"):
        return False

    expires = datetime.datetime.fromisoformat(job["token"]["expires_at"])
    return expires <= datetime.datetime.now(datetime.timezone.utc)


# -------------------------------
# COIN INSERTION CALLBACK
# -------------------------------
def on_coin(amount):
    try:
        requests.post(
            f"{API_BASE}/coins/insert",
            json={
                "kiosk_id": KIOSK_ID,
                "value": amount
            },
            timeout=2
        )
        print(f"[COIN] ₱{amount} sent")
    except Exception as e:
        print("[COIN] Failed:", e)

# -------------------------------
# MAIN LOOP
# -------------------------------
def main():
    global last_heartbeat
    global coin_reader

    current_interval = idle_interval
    
    signal.signal(signal.SIGINT, shutdown)
    signal.signal(signal.SIGTERM, shutdown)

    coin_reader = get_coin_reader(on_coin)
    print("[SYSTEM] Coin reader initialized")

    print("[DAEMON] Running — retry queue mode")

    # while True:
    #     now = time.time()

    #     # Heartbeat
    #     if now - last_heartbeat > HEARTBEAT_INTERVAL:
    #         send_heartbeat()
    #         last_heartbeat = now

    #     # Fetch unlock jobs
    #     jobs = fetch_pending_jobs()

    #     if jobs:
    #         print(f"[DAEMON] {len(jobs)} job(s) found")
    #         current_interval = active_interval
    #     else:
    #         current_interval = idle_interval

    #     for job in jobs:
    #         if is_expired(job):
    #             print(f"[DAEMON] Skipping expired job {job['id']}")
    #             continue
    #         process_job(job)

    #     time.sleep(current_interval)

    while True:
        now = time.time()

        # -----------------------
        # HEARTBEAT
        # -----------------------
        if now - last_heartbeat > HEARTBEAT_INTERVAL:
            send_heartbeat()
            last_heartbeat = now

        # -----------------------
        # FETCH JOBS
        # -----------------------
        jobs = fetch_pending_jobs()

        if jobs:
            print(f"[DAEMON] {len(jobs)} job(s) found")

            # reset to fast polling
            current_interval = idle_interval

            for job in jobs:
                if is_expired(job):
                    print(f"[DAEMON] Skipping expired job {job['id']}")
                    continue

                process_job(job)

            # 🔥 IMPORTANT: do NOT sleep after processing
            time.sleep(1) #original 0.3
            continue

        # -----------------------
        # NO JOBS → BACKOFF
        # -----------------------
        current_interval = min(current_interval * 1.5, max_idle_interval)

        time.sleep(current_interval)


if __name__ == "__main__":
    main()