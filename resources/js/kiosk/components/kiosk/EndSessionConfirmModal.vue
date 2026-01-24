<script setup>
const emit = defineEmits(["confirm", "cancel"]);

defineProps({
    show: {
        type: Boolean,
        required: true,
    },
});
</script>

<template>
    <teleport to="body">
        <!-- BACKDROP (always mounted while modal exists) -->
        <div
            v-show="show"
            class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center"
        >
            <!-- PANEL (animated) -->
            <Transition name="modal-fade">
                <div
                    v-if="show"
                    class="w-[620px] bg-white rounded-[28px] border border-black/10 shadow-[0_40px_100px_rgba(0,0,0,0.35)] px-14 py-12 text-center"
                >
                    <!-- ICON -->
                    <div
                        class="mx-auto mb-6 w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 text-[36px]"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="35"
                            height="35"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-log-out-icon lucide-log-out"
                        >
                            <path d="m16 17 5-5-5-5" />
                            <path d="M21 12H9" />
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        </svg>
                    </div>

                    <!-- TITLE -->
                    <p class="text-[34px] font-semibold text-gray-900">
                        End Current Session
                    </p>

                    <!-- MESSAGE -->
                    <p
                        class="mt-4 text-[20px] text-gray-600 leading-relaxed max-w-[480px] mx-auto"
                    >
                        This will cancel the current session and return the
                        kiosk to the start screen.
                    </p>

                    <p class="mt-2 text-[18px] text-gray-500">
                        Any progress made will not be saved.
                    </p>

                    <!-- ACTIONS -->
                    <div class="mt-12 flex justify-center gap-8">
                        <button
                            class="px-12 h-16 rounded-2xl bg-gray-200 text-gray-800 text-[20px] font-semibold transition active:scale-[0.97]"
                            @click="emit('cancel')"
                        >
                            Continue Session
                        </button>

                        <button
                            class="px-12 h-16 rounded-2xl bg-gray-800 text-white text-[20px] font-semibold transition active:scale-[0.97]"
                            @click="emit('confirm')"
                        >
                            End Session
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </teleport>
</template>
