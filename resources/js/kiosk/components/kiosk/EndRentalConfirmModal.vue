<script setup>
defineProps({
    show: Boolean,
    lockerNumber: Number,
});

const emit = defineEmits(["cancel", "confirm"]);
</script>

<template>
    <teleport to="body">
        <div
            v-show="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        >
            <Transition name="modal-fade">
                <!-- CONTAINER -->
                <div
                    v-if="show"
                    class="w-[620px] bg-white rounded-[28px] border border-black/10 shadow-[0_40px_100px_rgba(0,0,0,0.35)] px-14 py-12 text-center"
                >
                    <!-- ICON -->
                    <div
                        class="mx-auto mb-6 w-20 h-20 rounded-full bg-rose-50 flex items-center justify-center text-rose-600"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="60"
                            height="60"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                    </div>

                    <!-- TITLE -->
                    <p class="text-[34px] font-semibold text-gray-900">
                        End Locker Rental
                    </p>

                    <!-- LOCKER CONTEXT -->
                    <p class="mt-4 text-[20px] text-gray-600">
                        You are about to end rental for
                    </p>

                    <p
                        class="mt-2 text-[32px] font-mono font-bold text-gray-900 tracking-wide"
                    >
                        Locker {{ lockerNumber }}
                    </p>

                    <!-- WARNING BLOCK -->
                    <div
                        class="mt-8 px-8 py-6 rounded-2xl bg-rose-50 border border-rose-200"
                    >
                        <p class="text-[20px] font-semibold text-rose-700">
                            This action is permanent
                        </p>

                        <p
                            class="mt-2 text-[18px] text-rose-700/80 leading-relaxed"
                        >
                            Please make sure all your belongings have been
                            removed before proceeding.
                        </p>
                    </div>

                    <!-- SUPPORT TEXT -->
                    <p class="mt-6 text-[18px] text-gray-500">
                        Once ended, your session cannot be restored.
                    </p>

                    <!-- ACTIONS -->
                    <div class="mt-14 flex justify-center gap-8">
                        <button
                            class="px-12 h-16 rounded-2xl bg-gray-200 text-gray-800 text-[20px] font-semibold transition active:scale-[0.97]"
                            @click="emit('cancel')"
                        >
                            Cancel
                        </button>

                        <button
                            class="px-12 h-16 rounded-2xl bg-rose-600 text-white text-[20px] font-semibold shadow-[0_10px_25px_rgba(220,38,38,0.35)] transition active:scale-[0.97]"
                            @click="emit('confirm')"
                        >
                            End Rental
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </teleport>
</template>
