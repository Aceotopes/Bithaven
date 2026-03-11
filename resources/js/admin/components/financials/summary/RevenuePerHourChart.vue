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
    <Card class="border border-gray-200 dark:border-gray-700 shadow-sm">
        <template #content>
            <div class="p-4 space-y-5">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h3
                            class="text-base font-semibold text-gray-800 dark:text-gray-100"
                        >
                            Hourly Revenue
                        </h3>

                        <p class="text-xs text-gray-500">
                            Revenue activity throughout the day
                        </p>
                    </div>

                    <i class="pi pi-clock text-cyan-500 text-lg"></i>
                </div>

                <!-- Heatmap -->

                <div class="grid grid-cols-12 gap-1">
                    <div
                        v-for="h in hours"
                        :key="h.hour"
                        class="rounded-md p-2 text-center transition-transform hover:scale-105"
                        :style="{ backgroundColor: getColor(h.revenue) }"
                    >
                        <div class="text-[10px] text-gray-500">
                            {{ formatHour(h.hour) }}
                        </div>

                        <div class="text-xs font-semibold">
                            ₱{{ h.revenue }}
                        </div>
                    </div>
                </div>

                <!-- Legend -->

                <div
                    class="flex items-center justify-between text-xs text-gray-500"
                >
                    <div class="flex items-center gap-2">
                        <span>Low</span>

                        <div class="flex gap-1">
                            <div class="w-3 h-3 rounded bg-cyan-100"></div>
                            <div class="w-3 h-3 rounded bg-cyan-300"></div>
                            <div class="w-3 h-3 rounded bg-cyan-500"></div>
                        </div>

                        <span>High</span>
                    </div>
                </div>

                <!-- Divider -->

                <div
                    class="border-t border-gray-200 dark:border-gray-700"
                ></div>

                <!-- Analytics -->

                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                    <!-- Peak Hour -->

                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 mb-1"
                        >
                            <i class="pi pi-clock text-gray-400"></i>
                            Peak Hour
                        </div>

                        <div class="font-semibold">
                            {{ formatHour(peakHour.hour) }}
                        </div>

                        <div class="text-xs text-gray-500">
                            ₱{{ peakHour.revenue.toLocaleString() }}
                        </div>
                    </div>

                    <!-- Least Active -->

                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 mb-1"
                        >
                            <i class="pi pi-moon text-gray-400"></i>
                            Least Active
                        </div>

                        <div class="font-semibold">
                            {{ formatHour(leastHour.hour) }}
                        </div>

                        <div class="text-xs text-gray-500">
                            ₱{{ leastHour.revenue.toLocaleString() }}
                        </div>
                    </div>

                    <!-- Peak Window -->

                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 mb-1"
                        >
                            <i class="pi pi-bolt text-gray-400"></i>
                            Peak Window
                        </div>

                        <div class="font-semibold text-xs">
                            {{ formatHour(peakWindow.start) }} –
                            {{ formatHour(peakWindow.start + 2) }}
                        </div>

                        <div class="text-xs text-gray-500">
                            ₱{{ peakWindow.revenue.toLocaleString() }}
                        </div>
                    </div>

                    <!-- Avg / Hour -->

                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 mb-1"
                        >
                            <i class="pi pi-chart-line text-gray-400"></i>
                            Avg / Hour
                        </div>

                        <div class="font-semibold">
                            ₱{{ avgRevenue.toLocaleString() }}
                        </div>
                    </div>

                    <!-- Active Hours -->

                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 mb-1"
                        >
                            <i class="pi pi-clock text-gray-400"></i>
                            Active Hours
                        </div>

                        <div class="font-semibold">{{ activeHours }} / 24</div>
                    </div>

                    <!-- Activity Rate -->

                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 mb-1"
                        >
                            <i class="pi pi-percentage text-gray-400"></i>
                            Activity Rate
                        </div>

                        <div class="font-semibold">
                            {{ Math.round((activeHours / 24) * 100) }}%
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
