<script setup>
import { ref, computed } from "vue";
// UI-only panel
// Logic, state, and emits will be wired later
const emit = defineEmits(["back", "confirm"]);
const selectedLocker = ref(null);
const selectedDuration = ref(null);

const props = defineProps({
    lockers: {
        type: Array,
        default: () => [],
    },
});

const isReadyToConfirm = computed(() => {
    return (
        selectedLocker.value !== null &&
        selectedDuration.value !== null &&
        !isOccupied(selectedLocker.value)
    );
});

const availableCount = computed(
    () => props.lockers.filter((l) => l.status === "AVAILABLE").length
);

function isOccupied(n) {
    return props.lockers.find((l) => l.number === n)?.status === "OCCUPIED";
}

function lockerStatus(n) {
    return isOccupied(n)
        ? {
              label: "OCCUPIED",
              badge: "bg-gray-500 text-white",
              frame: "border-gray-400",
          }
        : {
              label: "AVAILABLE",
              badge: "bg-emerald-600 text-white",
              frame: "border-emerald-400",
          };
}
</script>

<template>
    <section
        class="relative z-20 w-full mx-auto bg-white/90 backdrop-blur-xl border border-black/10 rounded-[24px] px-14 py-12 shadow-[0_24px_60px_rgba(0,0,0,0.18)]"
    >
        <!-- ========================= -->
        <!-- Top System Strip -->
        <!-- ========================= -->
        <div class="flex justify-between items-center">
            <p
                class="text-[24px] tracking-[0.4em] uppercase text-emerald-600 font-semibold"
            >
                Locker Selection
            </p>

            <p class="text-[24px] tracking-wide text-gray-500">
                Available Lockers
                <span class="font-mono font-semibold text-gray-900">
                    {{ availableCount }}
                </span>
            </p>
        </div>

        <!-- Divider -->
        <div class="my-8 h-px bg-black/10"></div>

        <!-- LEGEND -->
        <div class="mb-6 flex justify-center gap-10 text-[16px] text-gray-600">
            <div class="flex items-center gap-3">
                <span
                    class="inline-block w-4 h-4 rounded bg-emerald-500"
                ></span>
                <span class="tracking-wide uppercase">Available</span>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-block w-4 h-4 rounded bg-gray-400"></span>
                <span class="tracking-wide uppercase">Occupied</span>
            </div>
        </div>

        <!-- ========================= -->
        <!-- Locker Grid (5 x 3) -->
        <!-- ========================= -->
        <div class="grid grid-cols-5 gap-8 py-8">
            <div
                v-for="n in 15"
                :key="n"
                @click="!isOccupied(n) && (selectedLocker = n)"
                class="relative h-56 rounded-2xl border-2 transition-all duration-150 select-none flex flex-col justify-between overflow-hidden"
                :class="[
                    isOccupied(n)
                        ? 'bg-gray-100 border-gray-300 cursor-not-allowed'
                        : selectedLocker === n
                        ? 'bg-emerald-50 border-emerald-600 ring-4 ring-emerald-300 shadow-[0_16px_40px_rgba(16,185,129,0.35)]'
                        : 'bg-white border-black/10 shadow-md cursor-pointer',
                    !isOccupied(n) && 'active:scale-[0.97]',
                ]"
            >
                <!-- STATUS STRIP -->
                <div
                    class="h-10 flex items-center justify-center text-[13px] font-semibold tracking-widest uppercase"
                    :class="
                        selectedLocker === n
                            ? 'bg-emerald-600 text-white'
                            : lockerStatus(n).badge
                    "
                >
                    {{
                        selectedLocker === n
                            ? "SELECTED"
                            : lockerStatus(n).label
                    }}
                </div>

                <!-- MAIN BODY -->
                <div class="flex-1 flex flex-col items-center justify-center">
                    <p
                        class="font-mono text-[52px] font-bold"
                        :class="
                            isOccupied(n)
                                ? 'text-gray-500'
                                : selectedLocker === n
                                ? 'text-emerald-800'
                                : 'text-gray-900'
                        "
                    >
                        {{ String(n).padStart(2, "0") }}
                    </p>

                    <p
                        class="mt-1 text-[13px] tracking-widest uppercase text-gray-400"
                    >
                        Locker
                    </p>
                </div>

                <!-- FOOTER INDICATOR -->
                <div
                    class="h-4"
                    :class="[
                        isOccupied(n)
                            ? 'bg-gray-300'
                            : selectedLocker === n
                            ? 'bg-emerald-600'
                            : 'bg-emerald-200',
                    ]"
                />
            </div>
        </div>

        <!-- ========================= -->
        <!-- Selected Locker Feedback -->
        <!-- ========================= -->
        <div class="text-center py-10 border-t border-black/10">
            <div
                class="mx-auto mb-6 w-16 h-16 rounded-full border border-emerald-300 flex items-center justify-center text-emerald-600 text-3xl font-mono"
            >
                —
            </div>

            <p class="text-[32px] font-semibold text-gray-800">
                {{
                    selectedLocker
                        ? `Locker ${String(selectedLocker).padStart(2, "0")}`
                        : "No Locker Selected"
                }}
            </p>

            <p class="mt-4 text-[22px] text-gray-500 max-w-[560px] mx-auto">
                Please select an available locker from the grid above to
                continue.
            </p>
        </div>

        <!-- ========================= -->
        <!-- Duration Selection -->
        <!-- ========================= -->
        <div class="mt-10 grid grid-cols-3 gap-8 text-center">
            <div
                v-for="h in [1, 2, 3]"
                :key="h"
                @click="selectedDuration = h"
                class="relative z-30 rounded-xl border py-8 cursor-pointer select-none transition-all duration-150 active:scale-[0.97]"
                :class="
                    selectedDuration === h
                        ? 'bg-emerald-50 border-emerald-400'
                        : 'bg-white/70 border-black/10'
                "
            >
                <p class="text-[16px] tracking-widest uppercase text-gray-500">
                    Duration
                </p>

                <p
                    class="mt-2 font-mono text-[30px]"
                    :class="
                        selectedDuration === h
                            ? 'text-emerald-700 font-semibold'
                            : 'text-gray-900'
                    "
                >
                    {{ h }} Hour{{ h > 1 ? "s" : "" }}
                </p>
            </div>
        </div>

        <!-- ========================= -->
        <!-- Action Buttons -->
        <!-- ========================= -->
        <div class="mt-16 flex gap-6">
            <button
                class="flex-1 h-24 rounded-2xl bg-gray-200 text-gray-700 text-[26px] font-semibold transition active:scale-[0.97]"
                @click="emit('back')"
            >
                Back
            </button>

            <button
                class="flex-1 h-24 rounded-2xl text-[26px] font-semibold transition"
                :class="
                    isReadyToConfirm
                        ? 'bg-emerald-600 text-white active:scale-[0.97]'
                        : 'bg-gray-300 text-gray-400 cursor-not-allowed'
                "
                :disabled="!isReadyToConfirm"
                @click="
                    emit('confirm', {
                        locker: selectedLocker,
                        duration: selectedDuration,
                    })
                "
            >
                Confirm Selection
            </button>
        </div>
    </section>
</template>
