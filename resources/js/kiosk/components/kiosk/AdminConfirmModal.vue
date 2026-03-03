<script setup>
defineProps({
    show: Boolean,
    message: String,
});
const emit = defineEmits(["confirm", "cancel"]);
</script>

<template>
    <Transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-3xl p-10 w-[500px] text-center">
                <h2 class="text-2xl font-semibold mb-6">{{ message }}</h2>

                <div class="flex gap-6 mt-8">
                    <button
                        class="flex-1 h-14 bg-gray-200 rounded-xl"
                        @click="$emit('cancel')"
                    >
                        Cancel
                    </button>

                    <button
                        class="flex-1 h-14 bg-red-600 text-white rounded-xl"
                        @click="$emit('confirm')"
                    >
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
