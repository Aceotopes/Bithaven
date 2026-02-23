<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import Card from "primevue/card";
import ProgressSpinner from "primevue/progressspinner";
import Chart from "primevue/chart";
import { Chart as ChartJS } from "chart.js";
import Dialog from "primevue/dialog";

const loading = ref(true);
const stats = ref({});
const events = ref([]);

ChartJS.register({
    id: "semiCircleTicks",
    afterDraw(chart, args, options) {
        if (!options || !options.enabled) return;

        const { ctx } = chart;
        const meta = chart.getDatasetMeta(0);

        if (!meta || !meta.data || !meta.data.length) return;

        const arc = meta.data[0];
        const centerX = arc.x;
        const centerY = arc.y;
        const outerRadius = arc.outerRadius;

        ctx.save();
        ctx.strokeStyle = "#cbd5e1";
        ctx.lineWidth = 1;

        const tickCount = 10;

        for (let i = 0; i <= tickCount; i++) {
            const angle = -Math.PI / 2 + (i / tickCount) * Math.PI;

            const startX = centerX + (outerRadius - 4) * Math.cos(angle);
            const startY = centerY + (outerRadius - 4) * Math.sin(angle);

            const endX = centerX + (outerRadius + 2) * Math.cos(angle);
            const endY = centerY + (outerRadius + 2) * Math.sin(angle);

            ctx.beginPath();
            ctx.moveTo(startX, startY);
            ctx.lineTo(endX, endY);
            ctx.stroke();
        }

        ctx.restore();
    },
});

// ROW 2 - revenue chart data and options
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

// ROW 2 - rental status pie chart data
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

// ROW 3 - LOCKER UTILIZATION RATE
const utilizationData = computed(() => ({
    datasets: [
        {
            data: [
                stats.value.occupied_lockers || 0,
                stats.value.available_lockers || 0,
            ],
            backgroundColor: [utilizationColor.value, "#e5e7eb"],
            borderWidth: 0,
        },
    ],
}));
const utilizationOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    rotation: -90,
    circumference: 180,
    cutout: "70%",
    animation: {
        animateRotate: true,
        duration: 1200,
        easing: "easeOutQuart",
    },
    plugins: {
        legend: { display: false },
        tooltip: { enabled: false },
        semiCircleTicks: {
            enabled: true,
        },
    },
}));
const utilizationColor = computed(() => {
    const rate = stats.value.locker_utilization_rate || 0;

    if (rate < 50) return "#2563eb"; // blue-600
    if (rate < 80) return "#06b6d4"; // cyan-500
    return "#ef4444"; // red-500
});

// ROW 3 - DEMAND VELOCITY SPARKLINE
const velocitySparklineData = computed(() => ({
    labels: stats.value.velocity_last7_labels || [],
    datasets: [
        {
            data: stats.value.velocity_last7_values || [],
            borderColor: "#06b6d4", // cyan-500
            tension: 0.5,
            borderWidth: 2,
            pointRadius: 0,
            fill: false,
        },
    ],
}));
const velocitySparklineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: { enabled: false },
    },
    scales: {
        x: { display: false },
        y: { display: false },
    },
};

// ROW 4 - EVENT LOG
const eventConfig = {
    RENTAL_PAID: {
        label: "Rental Started",
        icon: "pi pi-check-circle",
        color: "cyan",
    },
    RENTAL_ENDED: {
        label: "Rental Ended",
        icon: "pi pi-stop-circle",
        color: "emerald",
    },
    RENTAL_EXPIRED: {
        label: "Rental Expired",
        icon: "pi pi-clock",
        color: "amber",
    },
    PENALTY_PAID: {
        label: "Penalty Settled",
        icon: "pi pi-credit-card",
        color: "blue",
    },
};
const selectedFilter = ref("ALL");
const highlightedEvents = ref(new Set());
const selectedEvent = ref(null);
const filteredEvents = computed(() => {
    if (selectedFilter.value === "ALL") return events.value;

    if (selectedFilter.value === "RENTAL") {
        return events.value.filter((e) =>
            ["RENTAL_PAID", "RENTAL_ENDED", "RENTAL_EXPIRED"].includes(
                e.event_type
            )
        );
    }

    if (selectedFilter.value === "PENALTY") {
        return events.value.filter((e) => e.event_type === "PENALTY_PAID");
    }

    return events.value;
});
function getInitials(student) {
    if (!student) return "?";
    return (
        student.first_name?.charAt(0)?.toUpperCase() +
        student.last_name?.charAt(0)?.toUpperCase()
    );
}

