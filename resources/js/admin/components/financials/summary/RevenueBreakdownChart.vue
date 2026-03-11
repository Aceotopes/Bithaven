<script setup>
import { computed } from "vue";
import MomentumIndicator from "@/admin/components/financials/summary/MomentumIndicator.vue";
const props = defineProps({
    daily: {
        type: Array,
        default: () => [],
    },
    momentum: Number,
});

/*
|--------------------------------------------------------------------------
| CORE METRICS
|--------------------------------------------------------------------------
*/

const rentalRevenue = computed(() =>
    props.daily.reduce((sum, d) => sum + Number(d.rental || 0), 0)
);

const penaltyRevenue = computed(() =>
    props.daily.reduce((sum, d) => sum + Number(d.penalty || 0), 0)
);

const totalRevenue = computed(() => rentalRevenue.value + penaltyRevenue.value);

const totalTransactions = computed(() =>
    props.daily.reduce((sum, d) => sum + Number(d.transactions || 0), 0)
);

/*
|--------------------------------------------------------------------------
| PERCENTAGES
|--------------------------------------------------------------------------
*/

const rentalPercent = computed(() =>
    totalRevenue.value
        ? ((rentalRevenue.value / totalRevenue.value) * 100).toFixed(1)
        : 0
);

const penaltyPercent = computed(() =>
    totalRevenue.value
        ? ((penaltyRevenue.value / totalRevenue.value) * 100).toFixed(1)
        : 0
);

/*
|--------------------------------------------------------------------------
| DOMINANT SOURCE
|--------------------------------------------------------------------------
*/

const dominantSource = computed(() => {
    return rentalRevenue.value >= penaltyRevenue.value ? "Rental" : "Penalty";
});

/*
|--------------------------------------------------------------------------
| REVENUE RATIO
|--------------------------------------------------------------------------
*/

const revenueRatio = computed(() => {
    if (!penaltyRevenue.value) return "∞";

    return (rentalRevenue.value / penaltyRevenue.value).toFixed(1);
});

/*
|--------------------------------------------------------------------------
| PENALTY DEPENDENCE
|--------------------------------------------------------------------------
*/

const penaltyDependence = computed(() => {
    const p = penaltyPercent.value;

    if (p < 10) return "Low";
    if (p < 30) return "Normal";

    return "High";
});

/*
|--------------------------------------------------------------------------
| AVERAGE REVENUE PER TRANSACTION
|--------------------------------------------------------------------------
*/

const avgRevenuePerTxn = computed(() => {
    if (!totalTransactions.value) return 0;

    return (totalRevenue.value / totalTransactions.value).toFixed(2);
});

/*
|--------------------------------------------------------------------------
| CHART DATA
|--------------------------------------------------------------------------
*/

const breakdownData = computed(() => ({
    labels: ["Rental", "Penalty"],
    datasets: [
        {
            data: [rentalRevenue.value, penaltyRevenue.value],
            backgroundColor: ["#48CAE4", "#FFB703"],
            borderWidth: 0,
            hoverOffset: 10,
        },
    ],
}));

const centerTextPlugin = {
    id: "centerText",

    beforeDraw(chart) {
        const { ctx, width, height } = chart;
        ctx.restore();

        const total = totalRevenue.value.toLocaleString();

        ctx.textAlign = "center";
        ctx.textBaseline = "middle";

        // Total revenue
        ctx.font = "bold 22px sans-serif";
        ctx.fillStyle = "#111827";
        ctx.fillText(`₱${total}`, width / 2, height / 2 - 10);

        // Label
        ctx.font = "12px sans-serif";
        ctx.fillStyle = "#6b7280";
        ctx.fillText("Total Revenue", width / 2, height / 2 + 12);

        ctx.save();
    },
};

const chartOptions = {
    maintainAspectRatio: false,
    cutout: "65%",

    plugins: {
        legend: {
            display: false,
        },
    },

    animation: {
        animateRotate: true,
        animateScale: true,
    },
};
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
                            Revenue Breakdown
                        </h3>
                        <p class="text-xs text-gray-500">
                            Rental vs Penalty income distribution
                        </p>
                    </div>

                    <i class="pi pi-chart-pie text-amber-500 text-lg"></i>
                </div>

                <!-- Chart + Revenue Sources -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
                    <!-- Donut Chart -->
                    <div class="flex justify-center">
                        <div class="h-44 w-44">
                            <Chart
                                type="doughnut"
                                :data="breakdownData"
                                :options="chartOptions"
                                :plugins="[centerTextPlugin]"
                                class="w-full h-full"
                            />
                        </div>
                    </div>

                    <!-- Revenue Sources -->
                    <div class="space-y-4">
                        <!-- Rental -->
                        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div
                                class="flex items-center justify-between text-xs text-gray-500 mb-1"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-3 h-3 rounded-full bg-cyan-400"
                                    ></span>
                                    Rental Revenue
                                </div>
                                <span>{{ rentalPercent }}%</span>
                            </div>

                            <div class="text-lg font-semibold text-cyan-600">
                                ₱{{ rentalRevenue.toLocaleString() }}
                            </div>
                        </div>

                        <!-- Penalty -->
                        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div
                                class="flex items-center justify-between text-xs text-gray-500 mb-1"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-3 h-3 rounded-full bg-amber-400"
                                    ></span>
                                    Penalty Revenue
                                </div>
                                <span>{{ penaltyPercent }}%</span>
                            </div>

                            <div class="text-lg font-semibold text-amber-500">
                                ₱{{ penaltyRevenue.toLocaleString() }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div
                    class="border-t border-gray-200 dark:border-gray-700"
                ></div>

                <!-- Analytics Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                    <!-- Total Revenue -->
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 mb-1"
                        >
                            <i class="pi pi-wallet text-gray-400"></i>
                            <span>Total Revenue</span>
                        </div>

                        <div
                            class="font-semibold text-gray-900 dark:text-gray-100"
                        >
                            ₱{{ totalRevenue.toLocaleString() }}
                        </div>
                    </div>

                    <!-- Transactions -->
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 mb-1"
                        >
                            <i class="pi pi-shopping-cart text-gray-400"></i>
                            <span>Transactions</span>
                        </div>

                        <div class="font-semibold">
                            {{ totalTransactions }}
                        </div>
                    </div>

                    <!-- Primary Source -->
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 mb-1"
                        >
                            <i class="pi pi-star text-gray-400"></i>
                            <span>Primary Source</span>
                        </div>

                        <div class="font-semibold">
                            {{ dominantSource }}
                        </div>
                    </div>

                    <!-- Revenue Ratio -->
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 mb-1"
                        >
                            <i class="pi pi-chart-bar text-gray-400"></i>
                            <span>Rental : Penalty</span>
                        </div>

                        <div class="font-semibold">{{ revenueRatio }} : 1</div>
                    </div>

                    <!-- Penalty Dependence -->
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 mb-1"
                        >
                            <i
                                class="pi pi-exclamation-circle text-gray-400"
                            ></i>
                            <span>Penalty Dependence</span>
                        </div>

                        <div class="font-semibold">
                            {{ penaltyDependence }}
                        </div>
                    </div>

                    <!-- Avg Revenue / Txn -->
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div
                            class="flex items-center gap-2 text-xs text-gray-500 mb-1"
                        >
                            <i class="pi pi-calculator text-gray-400"></i>
                            <span>Avg Revenue / Txn</span>
                        </div>

                        <div class="font-semibold">₱{{ avgRevenuePerTxn }}</div>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
