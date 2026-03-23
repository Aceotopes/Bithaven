<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useConfirm } from "primevue/useconfirm";
import axios from "axios";
import Dialog from "primevue/dialog";
import { useToast } from "primevue/usetoast";

const toast = useToast();
const confirm = useConfirm();

const props = defineProps({
    visible: Boolean,
    locker: Object,
});

const emit = defineEmits(["update:visible", "refresh"]);

const loadingAction = ref(false);
const now = ref(new Date());
let interval = null;

onMounted(() => {
    interval = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onBeforeUnmount(() => {
    if (interval) clearInterval(interval);
});

/* -----------------------
   STUDENT INFO
------------------------ */

const fullName = computed(() => {
    if (!props.locker?.rental) return null;
    return `${props.locker.rental.first_name} ${props.locker.rental.last_name}`;
});

const initials = computed(() => {
    if (!props.locker?.rental) return "";
    return (
        props.locker.rental.first_name?.[0] + props.locker.rental.last_name?.[0]
    ).toUpperCase();
});

/* -----------------------
   STATUS FLAGS
------------------------ */

const isActiveRental = computed(
    () => props.locker?.rental?.status === "ACTIVE"
);

const isPenaltyActive = computed(
    () => props.locker?.penalty?.status === "ACTIVE"
);

const isOutOfService = computed(
    () => props.locker?.status === "OUT_OF_SERVICE"
);

/* -----------------------
   TIME FORMATTER (HH:mm:ss)
------------------------ */

function formatDuration(ms) {
    const totalSeconds = Math.floor(ms / 1000);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    return `${hours}h ${minutes}m ${seconds}s`;
}

const remainingTime = computed(() => {
    if (!isActiveRental.value) return null;

    const end = new Date(props.locker.rental.end_time);
    const diff = end - now.value;

    if (diff <= 0) return "Expiring...";

    return formatDuration(diff);
});

const overdueTime = computed(() => {
    if (!isPenaltyActive.value) return null;

    const end = new Date(props.locker.rental.end_time);
    const diff = now.value - end;

    return formatDuration(diff);
});

/* -----------------------
   BUTTON VALIDATION
------------------------ */

const canEndRental = computed(() => isActiveRental.value);
const canClearPenalty = computed(() => isPenaltyActive.value);
const canDisableLocker = computed(
    () =>
        !isActiveRental.value && !isPenaltyActive.value && !isOutOfService.value
);
const canEnableLocker = computed(() => isOutOfService.value);

/* -----------------------
   CONFIRMED ACTION CALLER
------------------------ */

async function confirmAction(message, endpoint) {
    confirm.require({
        group: "action",
        message,
        header: "Confirm Action",
        icon: "pi pi-exclamation-triangle",
        acceptClass: "p-button-danger",

        accept: async () => {
            loadingAction.value = true;

            try {
                await axios.post(endpoint);

                toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Action completed successfully",
                    life: 5000,
                });

                emit("refresh");
            } catch (e) {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Something went wrong",
                    life: 5000,
                });

                console.error(e);
            } finally {
                loadingAction.value = false;
            }
        },
    });
}

/* -----------------------
   ACTION METHODS
------------------------ */

function forceUnlock() {
    confirmAction(
        "Force unlock this locker?",
        `/admin/live/lockers/${props.locker.id}/force-unlock`
    );
}

function endRental() {
    confirmAction(
        "End this rental immediately?",
        `/admin/live/rentals/${props.locker.rental.id}/end`
    );
}

function clearPenalty() {
    confirmAction(
        "Clear active penalty?",
        `/admin/live/penalties/${props.locker.penalty.id}/clear`
    );
}

function disableLocker() {
    confirmAction(
        "Disable this locker (Out of Service)?",
        `/admin/live/lockers/${props.locker.id}/disable`
    );
}

