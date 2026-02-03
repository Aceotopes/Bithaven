import { ref } from "vue";

export function useRFIDService() {
    const buffer = ref("");
    let enabled = false;

    function onKeydown(e, onScan) {
        if (!enabled) return;

        if (/^[0-9]$/.test(e.key)) {
            buffer.value += e.key;

            // AUTO submit when UID length reached
            if (buffer.value.length === 10) {
                onScan(buffer.value);
                buffer.value = "";
            }
        }
    }

    function enable(onScan) {
        if (enabled) return;
        enabled = true;

        window.addEventListener("keydown", (e) => onKeydown(e, onScan));
    }

    function disable() {
        enabled = false;
        buffer.value = "";
    }

    return { enable, disable };
}
