<script setup>
import { ref, computed, onUnmounted, watch } from "vue";

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

const studentInitials = computed(() => {
    if (!rental.value?.student) return "";

    const first = rental.value.student.first_name?.[0] ?? "";
    const last = rental.value.student.last_name?.[0] ?? "";

    return (first + last).toUpperCase();
});

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
                badge: "bg-rose-500 text-white",
                frame: "border-rose-500",
                footer: "bg-rose-400",
                label: "OUT OF SERVICE",
            };

        case "PENALTY":
            return {
                badge: "bg-amber-600 text-white",
                frame: "border-amber-500",
                footer: "bg-amber-400",
                label: "OVERDUE",
            };

        case "IN_USE":
            return {
                badge: "bg-sky-600 text-white",
                frame: "border-sky-500",
                footer: "bg-sky-400",
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

/* ===============================
   TIMER
================================ */
const timeRemaining = ref(0);
let timerInterval = null;

function formatSeconds(totalSeconds) {
    const seconds = Math.max(0, Math.floor(totalSeconds));

    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;

    return [
        String(h).padStart(2, "0"),
        String(m).padStart(2, "0"),
        String(s).padStart(2, "0"),
    ].join(":");
}

const formattedTimeRemaining = computed(() =>
    formatSeconds(Math.max(0, timeRemaining.value))
);

watch(rental, (newRental) => {
    clearInterval(timerInterval);

    if (!newRental) {
        timeRemaining.value = 0;
        return;
    }

    timeRemaining.value = Math.floor(
        Number(newRental.time_remaining_seconds ?? 0)
    );

    timerInterval = setInterval(() => {
        if (timeRemaining.value > 0) {
            timeRemaining.value--;
        }
    }, 1000);
});
watch(rental, (r) => {
    console.log("PHOTO URL:", r?.student?.photo_url);
});

onUnmounted(() => {
    clearInterval(timerInterval);
});
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
                <span class="w-4 h-4 rounded bg-sky-500"></span>
                <span>In Use</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded bg-amber-500"></span>
                <span>Overdue</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded bg-rose-500"></span>
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
            <!-- Locker Number -->

            <!-- <p class="text-[32px] font-semibold text-gray-800">
                {{
                    selectedNumber
                        ? `Locker ${String(selectedNumber).padStart(2, "0")}`
                        : "No Locker Selected"
                }}
            </p> -->

            <!-- ========================= -->
            <!-- OUT OF SERVICE -->
            <!-- ========================= -->
            <div
                v-if="lockerStatus === 'OUT_OF_SERVICE'"
                class="mt-10 text-rose-600 text-xl font-semibold"
            >
                This locker is currently out of service.
            </div>

            <!-- ========================= -->
            <!-- ACTIVE RENTAL -->
            <!-- ========================= -->
            <div v-else-if="rental" class="mt-5">
                <!-- Student Info -->
                <div class="flex items-center gap-8 justify-center">
                    <!-- Photo -->
                    <div
                        class="w-28 h-28 rounded-2xl overflow-hidden border border-cyan-300 shadow-md"
                    >
                        <img
                            v-if="rental.student?.photo_url"
                            :src="rental.student.photo_url"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center bg-cyan-500 text-white text-4xl font-bold"
                        >
                            {{ studentInitials }}
                        </div>
                    </div>

                    <!-- Text Info -->
                    <div class="text-left">
                        <h2 class="text-3xl font-bold text-gray-900">
                            {{ rental.student?.first_name }}
                            {{ rental.student?.last_name }}
                        </h2>

                        <p class="text-gray-500 text-lg">
                            {{ rental.student?.student_number }}
                        </p>

                        <p class="text-gray-600 mt-2">
                            {{ rental.student?.year_level }} -
                            {{ rental.student?.department }}
                        </p>
                    </div>
                </div>

                <!-- Rental Timing -->
                <div class="mt-10 grid grid-cols-3 gap-6 text-center">
                    <div class="bg-gray-50 rounded-xl p-6 shadow-sm">
                        <p
                            class="text-sm uppercase tracking-wide text-gray-500"
                        >
                            Start Time
                        </p>
                        <p class="text-xl font-semibold">
                            {{
                                new Date(rental.start_time).toLocaleTimeString()
                            }}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6 shadow-sm">
                        <p
                            class="text-sm uppercase tracking-wide text-gray-500"
                        >
                            End Time
                        </p>
                        <p class="text-xl font-semibold">
                            {{ new Date(rental.end_time).toLocaleTimeString() }}
                        </p>
                    </div>

                    <div class="bg-cyan-50 rounded-xl p-6 shadow-md">
                        <p
                            class="text-sm uppercase tracking-wide text-cyan-500"
                        >
                            Time Remaining
                        </p>

                        <p
                            v-if="timeRemaining > 0"
                            class="text-2xl font-bold text-cyan-700"
                        >
                            {{ formattedTimeRemaining }}
                        </p>

                        <p v-else class="text-2xl font-bold text-orange-600">
                            EXPIRED
                        </p>
                    </div>
                </div>

                <!-- ========================= -->
                <!-- PENALTY SECTION -->
                <!-- ========================= -->
                <div
                    v-if="penalty"
                    class="mt-10 bg-amber-50 border border-orange-200 rounded-2xl p-6"
                >
                    <h3 class="text-xl font-semibold text-amber-700 mb-4">
                        Penalty Active
                    </h3>

                    <div class="grid grid-cols-3 gap-6 text-center">
                        <div>
                            <p class="text-sm text-amber-500 uppercase">
                                Exceeded
                            </p>
                            <p class="font-semibold">
                                {{ penalty.exceeded_duration }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-amber-500 uppercase">
                                Amount
                            </p>
                            <p class="font-semibold">
                                ₱ {{ penalty.frozen_amount }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-amber-500 uppercase">
                                Started
                            </p>
                            <p class="font-semibold">
                                {{
                                    new Date(
                                        penalty.started_at
                                    ).toLocaleTimeString()
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="mt-10 text-gray-500 text-xl">
                This locker currently has no active rental.
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
