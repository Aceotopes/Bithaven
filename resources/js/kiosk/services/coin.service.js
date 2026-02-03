import { ref } from "vue";

export function useCoinService() {
    const totalInserted = ref(0);

    function insert(amount) {
        totalInserted.value += amount;
    }

    function reset() {
        totalInserted.value = 0;
    }

    return {
        totalInserted,
        insert,
        reset,
    };
}
