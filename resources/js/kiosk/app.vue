<script setup>
import { ref } from "vue";
import { KIOSK_STATES } from "./constants/kioskStates";

import IdleScreen from "./screens/IdleScreen.vue";
import SelectLockerScreen from "./screens/LockerSelectScreen.vue";

const currentState = ref(KIOSK_STATES.IDLE);
</script>

<template>
    <IdleScreen v-if="currentState === KIOSK_STATES.IDLE" />
    <SelectLockerScreen v-if="currentState === KIOSK_STATES.SELECT_LOCKER" />

    <!-- temporary debug control -->
    <button
        class="fixed bottom-4 right-4 bg-black text-white px-4 py-2 rounded"
        @click="currentState = KIOSK_STATES.SELECT_LOCKER"
    >
        next
    </button>
    <button
        class="fixed bottom-4 left-4 bg-black text-white px-4 py-2 rounded"
        @click="currentState = KIOSK_STATES.IDLE"
    >
        back
    </button>
</template>
