<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import Card from "primevue/card";
import ProgressSpinner from "primevue/progressspinner";
import Chart from "primevue/chart";

const loading = ref(true);
const stats = ref({});
const events = ref([]);

// revenue chart data and options
const revenueChartData = computed(() => ({
    labels: stats.value.revenue_labels || [],

    datasets: [
        {
            type: "line",
            label: "Rental Revenue",
            data: stats.value.rental_revenue_values || [],
            borderColor: "#0096C7",
            backgroundColor: "#0096C7",
            tension: 0.5,
            borderWidth: 3,
            pointRadius: 2,
            fill: false,
        },
        {
            type: "line",
            label: "Penalty Revenue",
            data: stats.value.penalty_revenue_values || [],
            borderColor: "#48CAE4",
            backgroundColor: "#48CAE4",
            tension: 0.5,
            borderWidth: 3,
            pointRadius: 2,
            fill: false,
        },
        {
            type: "bar",
            label: "Total Revenue",
            data: stats.value.total_revenue_values || [],
            backgroundColor: "#CAF0F8", // soft blue
            borderRadius: 6,
            barThickness: 15,
        },
    ],
}));
const revenueChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,

    interaction: {
        mode: "index",
        intersect: false,
    },

    plugins: {
        legend: {
            position: "top",
            labels: {
                usePointStyle: true,
                padding: 16,
                color: "#6b7280",
                font: { size: 11 },
            },
        },
        tooltip: {
            backgroundColor: "#111827",
            titleColor: "#ffffff",
            bodyColor: "#d1d5db",
            padding: 10,
            cornerRadius: 8,
            callbacks: {
                label: function (context) {
                    return (
                        context.dataset.label +
                        ": ₱" +
                        context.parsed.y.toLocaleString()
                    );
                },
            },
        },
    },

    scales: {
        x: {
            grid: { display: false },
            ticks: {
                color: "#6b7280",
                font: { size: 11 },
            },
        },
        y: {
            grid: {
                color: "rgba(156,163,175,0.15)",
            },
            ticks: {
                color: "#6b7280",
                font: { size: 11 },
                callback: (value) => "₱" + value,
            },
        },
    },
}));

// rental status pie chart data
const rentalStatusData = computed(() => ({
    labels: ["Active", "Ended", "Expired", "Cancelled"],

    datasets: [
        {
            data: stats.value.rental_status_counts || [0, 0, 0, 0],
            backgroundColor: [
                "#ADE8F4", // cyan-500
                "#48CAE4", // blue-500
                "#0096C7", // blue-700
                "#0077B6", // slate-900
            ],
            borderWidth: 0,
            hoverOffset: 4,
            cutout: "70%",
        },
    ],
}));
const rentalStatusOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,

    plugins: {
        legend: {
            position: "bottom",
            labels: {
                usePointStyle: true,
                pointStyle: "circle",
                padding: 16,
                color: "#6b7280",
                font: { size: 11 },
            },
        },
        tooltip: {
            backgroundColor: "#111827",
            titleColor: "#ffffff",
            bodyColor: "#d1d5db",
            padding: 10,
            cornerRadius: 8,
        },
    },
}));
const rentalTotalToday = computed(() => {
    return (stats.value.rental_status_counts || []).reduce(
        (sum, value) => sum + value,
        0
    );
});

