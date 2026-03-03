<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    lockers: {
        type: Array,
        default: () => [],
    },
    selectedLockerDetails: Object,
});

const emit = defineEmits([
    "select-locker",
    "force-unlock",
    "disable-locker",
    "enable-locker",
    "clear-penalty",
    "end-rental",
]);

const selectedNumber = computed(
    () => props.selectedLockerDetails?.locker?.number ?? null
);

const rental = computed(() => props.selectedLockerDetails?.rental);
const penalty = computed(() => props.selectedLockerDetails?.penalty);
const lockerStatus = computed(
    () => props.selectedLockerDetails?.locker?.status
);

const hasActiveRental = computed(() => rental.value?.status === "ACTIVE");

const hasPenalty = computed(() => penalty.value?.status === "ACTIVE");

const canEndRental = computed(() => hasActiveRental.value && !hasPenalty.value);

const canClearPenalty = computed(() => hasPenalty.value);

const isAvailable = computed(() => lockerStatus.value === "AVAILABLE");

const isOutOfService = computed(() => lockerStatus.value === "OUT_OF_SERVICE");

const canToggleLocker = computed(
    () => isAvailable.value || isOutOfService.value
);

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

function handleToggleLocker() {
    if (!canToggleLocker.value) return;

    if (isAvailable.value) emit("disable-locker");
    else emit("enable-locker");
}

function lockerVisual(locker) {
    switch (locker.operational_state) {
        case "OUT_OF_SERVICE":
            return {
                badge: "bg-red-500 text-white",
                frame: "border-red-500",
                footer: "bg-red-400",
                label: "OUT OF SERVICE",
            };

        case "PENALTY":
            return {
                badge: "bg-orange-600 text-white",
                frame: "border-orange-500",
                footer: "bg-orange-400",
                label: "PENALTY",
            };

        case "IN_USE":
            return {
                badge: "bg-blue-600 text-white",
                frame: "border-blue-500",
                footer: "bg-blue-400",
                label: "IN USE",
            };

        default:
            return {
                badge: "bg-emerald-600 text-white",
                frame: "border-emerald-500",
                footer: "bg-emerald-300",
                label: "AVAILABLE",
            };
    }
}
</script>

<template>
    <section
        class="relative z-20 w-full mx-auto bg-white/90 backdrop-blur-xl border border-black/10 rounded-[24px] px-14 py-12 shadow-[0_24px_60px_rgba(0,0,0,0.18)]"
    >
        <!-- ========================= -->
        <!-- Top Strip -->
        <!-- ========================= -->
        <div class="flex justify-between items-center">
            <p
                class="text-[24px] tracking-[0.4em] uppercase text-cyan-500 font-semibold"
            >
                Admin Control
            </p>

            <p class="text-[24px] tracking-wide text-gray-500">
                Total Lockers
                <span class="font-mono font-semibold text-gray-900">
                    {{ lockers.length }}
                </span>
            </p>
        </div>

        <div class="my-8 h-px bg-black/10"></div>
        <div class="mb-6 flex justify-center gap-10 text-[16px] text-gray-600">
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded bg-emerald-500"></span>
                <span>Available</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded bg-blue-500"></span>
                <span>In Use</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded bg-orange-500"></span>
                <span>Penalty</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded bg-red-500"></span>
                <span>Out of Service</span>
            </div>
        </div>

        <!-- ========================= -->
        <!-- Grid (5 x 3) -->
        <!-- ========================= -->
        <div class="grid grid-cols-5 gap-8 py-8">
            <div
                v-for="locker in lockers"
                :key="locker.id"
                @click="emit('select-locker', locker.locker_number)"
                class="relative h-56 rounded-2xl border-2 transition-all duration-150 select-none flex flex-col justify-between overflow-hidden cursor-pointer"
                :class="[
                    selectedNumber === locker.locker_number
                        ? 'ring-4 ring-cyan-400 border-cyan-500 shadow-[0_16px_40px_rgba(0,0,0,0.2)]'
                        : lockerVisual(locker).frame,
                ]"
            >
                <div
                    class="h-10 flex items-center justify-center text-[13px] font-semibold tracking-widest uppercase"
                    :class="lockerVisual(locker).badge"
                >
                    {{ lockerVisual(locker).label }}
                </div>

                <div class="flex-1 flex flex-col items-center justify-center">
                    <p class="font-mono text-[52px] font-bold text-gray-900">
                        {{ String(locker.locker_number).padStart(2, "0") }}
                    </p>

                    <p
                        class="mt-1 text-[13px] tracking-widest uppercase text-gray-400"
                    >
                        Locker
                    </p>
                </div>

                <div class="h-4" :class="lockerVisual(locker).footer" />
            </div>
        </div>

        <!-- ========================= -->
        <!-- Selected Locker Details -->
        <!-- ========================= -->
        <div class="text-center py-10 border-t border-black/10">
            <div
                class="mx-auto mb-6 w-16 h-16 rounded-full border border-cyan-300 flex items-center justify-center text-cyan-600 text-3xl font-mono"
            >
                {{
                    selectedNumber
                        ? String(selectedNumber).padStart(2, "0")
                        : "—"
                }}
            </div>

            <p class="text-[32px] font-semibold text-gray-800">
                {{
                    selectedNumber
                        ? `Locker ${String(selectedNumber).padStart(2, "0")}`
                        : "No Locker Selected"
                }}
            </p>

            <div v-if="rental" class="mt-6 text-[18px] text-gray-600 space-y-2">
                <p>Student: {{ rental.student.name }}</p>
                <p>Status: {{ rental.status }}</p>
                <p>Time Remaining: {{ rental.time_remaining_seconds }}s</p>
            </div>

            <div v-else class="mt-6 text-[18px] text-gray-500">
                No active rental.
            </div>
        </div>

        <!-- ========================= -->
        <!-- Action Buttons -->
        <!-- ========================= -->
        <div class="mt-12 grid grid-cols-2 gap-6">
            <button
                class="h-20 rounded-xl"
                :class="
                    canEndRental
                        ? 'bg-orange-600 text-white active:scale-[0.97]'
                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                "
                :disabled="!canEndRental"
                @click="emit('end-rental')"
            >
                End Rental Early
            </button>

            <button
                class="h-20 rounded-xl"
                :class="
                    canClearPenalty
                        ? 'bg-purple-600 text-white active:scale-[0.97]'
                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                "
                :disabled="!canClearPenalty"
                @click="emit('clear-penalty')"
            >
                Clear Penalty
            </button>

            <button
                class="h-20 rounded-xl bg-red-600 text-white active:scale-[0.97]"
                @click="emit('force-unlock')"
            >
                Force Unlock
            </button>

            <button
                class="h-20 rounded-xl"
                :class="toggleClass"
                :disabled="!canToggleLocker"
                @click="handleToggleLocker"
            >
                {{ toggleLabel }}
            </button>
        </div>
    </section>
</template>
