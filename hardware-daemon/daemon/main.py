import time
import requests
import datetime
from adapters.hardware import get_relay_controller

API_BASE = "http://127.0.0.1:8000/api/kiosk"
POLL_INTERVAL = 1

relay = get_relay_controller()

KIOSK_ID = "KIOSK-01"
HEARTBEAT_INTERVAL = 10
last_heartbeat = 0

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
        print(r.status_code, r.text)
    except Exception as e:
        print("[DAEMON] Failed to mark succeeded:", e)


def mark_job_failed(job_id):
    try:
        r = requests.post(
            f"{API_BASE}/unlock-jobs/{job_id}/status",
            json={"status": "FAILED"},
            timeout=3
        )
        r.raise_for_status()
    except Exception as e:
        print("[DAEMON] Failed to mark failed:", e)


# -------------------------------
# PROCESS JOB
# -------------------------------
def process_job(job):
    job_id = job["id"]
    locker_id = job["locker_id"]

    if not mark_job_processing(job_id):
        print(f"[DAEMON] Failed to mark job {job_id} as processing - skipping")
        return

    print(f"[DAEMON] Processing job {job_id} for locker {locker_id}")

    mark_job_processing(job_id)

    success = relay.unlock(locker_id)
    # success = False
    print(f"[DAEMON] unlock() returned: {success}")

    if success:
        print("[DAEMON] Unlock success - marking succeeded")
        mark_job_succeeded(job_id)
    else:
        print("[DAEMON] Unlock failed - marking failed")
        mark_job_failed(job_id)


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
# MAIN LOOP
# -------------------------------
def main():
    global last_heartbeat

    print("[DAEMON] Running — retry queue mode")

    while True:
        now = time.time()

        # Heartbeat
        if now - last_heartbeat > HEARTBEAT_INTERVAL:
            send_heartbeat()
            last_heartbeat = now

        # Fetch unlock jobs
        jobs = fetch_pending_jobs()

        for job in jobs:
            if is_expired(job):
                print(f"[DAEMON] Skipping expired job {job['id']}")
                continue
            process_job(job)

        time.sleep(POLL_INTERVAL)


if __name__ == "__main__":
    main()