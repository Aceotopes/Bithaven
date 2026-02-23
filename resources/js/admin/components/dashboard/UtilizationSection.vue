<script setup>
import { computed } from "vue";
import Card from "primevue/card";
import Chart from "primevue/chart";
import { Chart as ChartJS } from "chart.js";

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
});

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

// ROW 3 - LOCKER UTILIZATION RATE
const utilizationData = computed(() => ({
    datasets: [
        {
            data: [
                props.stats?.occupied_lockers ?? 0,
                props.stats?.available_lockers ?? 0,
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
    const rate = props.stats?.locker_utilization_rate ?? 0;

    if (rate < 50) return "#2563eb"; // blue-600
    if (rate < 80) return "#06b6d4"; // cyan-500
    return "#ef4444"; // red-500
});

// ROW 3 - DEMAND VELOCITY SPARKLINE
const velocitySparklineData = computed(() => ({
    labels: props.stats?.velocity_last7_labels ?? [],
    datasets: [
        {
            data: props.stats?.velocity_last7_values ?? [],
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
</script>

<template>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <Card class="kpi-card bg-white dark:bg-gray-800">
            <template #content>
                <div class="kpi-body">
                    <div>
                        <h3 class="kpi-label">Locker Utilization</h3>
                        <p class="kpi-meta">Real-time capacity overview</p>
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
                                            stats.locker_utilization_rate < 50,
                                        'text-cyan-500':
                                            stats.locker_utilization_rate >=
                                                50 &&
                                            stats.locker_utilization_rate < 80,
                                        'text-red-500':
                                            stats.locker_utilization_rate >= 80,
                                    }"
                                >
                                    {{ stats.locker_utilization_rate }}%
                                </div>

                                <div class="kpi-meta uppercase tracking-wide">
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
                            <p class="kpi-meta">Rentals started today</p>
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

                        <div class="mt-2 flex items-center gap-2 text-sm">
                            <i
                                v-if="stats.velocity_change_percentage > 0"
                                class="pi pi-arrow-up text-emerald-500"
                            ></i>

                            <i
                                v-else-if="stats.velocity_change_percentage < 0"
                                class="pi pi-arrow-down text-red-500"
                            ></i>

                            <span
                                :class="{
                                    'text-emerald-600 dark:text-emerald-400':
                                        stats.velocity_change_percentage > 0,
                                    'text-red-600 dark:text-red-400':
                                        stats.velocity_change_percentage < 0,
                                    'text-gray-500 dark:text-gray-400':
                                        stats.velocity_change_percentage === 0,
                                }"
                            >
                                {{
                                    Math.abs(stats.velocity_change_percentage)
                                }}%
                            </span>

                            <span class="text-gray-500 dark:text-gray-400">
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
</template>
