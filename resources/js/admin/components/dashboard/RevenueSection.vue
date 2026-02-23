<script setup>
import { computed } from "vue";
import Card from "primevue/card";
import Chart from "primevue/chart";

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
});

const revenueChartData = computed(() => ({
    labels: props.stats?.revenue_labels ?? [],

    datasets: [
        {
            type: "line",
            label: "Rental Revenue",
            data: props.stats?.rental_revenue_values ?? [],
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
            data: props.stats?.penalty_revenue_values ?? [],
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
            data: props.stats?.total_revenue_values ?? [],
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

// ROW 2 - rental status pie chart data
const rentalStatusData = computed(() => ({
    labels: ["Active", "Ended", "Expired", "Cancelled"],

    datasets: [
        {
            data: props.stats?.rental_status_counts ?? [0, 0, 0, 0],
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
    return (props.stats.rental_status_counts || []).reduce(
        (sum, value) => sum + value,
        0
    );
});
</script>

<template>
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-4">
        <Card
            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm xl:col-span-3"
        >
            <template #content>
                <div class="px-5 py-4">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="kpi-label">Revenue Trend</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
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
                            type="bar"
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
                            <h3 class="kpi-label">Rental Status</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Todays activity
                            </p>
                        </div>

                        <i class="pi pi-chart-pie text-cyan-500 text-lg"></i>
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
</template>
