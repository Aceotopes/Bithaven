<script setup>
defineProps({
    type: {
        type: String,
        default: "success", // 'success' | 'error'
    },
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        required: true,
    },
    show: {
        type: Boolean,
        required: true,
    },
    countdown: {
        type: Number,
    },
    showAction: {
        type: Boolean,
    },
    actionLabel: {
        type: String,
        default: "Continue",
    },
});

defineEmits(["close", "action"]);
</script>

<template>
    <Transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
            @click.stop
        >
            <div
                class="relative w-[780px] rounded-[36px] px-16 py-14 text-center bg-white/75 backdrop-blur-2xl shadow-[0_30px_80px_rgba(0,0,0,0.25)] animate-modal-in"
                :class="type === 'error' ? 'animate-shake' : ''"
                @click.stop
            >
                <!-- Status Icon -->
                <div class="relative mx-auto mb-12 w-32 h-32">
                    <!-- Glow -->
                    <div
                        class="absolute inset-0 rounded-full blur-xl opacity-60"
                        :class="
                            type === 'success'
                                ? 'bg-emerald-400'
                                : 'bg-rose-400'
                        "
                    />

                    <!-- Icon badge -->
                    <div
                        class="relative z-10 w-35 h-35 mx-auto rounded-full bg-white flex items-center justify-center shadow-[0_10px_30px_rgba(0,0,0,0.2)]"
                    >
                        <!-- Success Icon -->
                        <svg
                            v-if="type === 'success'"
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-25 h-25 text-emerald-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>

                        <!-- Error Icon -->
                        <svg
                            v-if="type === 'error'"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-25 h-25 text-rose-600"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Title -->
                <p
                    class="mt-6 text-[42px] font-semibold tracking-tight text-gray-900"
                >
                    {{ title }}
                </p>

                <!-- divider -->
                <div
                    class="mx-auto my-5 h-[2px] w-100 bg-gradient-to-r from-transparent via-black/25 to-transparent"
                ></div>

                <div class="mt-8">
                    <!-- Primary Message -->
                    <p
                        class="text-[26px] leading-[1.35] font-medium text-gray-900"
                    >
                        <span v-if="type === 'success'">
                            Your information has been
                            <span class="font-semibold text-emerald-600">
                                verified </span
                            >.
                        </span>

                        <span v-else>
                            Your information is
                            <span class="font-semibold text-rose-600">
                                not currently enrolled
                            </span>
                            in the system.
                        </span>
                    </p>
                    <p
                        v-if="countdown !== null"
                        class="mt-6 text-xl text-gray-500"
                    >
                        Redirecting in
                        <span class="font-semibold text-gray-800">
                            {{ countdown }}
                        </span>
                        seconds…
                    </p>

                    <!-- Secondary Instruction -->
                    <p
                        class="text-[22px] leading-[1.45]"
                        :class="
                            type === 'success'
                                ? 'text-gray-600'
                                : 'text-gray-700'
                        "
                    >
                        <span v-if="type === 'success'">
                            You will be redirected to the system shortly.
                        </span>

                        <span v-else>
                            Please contact the
                            <span class="font-semibold">administrator</span>
                            for further assistance.
                        </span>
                    </p>
                </div>

                <!-- Action Button -->
                <button
                    v-if="type === 'error'"
                    class="mt-16 w-full py-6 text-2xl font-semibold rounded-2xl text-white bg-gradient-to-r from-rose-500 to-red-600 shadow-[0_12px_30px_rgba(239,68,68,0.45)] transition-transform duration-150 active:scale-95"
                    @click="$emit('close')"
                >
                    Try Again
                </button>
                <button
                    v-if="showAction"
                    class="mt-10 w-full py-6 text-2xl font-semibold rounded-2xl text-white bg-gradient-to-r from-emerald-500 to-green-600 shadow-lg shadow-green-500/40 transition-transform duration-150 active:scale-95"
                    @click="$emit('action')"
                >
                    {{ actionLabel }}
                </button>
            </div>
        </div>
    </Transition>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@keyframes modal-in {
    from {
        opacity: 0;
        transform: scale(0.94) translateY(16px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.animate-modal-in {
    animation: modal-in 0.38s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes shake {
    0% {
        transform: translateX(0);
    }
    20% {
        transform: translateX(-10px);
    }
    40% {
        transform: translateX(10px);
    }
    60% {
        transform: translateX(-6px);
    }
    80% {
        transform: translateX(6px);
    }
    100% {
        transform: translateX(0);
    }
}

.animate-shake {
    animation: shake 0.4s ease-in-out;
}
</style>
