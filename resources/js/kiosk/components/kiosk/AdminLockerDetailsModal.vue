<script setup>
import { computed } from "vue";

const props = defineProps({
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

const hasRental = computed(() => !!props.details?.rental);

const hasActiveRental = computed(
    () => props.details?.rental?.status === "ACTIVE"
);

const hasPenalty = computed(() => props.details?.penalty?.status === "ACTIVE");

const lockerStatus = computed(() => props.details?.locker?.status);

const isAvailable = computed(() => lockerStatus.value === "AVAILABLE");

const isOutOfService = computed(() => lockerStatus.value === "OUT_OF_SERVICE");

const canEndRental = computed(() => hasActiveRental.value && !hasPenalty.value);

const canClearPenalty = computed(() => hasPenalty.value);

const canForceUnlock = computed(() => hasRental.value);

const toggleLabel = computed(() => {
    if (isAvailable.value) return "Disable Locker";
    if (isOutOfService.value) return "Enable Locker";
    return "Unavailable";
});

const toggleClass = computed(() => {
    if (isAvailable.value) return "bg-gray-800 text-white";
    if (isOutOfService.value) return "bg-emerald-600 text-white";
    return "bg-gray-300 text-gray-500 cursor-not-allowed";
});

const canToggleLocker = computed(
    () => isAvailable.value || isOutOfService.value
);

const handleToggleLocker = () => {
    if (!canToggleLocker.value) return;

    if (isAvailable.value) {
        emit("disable-locker");
    } else if (isOutOfService.value) {
        emit("enable-locker");
    }
};
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
                        :disabled="!canForceUnlock"
                        class="h-16 rounded-xl"
                        :class="
                            canForceUnlock
                                ? 'bg-red-600 text-white'
                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                        "
                        @click="$emit('force-unlock')"
                    >
                        Force Unlock
                    </button>

                    <button
                        :disabled="!canEndRental"
                        class="h-16 rounded-xl"
                        :class="
                            canEndRental
                                ? 'bg-orange-600 text-white'
                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                        "
                        @click="$emit('end-rental')"
                    >
                        End Rental Early
                    </button>

                    <button
                        :disabled="!canClearPenalty"
                        class="h-16 rounded-xl"
                        :class="
                            canClearPenalty
                                ? 'bg-purple-600 text-white'
                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                        "
                        @click="$emit('clear-penalty')"
                    >
                        Clear Penalty
                    </button>

                    <button
                        :disabled="!canToggleLocker"
                        class="h-16 rounded-xl"
                        :class="toggleClass"
                        @click="handleToggleLocker"
                    >
                        {{ toggleLabel }}
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
