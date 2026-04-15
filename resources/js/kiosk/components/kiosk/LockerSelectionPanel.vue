<script setup>
import { ref, computed } from "vue";

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

const isCustomMode = ref(false);
const showCustomModal = ref(false);

const pricePerHour = 5;

const totalAmount = computed(() => {
    if (!selectedDuration.value) return 0;
    return selectedDuration.value * pricePerHour;
});

const availableCount = computed(
    () => props.lockers.filter((l) => l.status === "AVAILABLE").length
);

function isOccupied(n) {
    return props.lockers.find((l) => l.number === n)?.status === "OCCUPIED";
}

function isOutOfService(n) {
    return (
        props.lockers.find((l) => l.number === n)?.status === "OUT_OF_SERVICE"
    );
}
function lockerStatus(n) {
    if (isOutOfService(n)) {
        return {
            label: "OUT OF SERVICE",
            badge: "bg-rose-600 text-white",
            frame: "border-rose-400",
        };
    }

    if (isOccupied(n)) {
        return {
            label: "OCCUPIED",
            badge: "bg-gray-500 text-white",
            frame: "border-gray-400",
        };
    }

    return {
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

            <div class="flex items-center gap-3">
                <span class="inline-block w-4 h-4 rounded bg-rose-500"></span>
                <span class="tracking-wide uppercase">Out of Service</span>
            </div>
        </div>

        <!-- ========================= -->
        <!-- Locker Grid (5 x 3) -->
        <!-- ========================= -->
        <div class="grid grid-cols-5 gap-8 py-8">
            <div
                v-for="n in 15"
                :key="n"
                @click="
                    !isOccupied(n) && !isOutOfService(n) && (selectedLocker = n)
                "
                class="relative h-56 rounded-2xl border-2 transition-all duration-150 select-none flex flex-col justify-between overflow-hidden"
                :class="[
                    isOutOfService(n)
                        ? 'bg-rose-100 border-rose-300 cursor-not-allowed'
                        : isOccupied(n)
                        ? 'bg-gray-100 border-gray-300 cursor-not-allowed'
                        : selectedLocker === n
                        ? 'bg-emerald-50 border-emerald-600 ring-4 ring-emerald-300 shadow-[0_16px_40px_rgba(16,185,129,0.35)]'
                        : 'bg-white border-black/10 shadow-md cursor-pointer',
                    !isOccupied(n) &&
                        !isOutOfService(n) &&
                        'active:scale-[0.97]',
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
                            isOutOfService(n)
                                ? 'text-rose-500'
                                : isOccupied(n)
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
                        isOutOfService(n)
                            ? 'bg-rose-400'
                            : isOccupied(n)
                            ? 'bg-gray-300'
                            : selectedLocker === n
                            ? 'bg-emerald-600'
                            : 'bg-emerald-200',
                    ]"
                />
            </div>
        </div>

        <!-- ========================= -->
        <!-- Duration Selection -->
        <!-- ========================= -->
        <!-- PRESET BUTTONS -->
        <div class="border-t border-black/30"></div>
        <div class="mt-10 grid grid-cols-4 gap-8 text-center">
            <div
                v-for="h in [1, 2, 3]"
                :key="h"
                @click="
                    selectedDuration = h;
                    isCustomMode = false;
                "
                class="rounded-xl border py-8 cursor-pointer"
                :class="
                    selectedDuration === h && !isCustomMode
                        ? 'bg-emerald-50 border-emerald-400'
                        : 'bg-white/70 border-black/30'
                "
            >
                <p class="text-[16px] uppercase text-gray-500">Duration</p>
                <p class="mt-2 font-mono text-[30px]">
                    {{ h }} Hour{{ h > 1 ? "s" : "" }}
                </p>
            </div>

            <!-- CUSTOM BUTTON -->
            <div
                @click="showCustomModal = true"
                class="rounded-xl border py-8 cursor-pointer"
                :class="
                    isCustomMode
                        ? 'bg-emerald-50 border-emerald-400'
                        : 'bg-white/70 border-black/30'
                "
            >
                <p class="text-[16px] uppercase text-gray-500">Duration</p>
                <!-- <p class="mt-2 font-mono text-[24px]">4–8 Hours</p> -->
                <p class="mt-2 font-mono text-[34px]">Custom</p>
            </div>
        </div>

        <!-- ========================= -->
        <!-- Selected Locker Feedback -->
        <!-- ========================= -->
        <div class="text-center py-5">
            <div
                class="mx-auto mb-3 w-20 h-20 rounded-full border flex items-center justify-center text-[28px] font-semibold"
                :class="
                    selectedDuration
                        ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                        : 'border-gray-300 text-gray-400'
                "
            >
                {{ selectedDuration ? `₱${totalAmount}` : "—" }}
            </div>

            <p class="text-[30px] font-semibold text-gray-800">
                {{
                    selectedLocker
                        ? `Locker ${String(selectedLocker).padStart(2, "0")}`
                        : "No Locker Selected"
                }}
            </p>

            <p class="mt-2 text-[22px] text-gray-600">
                {{
                    selectedDuration
                        ? `Duration: ${selectedDuration} Hour${
                              selectedDuration > 1 ? "s" : ""
                          }`
                        : "No Duration Selected"
                }}
            </p>

            <p class="mt-4 text-[18px] text-gray-500 max-w-[560px] mx-auto">
                {{
                    !selectedLocker
                        ? "Select a locker to begin."
                        : !selectedDuration
                        ? "Choose a duration to continue."
                        : "Ready to proceed. Confirm your selection below."
                }}
            </p>
        </div>

        <!-- ========================= -->
        <!-- Action Buttons -->
        <!-- ========================= -->
        <div class="mt-8 flex gap-6">
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
    <!-- MODAL FOR CUSTOM TIME -->
    <div
        v-if="showCustomModal"
        class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
    >
        <div class="bg-white w-[600px] rounded-2xl p-8 shadow-2xl text-center">
            <h2 class="text-[26px] font-semibold mb-2">Select Duration</h2>

            <p class="text-gray-500 mb-6">Choose between 4 to 8 hours</p>

            <!-- OPTIONS -->
            <div class="grid grid-cols-5 gap-4 mb-8">
                <div
                    v-for="h in [4, 5, 6, 7, 8]"
                    :key="h"
                    @click="selectedDuration = h"
                    class="py-4 rounded-xl border cursor-pointer transition"
                    :class="
                        selectedDuration === h
                            ? 'bg-emerald-600 text-white border-emerald-600'
                            : 'bg-white border-gray-300 hover:bg-gray-50'
                    "
                >
                    <p class="text-[20px] font-mono">{{ h }}h</p>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="flex gap-4">
                <button
                    class="flex-1 py-4 rounded-xl bg-gray-200 text-gray-700"
                    @click="showCustomModal = false"
                >
                    Cancel
                </button>

                <button
                    class="flex-1 py-4 rounded-xl font-semibold"
                    :class="
                        selectedDuration >= 4
                            ? 'bg-emerald-600 text-white'
                            : 'bg-gray-300 text-gray-400 cursor-not-allowed'
                    "
                    :disabled="selectedDuration < 4"
                    @click="
                        isCustomMode = true;
                        showCustomModal = false;
                    "
                >
                    Confirm
                </button>
            </div>
        </div>
    </div>
</template>
