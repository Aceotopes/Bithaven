<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import axios from "axios";
import Card from "primevue/card";
import Dialog from "primevue/dialog";
import LiveLockerGrid from "@/admin/components/liveoperations/LiveLockerGrid.vue";
import LockerControlModal from "@/admin/components/liveoperations/LockerControlModal.vue";

// FOR LIVE LOCKER MONITORING AND CONTROL
const lockers = ref([]);
const loading = ref(true);
const selectedLocker = ref(null);
const activeFilter = ref("ALL");
let interval = null;

async function fetchLockers() {
    try {
        const res = await axios.get("/admin/live/lockers");
        lockers.value = res.data;
    } finally {
        loading.value = false;
    }
}

const summary = computed(() => {
    const counts = {
        available: 0,
        occupied: 0,
        overdue: 0,
        out: 0,
    };

    lockers.value.forEach((locker) => {
        if (locker.status === "OUT_OF_SERVICE") {
            counts.out++;
        } else if (locker.penalty?.status === "ACTIVE") {
            counts.overdue++;
        } else if (locker.rental) {
            counts.occupied++;
        } else {
            counts.available++;
        }
    });

    return counts;
});

const filteredLockers = computed(() => {
    if (activeFilter.value === "ALL") return lockers.value;

    return lockers.value.filter((locker) => {
        if (activeFilter.value === "OUT_OF_SERVICE")
            return locker.status === "OUT_OF_SERVICE";

        if (activeFilter.value === "OVERDUE")
            return locker.penalty?.status === "ACTIVE";

        if (activeFilter.value === "OCCUPIED")
            return locker.rental && !locker.penalty;

        if (activeFilter.value === "AVAILABLE")
            return !locker.rental && locker.status !== "OUT_OF_SERVICE";

        return true;
    });
});

// FOR LOCKER CONTROL MODAL
const showModal = ref(false);

function openLocker(locker) {
    selectedLocker.value = locker;
    showModal.value = true;
}

async function refreshLockers() {
    await fetchLockers();
    selectedLocker.value = lockers.value.find(
        (l) => l.id === selectedLocker.value.id
    );
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
                    @open="openLocker"
                />
            </div>
        </template>
    </Card>

    <!-- DETAILS MODAL -->
    <LockerControlModal
        v-model:visible="showModal"
        :locker="selectedLocker"
        @refresh="refreshLockers"
    />
    <ConfirmDialog />
</template>