function formatTime(timestamp) {
    const diff = (Date.now() - new Date(timestamp)) / 1000;

    if (diff < 60) return "Just now";
    if (diff < 3600) return Math.floor(diff / 60) + "m ago";
    if (diff < 86400) return Math.floor(diff / 3600) + "h ago";
    return Math.floor(diff / 86400) + "d ago";
}

function formatDate(date) {
    return new Date(date).toLocaleString();
}

async function fetchDashboard() {
    try {
        const response = await axios.get("/api/admin/dashboard/summary");
        stats.value = response.data;
        events.value = response.data.recent_events || [];

        const previousIds = new Set(events.value.map((e) => e.id));

        events.value = response.data.recent_events || [];

        events.value.forEach((event) => {
            if (!previousIds.has(event.id)) {
                highlightedEvents.value.add(event.id);

                setTimeout(() => {
                    highlightedEvents.value.delete(event.id);
                }, 7000); // 7 seconds
            }
        });
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
                                    <h3 class="kpi-label">Revenue Trend</h3>
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

            <!-- Tertiary Metrics -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <Card class="kpi-card bg-white dark:bg-gray-800">
                    <template #content>
                        <div class="kpi-body">
                            <div>
                                <h3 class="kpi-label">Locker Utilization</h3>
                                <p class="kpi-meta">
                                    Real-time capacity overview
                                </p>
                            </div>

                            <div class="relative h-34 mt-2">
                                <Chart
                                    type="doughnut"
                                    :data="utilizationData"
                                    :options="utilizationOptions"
                                    class="h-full"
                                />

                                <div
                                    class="absolute inset-0 flex items-end justify-center pb-8 pointer-events-none"
                                >
                                    <div class="text-center">
                                        <div
                                            class="text-4xl font-semibold tracking-tight"
                                            :class="{
                                                'text-blue-600':
                                                    stats.locker_utilization_rate <
                                                    50,
                                                'text-cyan-500':
                                                    stats.locker_utilization_rate >=
                                                        50 &&
                                                    stats.locker_utilization_rate <
                                                        80,
                                                'text-red-500':
                                                    stats.locker_utilization_rate >=
                                                    80,
                                            }"
                                        >
                                            {{ stats.locker_utilization_rate }}%
                                        </div>

                                        <div
                                            class="kpi-meta uppercase tracking-wide"
                                        >
                                            Utilized
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 mt-4">
                                <!-- Occupied -->
                                <div
                                    class="flex flex-col items-center justify-center border-r border-gray-300 dark:border-gray-700 px-3"
                                >
                                    <div
                                        class="flex items-center gap-1 text-gray-500 dark:text-gray-400 text-sm uppercase tracking-wide"
                                    >
                                        Occupied
                                    </div>
                                    <div
                                        class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-1"
                                    >
                                        {{ stats.occupied_lockers }}
                                    </div>
                                </div>

                                <!-- Available -->
                                <div
                                    class="flex flex-col items-center justify-center border-r border-gray-300 dark:border-gray-700 px-3"
                                >
                                    <div
                                        class="flex items-center gap-1 text-gray-500 dark:text-gray-400 text-sm uppercase tracking-wide"
                                    >
                                        Available
                                    </div>
                                    <div
                                        class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-1"
                                    >
                                        {{ stats.available_lockers }}
                                    </div>
                                </div>

                                <!-- Out of Service -->
                                <div
                                    class="flex flex-col items-center justify-center px-3"
                                >
                                    <div
                                        class="flex items-center gap-1 text-gray-500 dark:text-gray-400 text-sm uppercase tracking-wide"
                                    >
                                        Out of Service
                                    </div>
                                    <div
                                        class="text-xl font-semibold text-gray-900 dark:text-gray-100 mt-1"
                                    >
                                        {{ stats.out_of_service_lockers }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="kpi-card bg-white dark:bg-gray-800">
                    <template #content>
                        <div class="kpi-body flex flex-col h-full">
                            <!-- Header -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="kpi-label">Demand Velocity</h3>
                                    <p class="kpi-meta">
                                        Rentals started today
                                    </p>
                                </div>
                                <i
                                    class="pi pi-bolt text-cyan-600 dark:text-cyan-400"
                                ></i>
                            </div>

                            <!-- Main Metric -->
                            <div class="mt-4">
                                <div
                                    class="text-4xl font-semibold text-gray-900 dark:text-gray-100 tracking-tight"
                                >
                                    {{ stats.today_rental_velocity }}
                                </div>

                                <div
                                    class="mt-2 flex items-center gap-2 text-sm"
                                >
                                    <i
                                        v-if="
                                            stats.velocity_change_percentage > 0
                                        "
                                        class="pi pi-arrow-up text-emerald-500"
                                    ></i>

                                    <i
                                        v-else-if="
                                            stats.velocity_change_percentage < 0
                                        "
                                        class="pi pi-arrow-down text-red-500"
                                    ></i>

                                    <span
                                        :class="{
                                            'text-emerald-600 dark:text-emerald-400':
                                                stats.velocity_change_percentage >
                                                0,
                                            'text-red-600 dark:text-red-400':
                                                stats.velocity_change_percentage <
                                                0,
                                            'text-gray-500 dark:text-gray-400':
                                                stats.velocity_change_percentage ===
                                                0,
                                        }"
                                    >
                                        {{
                                            Math.abs(
                                                stats.velocity_change_percentage
                                            )
                                        }}%
                                    </span>

                                    <span
                                        class="text-gray-500 dark:text-gray-400"
                                    >
                                        vs yesterday
                                    </span>
                                </div>
                            </div>

                            <!-- Sparkline -->
                            <div class="mt-6 h-20">
                                <Chart
                                    type="line"
                                    :data="velocitySparklineData"
                                    :options="velocitySparklineOptions"
                                    class="h-full"
                                />
                            </div>

                            <!-- Footer Meta -->
                            <div
                                class="mt-auto pt-4 border-t border-gray-200 dark:border-gray-600/40 flex justify-between text-xs text-gray-500 dark:text-gray-400"
                            >
                                <span> 7-day trend </span>
                                <span> Live activity </span>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Recent Activity -->
            <Card class="kpi-card bg-white dark:bg-gray-800">
                <template #content>
                    <div class="kpi-body">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="kpi-label">Today's Kiosk Events</h3>
                                <p class="kpi-meta">
                                    Live operational activity
                                </p>
                            </div>

                            <router-link
                                to="/admin/logs"
                                class="text-xs text-cyan-600 dark:text-cyan-400 hover:underline"
                            >
                                View all →
                            </router-link>
                        </div>

                        <!-- Filters -->
                        <div class="flex gap-2 mb-4">
                            <button
                                v-for="type in ['ALL', 'RENTAL', 'PENALTY']"
                                :key="type"
                                @click="selectedFilter = type"
                                class="text-xs px-3 py-1 rounded-md border transition"
                                :class="{
                                    'bg-cyan-600 text-white border-cyan-600':
                                        selectedFilter === type,
                                    'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300':
                                        selectedFilter !== type,
                                }"
                            >
                                {{ type }}
                            </button>
                        </div>

                        <!-- Scrollable Container -->
                        <div class="max-h-[40vh] overflow-y-auto pr-2">
                            <!-- Empty State -->
                            <div
                                v-if="filteredEvents.length === 0"
                                class="text-center py-10 text-gray-400 dark:text-gray-500"
                            >
                                <i class="pi pi-inbox text-3xl mb-2"></i>
                                <div class="text-sm">
                                    No kiosk activity today
                                </div>
                            </div>

                            <!-- Events -->
                            <div
                                v-for="event in filteredEvents"
                                :key="event.id"
                                @click="selectedEvent = event"
                                class="flex items-center gap-3 py-2 border-b border-gray-200 dark:border-gray-700 last:border-0 cursor-pointer transition-colors"
                                :class="{
                                    'bg-cyan-50 dark:bg-cyan-900/20':
                                        highlightedEvents.has(event.id),
                                    'hover:bg-gray-50 dark:hover:bg-gray-800/40': true,
                                }"
                            >
                                <!-- Avatar -->
                                <div
                                    class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0"
                                >
                                    <img
                                        v-if="event.student?.photo_url"
                                        :src="event.student.photo_url"
                                        class="w-full h-full object-cover"
                                    />

                                    <div
                                        v-else
                                        class="w-full h-full flex items-center justify-center bg-cyan-500 text-white text-[10px] font-semibold"
                                    >
                                        {{ getInitials(event.student) }}
                                    </div>
                                </div>

                                <!-- Text -->
                                <div class="flex-1 min-w-0">
                                    <div
                                        class="flex items-center gap-2 text-[13px] text-gray-900 dark:text-gray-100"
                                    >
                                        <i
                                            :class="[
                                                eventConfig[event.event_type]
                                                    ?.icon,
                                                'text-xs',
                                                eventConfig[event.event_type]
                                                    ?.color === 'cyan' &&
                                                    'text-cyan-600 dark:text-cyan-400',
                                                eventConfig[event.event_type]
                                                    ?.color === 'emerald' &&
                                                    'text-emerald-600 dark:text-emerald-400',
                                                eventConfig[event.event_type]
                                                    ?.color === 'amber' &&
                                                    'text-amber-600 dark:text-amber-400',
                                                eventConfig[event.event_type]
                                                    ?.color === 'blue' &&
                                                    'text-blue-600 dark:text-blue-400',
                                            ]"
                                        ></i>

                                        <span class="font-medium truncate">
                                            {{ event.student?.first_name }}
                                            {{ event.student?.last_name }}
                                        </span>

                                        <span class="text-gray-400">•</span>

                                        <span
                                            class="truncate text-gray-600 dark:text-gray-300"
                                        >
                                            {{
                                                eventConfig[event.event_type]
                                                    ?.label
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                    >
                                        Locker {{ event.locker?.locker_number }}
                                    </div>
                                </div>

                                <!-- Time -->
                                <div
                                    class="text-[11px] text-gray-400 dark:text-gray-500 whitespace-nowrap"
                                >
                                    {{ formatTime(event.created_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <Dialog
                        v-model:visible="selectedEvent"
                        modal
                        header="Event Details"
                        class="w-96"
                    >
                        <div v-if="selectedEvent">
                            <div class="mb-2 font-semibold">
                                {{ selectedEvent.student?.first_name }}
                                {{ selectedEvent.student?.last_name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{
                                    eventConfig[selectedEvent.event_type]?.label
                                }}
                            </div>
                            <div
                                class="mt-3 text-xs text-gray-600 dark:text-gray-300"
                            >
                                Full timestamp:
                                {{ formatDate(selectedEvent.created_at) }}
                            </div>
                        </div>
                    </Dialog>
                </template>
            </Card>
        </div>
    </div>
</template>
