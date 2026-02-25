<script setup>
const props = defineProps({
    lockers: Array,
    loading: Boolean,
});

const emit = defineEmits(["open"]);

function statusDot(locker) {
    const status = effectiveStatus(locker);

    switch (status) {
        case "AVAILABLE":
            return "bg-emerald-500";
        case "OCCUPIED":
            return "bg-blue-500";
        case "OVERDUE":
            return "bg-amber-500 animate-pulse";
        case "OUT_OF_SERVICE":
            return "bg-red-500";
    }
}

function effectiveStatus(locker) {
    if (locker.status === "OUT_OF_SERVICE") {
        return "OUT_OF_SERVICE";
    }

    if (locker.penalty && locker.penalty.status === "ACTIVE") {
        return "OVERDUE";
    }

    if (locker.rental && !locker.penalty) {
        return "OCCUPIED";
    }

    return "AVAILABLE";
}

function statusLabel(locker) {
    const status = effectiveStatus(locker);

    switch (status) {
        case "AVAILABLE":
            return "Available";
        case "OCCUPIED":
            return "Occupied";
        case "OVERDUE":
            return "Overdue";
        case "OUT_OF_SERVICE":
            return "Out of Service";
    }
}
function timeIndicator(locker) {
    if (!locker.rental) return null;

    const end = new Date(locker.rental.end_time);
    const now = new Date();
    const diff = end - now;

    const minutes = Math.floor(Math.abs(diff) / 60000);

    if (diff <= 0) {
        return `Overdue by ${minutes}m`;
    }

    return `Ends in ${minutes}m`;
}
</script>

<template>
    <div v-if="loading" class="py-10 text-center text-gray-500">Loading...</div>

    <div v-else class="grid grid-cols-5 gap-4">
        <div
            v-for="locker in lockers"
            :key="locker.id"
            @click="emit('open', locker)"
            :class="[
                'rounded-xl border p-4 shadow-sm hover:shadow-md transition cursor-pointer',
                effectiveStatus(locker) === 'OVERDUE'
                    ? 'border-amber-500 ring-2 ring-amber-200'
                    : effectiveStatus(locker) === 'OCCUPIED'
                    ? 'border-blue-300'
                    : effectiveStatus(locker) === 'AVAILABLE'
                    ? 'border-emerald-300'
                    : 'border-red-500 ring-2 ring-red-200 ',
            ]"
        >
            <div class="flex justify-between items-center mb-2">
                <div class="flex items-center gap-2">
                    <span
                        class="w-2.5 h-2.5 rounded-full"
                        :class="statusDot(locker)"
                    ></span>

                    <span
                        class="text-[11px] font-medium text-gray-500 uppercase"
                    >
                        {{ statusLabel(locker) }}
                    </span>
                </div>

                <span class="font-mono text-sm font-semibold">
                    {{ String(locker.locker_number).padStart(2, "0") }}
                </span>
            </div>

            <div class="text-xs text-gray-600 dark:text-gray-300">
                <div v-if="locker.rental">
                    <div class="font-semibold">
                        {{ locker.rental.student_name }}
                    </div>

                    <div class="text-[11px]">
                        {{ timeIndicator(locker) }}
                    </div>
                </div>

                <div v-else class="text-gray-400">Available</div>
            </div>
        </div>
    </div>
</template>