async function fetchDashboard() {
    try {
        const response = await axios.get("/api/admin/dashboard/summary");
        stats.value = response.data;
        events.value = response.data.recent_events || [];
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
}

onMounted(fetchDashboard);
</script>

<template>
    <div>
        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-20">
            <ProgressSpinner />
        </div>

        <div v-else class="space-y-10">
            <!-- Main KPI Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <Card class="kpi-card bg-white dark:bg-gray-800">
                    <template #content>
                        <div class="kpi-body border-l-2 border-indigo-500">
                            <div class="flex items-center justify-between">
                                <span class="kpi-label"> Students </span>

                                <i
                                    class="pi pi-users kpi-icon text-indigo-600 dark:text-indigo-400"
                                ></i>
                            </div>

                            <div class="kpi-value">
                                {{ stats.registered_students }}
                            </div>

                            <div class="kpi-meta">Registered accounts</div>
                        </div>
                    </template>
                </Card>

                <Card class="kpi-card bg-white dark:bg-gray-800">
                    <template #content>
                        <div class="kpi-body border-l-2 border-blue-500">
                            <div class="flex items-center justify-between">
                                <span class="kpi-label"> Active Rentals </span>

                                <i
                                    class="pi pi-box kpi-icon text-blue-600 dark:text-blue-400"
                                ></i>
                            </div>

                            <div class="kpi-value">
                                {{ stats.active_rentals }}
                            </div>

                            <div class="kpi-meta">Lockers currently in use</div>
                        </div>
                    </template>
                </Card>

                <Card class="kpi-card bg-white dark:bg-gray-800">
                    <template #content>
                        <div class="kpi-body border-l-2 border-emerald-500">
                            <div class="flex items-center justify-between">
                                <span class="kpi-label"> Revenue Today </span>

                                <i
                                    class="pi pi-wallet kpi-icon text-emerald-600 dark:text-emerald-400"
                                ></i>
                            </div>

                            <div class="kpi-value">
                                ₱
                                {{
                                    Number(
                                        stats.today_revenue ?? 0
                                    ).toLocaleString()
                                }}
                            </div>

                            <div class="kpi-meta">Completed sessions</div>
                        </div>
                    </template>
                </Card>

                <Card class="kpi-card bg-white dark:bg-gray-800">
                    <template #content>
                        <div class="kpi-body border-l-2 border-rose-500">
                            <div class="flex items-center justify-between">
                                <span class="kpi-label">
                                    Active Penalties
                                </span>

                                <i
                                    class="pi pi-exclamation-circle kpi-icon text-rose-600 dark:text-rose-400"
                                ></i>
                            </div>

                            <div class="kpi-value">
                                {{ stats.active_penalties }}
                            </div>

                            <div class="kpi-meta">Unresolved cases</div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Secondary Metrics -->
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-4">
                <Card
                    class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm xl:col-span-3"
                >
                    <template #content>
                        <div class="px-5 py-4">
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3
                                        class="text-sm font-semibold text-gray-800 dark:text-gray-200"
                                    >
                                        Revenue Trend
                                    </h3>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        Last 7 days
                                    </p>
                                </div>

                                <i
                                    class="pi pi-chart-line text-blue-600 dark:text-blue-400 text-lg"
                                ></i>
                            </div>

                            <!-- Chart Container -->
                            <div class="h-72">
                                <Chart
                                    type="chart"
                                    :data="revenueChartData"
                                    :options="revenueChartOptions"
                                    class="h-full"
                                />
                            </div>
                        </div>
                    </template>
                </Card>

                <Card
                    class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm xl:col-span-1"
                >
                    <template #content>
                        <div class="px-5 py-4">
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3
                                        class="text-sm font-semibold text-gray-800 dark:text-gray-200"
                                    >
                                        Rental Status
                                    </h3>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        Today’s activity
                                    </p>
                                </div>

                                <i
                                    class="pi pi-chart-pie text-cyan-500 text-lg"
                                ></i>
                            </div>

                            <!-- Doughnut -->
                            <div class="relative h-70">
                                <Chart
                                    type="doughnut"
                                    :data="rentalStatusData"
                                    :options="rentalStatusOptions"
                                    class="h-full"
                                />

                                <!-- Center Metric -->
                                <div
                                    class="absolute inset-0 flex items-center justify-center pointer-events-none"
                                >
                                    <div class="text-center h-30">
                                        <div
                                            class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            {{ rentalTotalToday }}
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide"
                                        >
                                            Total
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Recent Activity -->
            <Card class="p-6">
                <template #title> Recent Activity </template>

                <template #content>
                    <div
                        v-if="events.length === 0"
                        class="text-gray-500 dark:text-gray-400"
                    >
                        No recent activity.
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="event in events"
                            :key="event.id"
                            class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-3"
                        >
                            <div>
                                <div
                                    class="font-medium text-gray-800 dark:text-gray-100"
                                >
                                    {{ event.event_type }}
                                </div>
                                <div
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ event.message }}
                                </div>
                            </div>

                            <div class="text-xs text-gray-400">
                                {{
                                    new Date(event.created_at).toLocaleString()
                                }}
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
