<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import axios from "axios";
import Card from "primevue/card";
import Dialog from "primevue/dialog";
import LiveLockerGrid from "@/admin/components/liveoperations/LiveLockerGrid.vue";

const lockers = ref([]);
const loading = ref(true);
const selectedLocker = ref(null);
const activeFilter = ref("ALL");
let interval = null;

async function fetchLockers() {
    try {
        const res = await axios.get("/api/admin/live/lockers");
        lockers.value = res.data;
    } finally {
        loading.value = false;
    }
}

const summary = computed(() => ({
    available: lockers.value.filter((l) => l.status === "AVAILABLE").length,
    occupied: lockers.value.filter((l) => l.status === "OCCUPIED").length,
    overdue: lockers.value.filter((l) => l.penalty?.status === "ACTIVE").length,
    out: lockers.value.filter((l) => l.status === "OUT_OF_SERVICE").length,
}));

const filteredLockers = computed(() => {
    if (activeFilter.value === "ALL") return lockers.value;
    return lockers.value.filter((l) => l.status === activeFilter.value);
});

function openDetails(locker) {
    selectedLocker.value = locker;
}

onMounted(() => {
    fetchLockers();
    interval = setInterval(fetchLockers, 8000);
});

onBeforeUnmount(() => {
    if (interval) clearInterval(interval);
});
</script>

<template>
    <Card class="kpi-card bg-white dark:bg-gray-800">
        <template #content>
            <div class="kpi-body space-y-6">
                <!-- HEADER -->
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="kpi-label">Live Operations</h3>
                        <p class="kpi-meta">Real-time locker monitoring</p>
                    </div>

                    <span class="text-xs text-gray-400"> Auto-refreshing </span>
                </div>

                <!-- SUMMARY BAR -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div
                        class="rounded-lg p-3 bg-emerald-50 dark:bg-emerald-900/20"
                    >
                        <div
                            class="text-xs text-emerald-600 uppercase tracking-wide"
                        >
                            Available
                        </div>
                        <div class="text-2xl font-semibold text-emerald-700">
                            {{ summary.available }}
                        </div>
                    </div>

                    <div class="rounded-lg p-3 bg-blue-50 dark:bg-blue-900/20">
                        <div
                            class="text-xs text-blue-600 uppercase tracking-wide"
                        >
                            Occupied
                        </div>
                        <div class="text-2xl font-semibold text-blue-700">
                            {{ summary.occupied }}
                        </div>
                    </div>

                    <div
                        class="rounded-lg p-3 bg-amber-50 dark:bg-amber-900/20"
                    >
                        <div
                            class="text-xs text-amber-600 uppercase tracking-wide"
                        >
                            Overdue
                        </div>
                        <div class="text-2xl font-semibold text-amber-700">
                            {{ summary.overdue }}
                        </div>
                    </div>

                    <div class="rounded-lg p-3 bg-red-50 dark:bg-red-900/20">
                        <div
                            class="text-xs text-red-600 uppercase tracking-wide"
                        >
                            Out of Service
                        </div>
                        <div class="text-2xl font-semibold text-red-700">
                            {{ summary.out }}
                        </div>
                    </div>
                </div>

                <!-- FILTER TABS -->
                <div class="flex gap-4 text-sm">
                    <button
                        v-for="tab in [
                            'ALL',
                            'AVAILABLE',
                            'OCCUPIED',
                            'OVERDUE',
                            'OUT_OF_SERVICE',
                        ]"
                        :key="tab"
                        @click="activeFilter = tab"
                        class="px-3 py-1 rounded-full border text-xs transition"
                        :class="
                            activeFilter === tab
                                ? 'bg-cyan-500 text-white border-cyan-500'
                                : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600'
                        "
                    >
                        {{ tab }}
                    </button>
                </div>

                <!-- GRID -->
                <LiveLockerGrid
                    :lockers="filteredLockers"
                    :loading="loading"
                    @open="openDetails"
                />
            </div>
        </template>
    </Card>

    <!-- DETAILS MODAL -->
    <Dialog
        v-model:visible="selectedLocker"
        modal
        header="Locker Details"
        :style="{ width: '450px' }"
    >
        <div v-if="selectedLocker" class="space-y-4">
            <div class="text-center">
                <p class="text-3xl font-mono font-semibold">
                    Locker {{ selectedLocker.locker_number }}
                </p>
                <p class="text-sm text-gray-500">
                    Status: {{ selectedLocker.status }}
                </p>
            </div>

            <div v-if="selectedLocker.rental" class="space-y-2 text-sm">
                <p>
                    <strong>Student:</strong>
                    {{ selectedLocker.rental.student_name }}
                </p>
                <p>
                    <strong>Ends:</strong>
                    {{
                        new Date(
                            selectedLocker.rental.end_time
                        ).toLocaleString()
                    }}
                </p>
            </div>

            <div v-else class="text-gray-400 text-sm">No active rental.</div>

            <div v-if="selectedLocker.penalty" class="text-red-600 text-sm">
                Penalty Status: {{ selectedLocker.penalty.status }}
            </div>
        </div>
    </Dialog>
</template>
