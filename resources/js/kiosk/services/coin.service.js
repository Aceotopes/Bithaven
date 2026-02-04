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
        throw new Error("Coin insert failed");
    }

    return res.json();
}
