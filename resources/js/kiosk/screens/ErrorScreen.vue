<script setup>
import errorAnimation from "@/kiosk/animations/error.json";
const props = defineProps({
    message: {
        type: String,
        default: "Something went wrong",
    },
    locker: {
        type: [String, Number],
        default: null,
    },
});

const emit = defineEmits(["retry", "done"]);
</script>

<template>
    <div class="fixed inset-0 bg-black flex items-center justify-center z-50">
        <div class="text-center text-white">
            <!-- Lottie Animation -->
            <Vue3Lottie
                :animationData="errorAnimation"
                :height="180"
                :width="180"
                :loop="true"
                :autoplay="true"
                class="mx-auto mb-6"
            />

            <!-- Title -->
            <h1 class="text-2xl font-bold mb-2">Unlock Failed</h1>

            <!-- Message -->
            <p class="mb-4 opacity-80">
                {{ message }}
            </p>

            <!-- Locker Info -->
            <p v-if="locker" class="mb-6 text-sm opacity-60">
                Locker {{ locker }}
            </p>

            <!-- Buttons -->
            <div class="flex gap-4 justify-center">
                <button
                    class="bg-white text-black px-6 py-2 rounded-lg"
                    @click="$emit('retry')"
                >
                    Retry
                </button>

                <button
                    class="bg-red-600 px-6 py-2 rounded-lg"
                    @click="$emit('done')"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</template>
