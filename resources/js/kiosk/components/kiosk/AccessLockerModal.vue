<script setup>
const emit = defineEmits(["confirm", "cancel"]);

defineProps({
    show: {
        type: Boolean,
        required: true,
    },
    lockerNumber: Number,
});
</script>

<template>
    <teleport to="body">
        <!-- BACKDROP -->
        <div
            v-show="show"
            class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center"
        >
            <!-- PANEL -->
            <Transition name="modal-fade">
                <div
                    v-if="show"
                    class="w-[620px] bg-white rounded-[28px] border border-black/10 shadow-[0_40px_100px_rgba(0,0,0,0.35)] px-14 py-12 text-center"
                >
                    <!-- ICON -->
                    <div
                        class="mx-auto mb-6 w-20 h-20 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="42"
                            height="42"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </div>

                    <!-- TITLE -->
                    <p class="text-[34px] font-semibold text-gray-900">
                        Open Locker {{ lockerNumber }}
                    </p>

                    <!-- EMPHASIS BLOCK -->
                    <div
                        class="mt-8 px-8 py-6 rounded-2xl bg-emerald-50 border border-emerald-200"
                    >
                        <p class="text-[22px] font-semibold text-emerald-700">
                            Temporary Unlock
                        </p>

                        <p class="mt-2 text-[18px] text-emerald-700/80">
                            The locker will open for a short time only.
                        </p>
                    </div>

                    <!-- SUPPORTING TEXT -->
                    <p class="mt-6 text-[19px] text-gray-500">
                        Your rental session will remain active.
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
                            class="px-12 h-16 rounded-2xl bg-emerald-600 text-white text-[20px] font-semibold shadow-[0_10px_25px_rgba(16,185,129,0.35)] transition active:scale-[0.97]"
                            @click="emit('confirm')"
                        >
                            Open Locker
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </teleport>
</template>
