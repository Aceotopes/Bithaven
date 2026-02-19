export async function insertCoin(kioskId, value) {
    const res = await fetch("/api/kiosk/coins/insert", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            kiosk_id: kioskId,
            value,
        }),
    });

    if (!res.ok) {
        const err = await res.json();

        if (err.error === "LOCKER_OUT_OF_SERVICE") {
            console.warn(`🚫 Locker ${err.locker_id} is out of service.`);
            return;
        }

        console.error("Unexpected error:", err);
        throw new Error("Coin insert failed");
    }

    return res.json();
}
