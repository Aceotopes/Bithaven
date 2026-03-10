<script setup>
import { computed } from "vue";

const props = defineProps({
    hourly: {
        type: Array,
        default: () => [],
    },
});

/* Normalize hours */
const hours = computed(() => {
    const map = {};

    props.hourly.forEach((h) => {
        map[h.hour] = Number(h.revenue);
    });

    const result = [];

    for (let i = 0; i < 24; i++) {
        result.push({
            hour: i,
            revenue: map[i] ?? 0,
        });
    }

    return result;
});

/* Maximum revenue */

const maxRevenue = computed(() => {
    return Math.max(...hours.value.map((h) => h.revenue), 1);
});

/* Heatmap color */

function getColor(value) {
    const intensity = value / maxRevenue.value;

    const opacity = 0.12 + intensity * 0.88;

    return `rgba(14,165,233,${opacity})`;
}

function formatHour(hour) {
    const period = hour >= 12 ? "PM" : "AM";
    const h = hour % 12 || 12;
    return `${h}:00 ${period}`;
}

/* Peak hour */

const peakHour = computed(() => {
    return [...hours.value].sort((a, b) => b.revenue - a.revenue)[0];
});

/* Least active */

const leastHour = computed(() => {
    return [...hours.value].sort((a, b) => a.revenue - b.revenue)[0];
});

/* Average */

const avgRevenue = computed(() => {
    const total = hours.value.reduce((s, h) => s + h.revenue, 0);

    return Math.round(total / hours.value.length);
});

/* Active hours */

const activeHours = computed(() => {
    return hours.value.filter((h) => h.revenue > 0).length;
});

/* Peak activity window (3 hours) */

const peakWindow = computed(() => {
    let best = { start: 0, revenue: 0 };

    for (let i = 0; i < 22; i++) {
        const sum =
            hours.value[i].revenue +
            hours.value[i + 1].revenue +
            hours.value[i + 2].revenue;

        if (sum > best.revenue) {
            best = {
                start: i,
                revenue: sum,
            };
        }
    }

    return best;
});
</script>

<template>
    <Card class="ui-card">
        <template #content>
            <div class="ui-card-body">
                <!-- HEADER -->

                <div class="ui-card-header">
                    <div>
                        <h3 class="ui-card-title">Hourly Revenue Heatmap</h3>

                        <p class="ui-card-subtitle">
                            Locker activity distribution
                        </p>
                    </div>

                    <i class="pi pi-clock text-cyan-500 text-xl"></i>
                </div>

                <!-- HEATMAP -->

                <div class="grid grid-cols-6 gap-2 mt-2">
                    <div
                        v-for="h in hours"
                        :key="h.hour"
                        class="rounded-lg p-3 text-center transition-all duration-200 hover:scale-105"
                        :style="{ backgroundColor: getColor(h.revenue) }"
                    >
                        <div class="text-[11px] text-gray-500">
                            {{ formatHour(h.hour) }}
                        </div>

                        <div class="font-semibold text-sm">
                            ₱{{ h.revenue }}
                        </div>
                    </div>
                </div>

                <!-- LEGEND -->

                <div
                    class="flex items-center justify-between mt-4 text-xs text-gray-500"
                >
                    <div class="flex items-center gap-2">
                        <span>Low</span>

                        <div class="flex gap-1">
                            <div class="w-4 h-4 rounded bg-cyan-100"></div>
                            <div class="w-4 h-4 rounded bg-cyan-300"></div>
                            <div class="w-4 h-4 rounded bg-cyan-500"></div>
                        </div>

                        <span>High</span>
                    </div>
                </div>

                <hr class="my-4 border-gray-200 dark:border-gray-700" />

                <!-- ANALYTICS GRID -->

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-1 text-sm"
                >
                    <!-- Peak Hour -->
                    <div
                        class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                    >
                        <div
                            class="flex items-center gap-2 text-gray-500 text-xs mb-1"
                        >
                            <i class="pi pi-clock text-gray-400"></i>
                            <span>Peak Hour</span>
                        </div>

                        <div class="text-lg font-semibold">
                            {{ formatHour(peakHour.hour) }}
                        </div>

                        <div class="text-xs text-gray-500">
                            ₱{{ peakHour.revenue.toLocaleString() }}
                        </div>
                    </div>

                    <!-- Least Active Hour -->
                    <div
                        class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                    >
                        <div
                            class="flex items-center gap-2 text-gray-500 text-xs mb-1"
                        >
                            <i class="pi pi-moon text-gray-400"></i>
                            <span>Least Active Hour</span>
                        </div>

                        <div class="text-lg font-semibold">
                            {{ formatHour(leastHour.hour) }}
                        </div>

                        <div class="text-xs text-gray-500">
                            ₱{{ leastHour.revenue.toLocaleString() }}
                        </div>
                    </div>

                    <!-- Peak Activity Window -->
                    <div
                        class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                    >
                        <div
                            class="flex items-center gap-2 text-gray-500 text-xs mb-1"
                        >
                            <i class="pi pi-bolt text-gray-400"></i>
                            <span>Peak Activity Window</span>
                        </div>

                        <div class="text-sm font-semibold">
                            {{ formatHour(peakWindow.start) }} –
                            {{ formatHour(peakWindow.start + 2) }}
                        </div>

                        <div class="text-xs text-gray-500">
                            ₱{{ peakWindow.revenue.toLocaleString() }} revenue
                        </div>
                    </div>

                    <!-- Average Revenue per Hour -->
                    <div
                        class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                    >
                        <div
                            class="flex items-center gap-2 text-gray-500 text-xs mb-1"
                        >
                            <i class="pi pi-chart-line text-gray-400"></i>
                            <span>Avg Revenue / Hour</span>
                        </div>

                        <div class="text-lg font-semibold">
                            ₱{{ avgRevenue.toLocaleString() }}
                        </div>
                    </div>

                    <!-- Active Hours -->
                    <div
                        class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                    >
                        <div
                            class="flex items-center gap-2 text-gray-500 text-xs mb-1"
                        >
                            <i class="pi pi-clock text-gray-400"></i>
                            <span>Active Hours</span>
                        </div>

                        <div class="text-lg font-semibold">
                            {{ activeHours }} / 24
                        </div>

                        <div class="text-xs text-gray-500">locker usage</div>
                    </div>

                    <!-- Hourly Activity -->
                    <div
                        class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
                    >
                        <div
                            class="flex items-center gap-2 text-gray-500 text-xs mb-1"
                        >
                            <i class="pi pi-percentage text-gray-400"></i>
                            <span>Hourly Activity</span>
                        </div>

                        <div class="text-lg font-semibold">
                            {{ Math.round((activeHours / 24) * 100) }}%
                        </div>

                        <div class="text-xs text-gray-500">usage rate</div>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
