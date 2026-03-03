<script setup>
defineProps({
    show: Boolean,
    details: Object,
});

const emit = defineEmits([
    "close",
    "force-unlock",
    "disable-locker",
    "enable-locker",
    "clear-penalty",
    "end-rental",
]);
</script>

<template>
    <Transition name="fade">
        <div
            v-if="show"
            class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
        >
            <div
                class="w-[900px] bg-white rounded-[36px] px-16 py-14 shadow-2xl"
            >
                <h2 class="text-[32px] font-semibold mb-6">
                    Locker {{ details?.locker?.number }}
                </h2>

                <div v-if="details?.rental">
                    <p class="text-xl mb-2">
                        Student: {{ details.rental.student.name }}
                    </p>

                    <p class="text-lg">Status: {{ details.rental.status }}</p>

                    <p class="text-lg">
                        Time Remaining:
                        {{ details.rental.time_remaining_seconds }}s
                    </p>
                </div>

                <div v-else>
                    <p class="text-lg text-gray-500">No active rental.</p>
                </div>

                <div class="mt-10 grid grid-cols-2 gap-6">
                    <button
                        v-if="details?.rental"
                        class="h-16 bg-red-600 text-white rounded-xl"
                        @click="$emit('force-unlock')"
                    >
                        Force Unlock
                    </button>

                    <button
                        v-if="details?.rental"
                        class="h-16 bg-orange-600 text-white rounded-xl"
                        @click="$emit('end-rental')"
                    >
                        End Rental Early
                    </button>

                    <button
                        v-if="details?.penalty"
                        class="h-16 bg-purple-600 text-white rounded-xl"
                        @click="$emit('clear-penalty')"
                    >
                        Clear Penalty
                    </button>

                    <button
                        v-if="details?.locker?.status === 'AVAILABLE'"
                        class="h-16 bg-gray-800 text-white rounded-xl"
                        @click="$emit('disable-locker')"
                    >
                        Disable Locker
                    </button>

                    <button
                        v-if="details?.locker?.status === 'OUT_OF_SERVICE'"
                        class="h-16 bg-emerald-600 text-white rounded-xl"
                        @click="$emit('enable-locker')"
                    >
                        Enable Locker
                    </button>
                </div>

                <button
                    class="mt-8 w-full h-16 bg-gray-200 rounded-xl"
                    @click="$emit('close')"
                >
                    Close
                </button>
            </div>
        </div>
    </Transition>
</template>
