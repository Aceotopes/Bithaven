<script setup>
import { computed } from "vue";

const props = defineProps({
    daily: {
        type: Array,
        default: () => [],
    },
});

/*
--------------------------------
TOTAL METRICS
--------------------------------
*/

const totalRevenue = computed(() =>
    props.daily.reduce((sum, d) => sum + Number(d.revenue || 0), 0)
);

const totalTransactions = computed(() =>
    props.daily.reduce((sum, d) => sum + Number(d.transactions || 0), 0)
);

const totalDays = computed(() => props.daily.length);

/*
--------------------------------
AVERAGE METRICS
--------------------------------
*/

const avgRevenuePerDay = computed(() => {
    if (!totalDays.value) return 0;
    return totalRevenue.value / totalDays.value;
});

const avgTransactionsPerDay = computed(() => {
    if (!totalDays.value) return 0;
    return totalTransactions.value / totalDays.value;
});

/*
--------------------------------
PEAK & LOWEST DAY
--------------------------------
*/

const peakDay = computed(() => {
    if (!props.daily.length) return null;
    return props.daily.reduce((max, d) =>
        Number(d.revenue) > Number(max.revenue) ? d : max
    );
});

const lowestDay = computed(() => {
    if (!props.daily.length) return null;
    return props.daily.reduce((min, d) =>
        Number(d.revenue) < Number(min.revenue) ? d : min
    );
});

/*
--------------------------------
REVENUE STABILITY
(standard deviation)
--------------------------------
*/

const revenueStability = computed(() => {
    if (!props.daily.length) return 0;

    const avg = avgRevenuePerDay.value;

    const variance =
        props.daily.reduce((sum, d) => {
            const diff = Number(d.revenue) - avg;

            return sum + diff * diff;
        }, 0) / props.daily.length;

    return Math.sqrt(variance);
});

/*
--------------------------------
REVENUE CONCENTRATION
(top 20% days)
--------------------------------
*/

const revenueConcentration = computed(() => {
    if (!props.daily.length) return 0;

    const sorted = [...props.daily].sort((a, b) => b.revenue - a.revenue);

    const topDays = Math.ceil(props.daily.length * 0.2);

    const topRevenue = sorted
        .slice(0, topDays)
        .reduce((sum, d) => sum + Number(d.revenue), 0);

    return (topRevenue / totalRevenue.value) * 100;
});

/*
--------------------------------
UTILIZATION SCORE
--------------------------------
*/

const activeDays = computed(
    () => props.daily.filter((d) => Number(d.transactions) > 0).length
);

const utilizationScore = computed(() => {
    if (!totalDays.value) return 0;

    return (activeDays.value / totalDays.value) * 100;
});
</script>

<template>
    <Card class="border border-gray-200 dark:border-gray-700 shadow-sm">
        <template #content>
            <div class="p-4">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3
                            class="text-lg font-semibold text-gray-800 dark:text-gray-100"
                        >
                            Revenue Insights
                        </h3>
                        <p class="text-sm text-gray-500">
                            Daily performance analytics
                        </p>
                    </div>

                    <i class="pi pi-chart-bar text-xl text-gray-400"></i>
                </div>

                <!-- Metrics Grid -->
                <div
                    class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4"
                >
                    <!-- Avg Revenue -->
                    <div
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                            >
                                Avg Revenue / Day
                            </span>

                            <i class="pi pi-wallet text-blue-500"></i>
                        </div>

                        <div
                            class="text-xl font-semibold text-gray-900 dark:text-gray-100"
                        >
                            ₱{{ avgRevenuePerDay.toFixed(2) }}
                        </div>
                    </div>

                    <!-- Avg Transactions -->
                    <div
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                            >
                                Avg Transactions
                            </span>

                            <i class="pi pi-shopping-cart text-indigo-500"></i>
                        </div>

                        <div class="text-xl font-semibold">
                            {{ avgTransactionsPerDay.toFixed(1) }}
                        </div>
                    </div>

                    <!-- Peak Day -->
                    <div
                        v-if="peakDay"
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                            >
                                Peak Revenue
                            </span>

                            <i class="pi pi-arrow-up text-green-500"></i>
                        </div>

                        <div class="text-lg font-semibold">
                            {{ peakDay.date }}
                        </div>

                        <div class="text-sm text-gray-500">
                            ₱{{ Number(peakDay.revenue).toLocaleString() }}
                        </div>
                    </div>

                    <!-- Lowest Day -->
                    <div
                        v-if="lowestDay"
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                            >
                                Lowest Revenue
                            </span>

                            <i class="pi pi-arrow-down text-red-500"></i>
                        </div>

                        <div class="text-lg font-semibold">
                            {{ lowestDay.date }}
                        </div>

                        <div class="text-sm text-gray-500">
                            ₱{{ Number(lowestDay.revenue).toLocaleString() }}
                        </div>
                    </div>

                    <!-- Revenue Stability -->
                    <div
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                            >
                                Revenue Stability
                            </span>

                            <i class="pi pi-chart-line text-purple-500"></i>
                        </div>

                        <div class="text-xl font-semibold">
                            ₱{{ revenueStability.toFixed(2) }}
                        </div>
                    </div>

                    <!-- Revenue Concentration -->
                    <div
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                            >
                                Revenue Concentration
                            </span>

                            <i class="pi pi-percentage text-emerald-500"></i>
                        </div>

                        <div class="text-xl font-semibold">
                            {{ revenueConcentration.toFixed(1) }}%
                        </div>

                        <div class="text-xs text-gray-500">top 20% days</div>
                    </div>

                    <!-- Active Days -->
                    <div
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                            >
                                Active Days
                            </span>

                            <i class="pi pi-calendar text-orange-500"></i>
                        </div>

                        <div class="text-xl font-semibold">
                            {{ activeDays }} / {{ totalDays }}
                        </div>
                    </div>

                    <!-- Utilization -->
                    <div
                        class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                            >
                                Utilization Score
                            </span>

                            <i class="pi pi-bolt text-yellow-500"></i>
                        </div>

                        <div class="text-xl font-semibold">
                            {{ utilizationScore.toFixed(1) }}%
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
