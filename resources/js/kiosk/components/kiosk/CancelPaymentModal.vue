<script setup>
defineProps({
    show: Boolean,
});

const emit = defineEmits(["confirm", "close"]);
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div
            class="w-[520px] rounded-2xl bg-white px-10 py-8 text-center shadow-xl"
        >
            <h2 class="text-[26px] font-semibold text-gray-900">
                Cancel Payment?
            </h2>

            <p class="mt-4 text-[18px] text-gray-600">
                If you cancel now, the transaction will be aborted and you will
                return to the previous screen.
            </p>

            <div class="mt-8 flex gap-4">
                <button
                    class="flex-1 h-14 rounded-xl bg-gray-200 text-gray-700 text-[18px] font-semibold"
                    @click="emit('close')"
                >
                    Continue Payment
                </button>

                <button
                    class="flex-1 h-14 rounded-xl bg-red-600 text-white text-[18px] font-semibold"
                    @click="emit('confirm')"
                >
                    Yes, Cancel
                </button>
            </div>
        </div>
    </div>
</template>
