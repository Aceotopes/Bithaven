<script setup>
defineProps({
    show: Boolean,
    secondsLeft: Number,
});

const emit = defineEmits(["confirm", "continue"]);
</script>

<template>
    <transition name="modal-fade">
        <div
            v-if="show"
            class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center"
        >
            <!-- MODAL PANEL -->
            <div
                class="w-[680px] bg-white rounded-[32px] px-16 py-14 text-center border border-black/10 shadow-[0_50px_120px_rgba(0,0,0,0.4)]"
            >
                <!-- ICON -->
                <div
                    class="mx-auto mb-8 w-24 h-24 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 text-[48px]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="74"
                        height="74"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-timer-icon lucide-timer"
                    >
                        <line x1="10" x2="14" y1="2" y2="2" />
                        <line x1="12" x2="15" y1="14" y2="11" />
                        <circle cx="12" cy="14" r="8" />
                    </svg>
                </div>

                <!-- TITLE -->
                <h2 class="text-[36px] font-semibold text-gray-900">
                    Session Inactive
                </h2>

                <!-- EXPLANATION -->
                <p
                    class="mt-4 text-[20px] text-gray-600 leading-relaxed max-w-[520px] mx-auto"
                >
                    No interaction has been detected for a period of time. For
                    security purposes, this kiosk will reset automatically.
                </p>

                <!-- COUNTDOWN BLOCK -->
                <div class="mt-10">
                    <p
                        class="text-[18px] tracking-wide uppercase text-gray-500"
                    >
                        Returning to Idle Screen in
                    </p>

                    <p
                        class="mt-2 font-mono text-[64px] font-semibold text-red-600 tracking-widest"
                    >
                        {{ secondsLeft }}
                    </p>

                    <p class="mt-2 text-[18px] text-gray-500">seconds</p>
                </div>

                <!-- ACTION GUIDANCE -->

                <!-- ACTIONS -->
                <div class="mt-10 flex gap-8 justify-center">
                    <!-- SAFE ACTION -->
                    <!-- <button
                        class="px-12 h-16 rounded-2xl bg-gray-200 text-gray-800 text-[20px] font-semibold transition active:scale-[0.97]"
                        @click="$emit('continue')"
                    >
                        Continue Session
                    </button> -->

                    <!-- DESTRUCTIVE ACTION -->
                    <button
                        class="px-12 h-16 rounded-2xl bg-red-600 text-white text-[20px] font-semibold transition active:scale-[0.97]"
                        @click="$emit('confirm')"
                    >
                        End Session Now
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<style>
/* ================= Idle Warning Modal Transition ================= */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
    transform: scale(0.96);
}
/* ================================================================ */
</style>
