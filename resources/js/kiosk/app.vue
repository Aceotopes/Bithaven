<script setup>
import { ref } from "vue";
import { KIOSK_STATES } from "./constants/kioskStates";

import IdleScreen from "./screens/IdleScreen.vue";
import MainScreen from "./screens/MainScreen.vue";
// import LockerSelectScreen from './screens/LockerSelectScreen.vue' //                             temporarily commented out for testing

const currentState = ref(KIOSK_STATES.IDLE);

function goToMain() {
    currentState.value = KIOSK_STATES.MAIN;
}
</script>

<template>
    <transition name="fade" mode="out-in">
        <IdleScreen
            v-if="currentState === KIOSK_STATES.IDLE"
            @next="goToMain"
        />
        <!-- <SelectLockerScreen v-if="currentState === KIOSK_STATES.SELECT_LOCKER" />                  temporarily commented out for testing -->

        <MainScreen v-else-if="currentState === KIOSK_STATES.MAIN" />

        <!-- temporary debug control                                                                    temporarily commented out for testing -->
        <!-- <button
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
    </button> -->
    </transition>
</template>

<style>
/* transition animation from 1 screen to another */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
