<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useConfirm } from "primevue/useconfirm";
import axios from "axios";
import Dialog from "primevue/dialog";

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

function confirmAction(message, endpoint) {
    confirm.require({
        message,
        header: "Confirm Action",
        icon: "pi pi-exclamation-triangle",
        acceptClass: "p-button-danger",
        accept: async () => {
            loadingAction.value = true;
            try {
                await axios.post(endpoint);
                emit("refresh");
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
    <ConfirmDialog />

    <Dialog
        :visible="visible"
        modal
        :autoFocus="false"
        class="w-[560px] rounded-2xl"
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
                    <div class="text-4xl font-semibold tracking-tight">
                        {{ String(locker.locker_number).padStart(2, "0") }}
                    </div>
                </div>

                <div
                    class="text-xs font-medium px-3 py-1 rounded-full"
                    :class="{
                        'bg-emerald-100 text-gray-600':
                            locker.status === 'AVAILABLE',
                        'bg-blue-50 text-blue-600': isActiveRental,
                        'bg-amber-50 text-amber-600': isPenaltyActive,
                        'bg-red-50 text-red-600': isOutOfService,
                    }"
                >
                    {{
                        isPenaltyActive
                            ? "Overdue"
                            : isActiveRental
                            ? "Occupied"
                            : locker.status
                    }}
                </div>
            </div>

            <!-- STUDENT SECTION -->
            <div v-if="locker.rental" class="flex items-center gap-4">
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

                <div class="flex-1">
                    <div class="font-medium text-lg">
                        {{ fullName }}
                    </div>
                    <div class="text-sm text-gray-400">
                        {{ locker.rental.section }}
                        • Year {{ locker.rental.year_level }}
                    </div>
                </div>

                <div class="text-right text-sm">
                    <div v-if="isActiveRental" class="text-gray-600">
                        {{ remainingTime }}
                    </div>
                    <div
                        v-if="isPenaltyActive"
                        class="text-amber-600 font-medium"
                    >
                        {{ overdueTime }} overdue
                    </div>
                </div>
            </div>

            <!-- TELEMETRY -->
            <div
                v-if="locker.rental"
                class="grid grid-cols-2 gap-y-3 text-sm border-t pt-5"
            >
                <div class="text-gray-400">Start</div>
                <div class="text-right">
                    {{
                        new Date(locker.rental.start_time).toLocaleTimeString()
                    }}
                </div>

                <div class="text-gray-400">End</div>
                <div class="text-right">
                    {{ new Date(locker.rental.end_time).toLocaleTimeString() }}
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="border-t pt-6 space-y-3">
                <div class="text-xs uppercase tracking-wider text-gray-400">
                    Actions
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- END RENTAL -->
                    <div v-tooltip="canEndRental ? null : 'No active rental'">
                        <button
                            class="action-btn"
                            :disabled="!canEndRental"
                            @click="endRental"
                        >
                            End Rental
                        </button>
                    </div>

                    <!-- CLEAR PENALTY -->
                    <div
                        v-tooltip="canClearPenalty ? null : 'No active penalty'"
                    >
                        <button
                            class="action-btn"
                            :disabled="!canClearPenalty"
                            @click="clearPenalty"
                        >
                            Clear Penalty
                        </button>
                    </div>

                    <!-- FORCE UNLOCK -->
                    <div v-tooltip="'Force unlock hardware'">
                        <button class="action-btn" @click="forceUnlock">
                            Force Unlock
                        </button>
                    </div>

                    <!-- ENABLE/DISABLE -->
                    <div
                        v-tooltip="
                            canDisableLocker
                                ? null
                                : 'Cannot disable while rental or penalty active'
                        "
                    >
                        <button
                            v-if="!isOutOfService"
                            class="action-btn"
                            :disabled="!canDisableLocker"
                            @click="disableLocker"
                        >
                            Disable Locker
                        </button>

                        <button v-else class="action-btn" @click="enableLocker">
                            Enable Locker
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Dialog>
</template>
