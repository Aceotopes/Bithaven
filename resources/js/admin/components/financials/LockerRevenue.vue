<script setup>
import { ref, onMounted, watch, computed } from "vue";
import axios from "axios";

import LockerRevenueTable from "@/admin/components/financials/locker/LockerRevenueTable.vue";

const props = defineProps({
    filters: Object,
});

const lockers = ref([]);
const loading = ref(false);

const topLocker = computed(() => {
    if (!lockers.value.length) return null;

    return lockers.value.reduce((max, locker) =>
        Number(locker.total_revenue) > Number(max.total_revenue) ? locker : max
    );
});

const mostUsedLocker = computed(() => {
    if (!lockers.value.length) return null;

    return lockers.value.reduce((max, locker) =>
        Number(locker.rentals) > Number(max.rentals) ? locker : max
    );
});

const penaltyLocker = computed(() => {
    if (!lockers.value.length) return null;

    return lockers.value.reduce((max, locker) =>
        Number(locker.penalties) > Number(max.penalties) ? locker : max
    );
});

const lowestLocker = computed(() => {
    if (!lockers.value.length) return null;

    return lockers.value.reduce((min, locker) =>
        Number(locker.total_revenue) < Number(min.total_revenue) ? locker : min
    );
});

function formatDate(date) {
    if (!date) return null;

    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, "0");
    const d = String(date.getDate()).padStart(2, "0");

    return `${y}-${m}-${d}`;
}

async function fetchLockers() {
    loading.value = true;

    try {
        const res = await axios.get("/admin/financials/locker-revenue", {
            params: {
                start_date: formatDate(props.filters?.start_date),
                end_date: formatDate(props.filters?.end_date),
            },
        });

        lockers.value = res.data.lockers;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

const chartData = computed(() => {
    const sorted = [...lockers.value]
        .sort((a, b) => Number(b.total_revenue) - Number(a.total_revenue))
        .slice(0, 10);

    return {
        labels: sorted.map((l) => "L" + l.resolved_locker_id),

        datasets: [
            {
                label: "Revenue",
                data: sorted.map((l) => Number(l.total_revenue)),
                backgroundColor: "#06b6d4",
                borderRadius: 6,
                barThickness: 18,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,

    plugins: {
        legend: {
            display: false,
        },

        tooltip: {
            callbacks: {
                label: (context) => "₱" + context.parsed.x.toLocaleString(),
            },
        },
    },

    indexAxis: "y",

    scales: {
        x: {
            beginAtZero: true,
            ticks: {
                callback: (value) => "₱" + value,
            },
            grid: {
                color: "rgba(0,0,0,0.05)",
            },
        },

        y: {
            grid: {
                display: false,
            },
        },
    },
};

onMounted(fetchLockers);

watch(
    () => props.filters,
    () => fetchLockers(),
    { deep: true }
);
</script>

<template>
    <div class="space-y-4">
        <!-- KPI ROW -->

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="kpi-card">
                <div class="kpi-body">
                    <span class="kpi-label">Top Earning Locker</span>
                    <div class="kpi-value">
                        L{{ topLocker?.resolved_locker_id }}
                    </div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-body">
                    <span class="kpi-label">Most Used Locker</span>
                    <div class="kpi-value">
                        L{{ mostUsedLocker?.resolved_locker_id }}
                    </div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-body">
                    <span class="kpi-label">Highest Penalty Locker</span>
                    <div class="kpi-value">
                        L{{ penaltyLocker?.resolved_locker_id }}
                    </div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-body">
                    <span class="kpi-label">Lowest Revenue Locker</span>
                    <div class="kpi-value">
                        L{{ lowestLocker?.resolved_locker_id }}
                    </div>
                </div>
            </div>
        </div>
        <Card class="ui-card">
            <template #content>
                <div class="ui-card-body">
                    <div class="ui-card-header">
                        <div>
                            <h3 class="ui-card-title">
                                Locker Revenue Ranking
                            </h3>
                            <p class="ui-card-subtitle">
                                Highest earning lockers
                            </p>
                        </div>

                        <i class="pi pi-chart-bar text-cyan-500"></i>
                    </div>

                    <div class="h-72">
                        <Chart
                            type="bar"
                            :data="chartData"
                            :options="chartOptions"
                            class="h-full w-full"
                        />
                    </div>
                </div>
            </template>
        </Card>

        <!-- TABLE -->

        <LockerRevenueTable :lockers="lockers" />
    </div>
</template>