function enableLocker() {
    confirmAction(
        "Enable this locker?",
        `/admin/live/lockers/${props.locker.id}/enable`
    );
}
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :autoFocus="false"
        class="w-[550px] rounded-2xl"
        @update:visible="emit('update:visible', $event)"
    >
        <div v-if="locker" class="space-y-8">
            <!-- HEADER -->
            <div class="flex justify-between items-start">
                <div>
                    <div
                        class="text-xs uppercase tracking-widest text-gray-400"
                    >
                        Locker
                    </div>

                    <div
                        class="text-4xl font-semibold tracking-tight flex items-center gap-3"
                    >
                        {{ String(locker.locker_number).padStart(2, "0") }}

                        <span
                            class="text-xs px-3 py-1 rounded-full font-medium"
                            :class="{
                                'bg-emerald-100 text-emerald-700':
                                    locker.status === 'AVAILABLE',
                                'bg-blue-100 text-blue-700': isActiveRental,
                                'bg-amber-100 text-amber-700': isPenaltyActive,
                                'bg-red-100 text-red-700': isOutOfService,
                            }"
                        >
                            {{
                                isPenaltyActive
                                    ? "Overdue"
                                    : isActiveRental
                                    ? "Occupied"
                                    : locker.status
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- STUDENT SECTION -->
            <div
                v-if="locker.rental"
                class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800"
            >
                <!-- Avatar -->
                <div
                    class="w-14 h-14 rounded-full bg-cyan-500 flex items-center justify-center text-base font-semibold text-white overflow-hidden"
                >
                    <img
                        v-if="locker.rental.photo_url"
                        :src="locker.rental.photo_url"
                        class="w-full h-full object-cover"
                    />
                    <span v-else>{{ initials }}</span>
                </div>

                <!-- Info -->
                <div class="flex-1">
                    <div class="font-semibold text-base">
                        {{ fullName }}
                    </div>

                    <div class="text-xs text-gray-500">
                        {{ locker.rental.section }} • Year
                        {{ locker.rental.year_level }}
                    </div>
                </div>

                <!-- Time -->
                <div class="text-right">
                    <div
                        v-if="isActiveRental"
                        class="text-sm font-medium text-blue-600"
                    >
                        {{ remainingTime }}
                    </div>

                    <div
                        v-if="isPenaltyActive"
                        class="text-sm font-semibold text-amber-600"
                    >
                        {{ overdueTime }} overdue
                    </div>
                </div>
            </div>

            <!-- TELEMETRY -->
            <div v-if="locker.rental" class="grid grid-cols-2 gap-3 text-sm">
                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div class="text-xs text-gray-500">Start Time</div>
                    <div class="font-medium">
                        {{
                            new Date(
                                locker.rental.start_time
                            ).toLocaleTimeString()
                        }}
                    </div>
                </div>

                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div class="text-xs text-gray-500">End Time</div>
                    <div class="font-medium">
                        {{
                            new Date(
                                locker.rental.end_time
                            ).toLocaleTimeString()
                        }}
                    </div>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="space-y-3">
                <div class="text-xs uppercase tracking-wide text-gray-400">
                    Actions
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <!-- End Rental (Primary / Destructive) -->
                    <button
                        @click="endRental"
                        :disabled="!canEndRental"
                        class="flex items-center justify-center gap-2 px-3 py-2 text-sm rounded-lg border transition border-red-200 text-red-600 bg-red-50 hover:bg-red-100 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-red-50"
                    >
                        <i class="pi pi-stop text-xs"></i>
                        End Rental
                    </button>

                    <!-- Clear Penalty -->
                    <button
                        @click="clearPenalty"
                        :disabled="!canClearPenalty"
                        class="flex items-center justify-center gap-2 px-3 py-2 text-sm rounded-lg border transition border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent"
                    >
                        <i class="pi pi-times text-xs"></i>
                        Clear Penalty
                    </button>

                    <!-- Force Unlock -->
                    <button
                        @click="forceUnlock"
                        class="flex items-center justify-center gap-2 px-3 py-2 text-sm rounded-lg border transition border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800"
                    >
                        <i class="pi pi-unlock text-xs"></i>
                        Unlock
                    </button>

                    <!-- Disable Locker -->
                    <button
                        v-if="!isOutOfService"
                        @click="disableLocker"
                        :disabled="!canDisableLocker"
                        class="flex items-center justify-center gap-2 px-3 py-2 text-sm rounded-lg border transition border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-transparent"
                    >
                        <i class="pi pi-ban text-xs"></i>
                        Disable
                    </button>

                    <!-- Enable Locker -->
                    <button
                        v-else
                        @click="enableLocker"
                        class="flex items-center justify-center gap-2 px-3 py-2 text-sm rounded-lg border transition border-emerald-200 text-emerald-600 bg-emerald-50 hover:bg-emerald-100"
                    >
                        <i class="pi pi-check text-xs"></i>
                        Enable
                    </button>
                </div>
            </div>
        </div>
    </Dialog>
</template>
