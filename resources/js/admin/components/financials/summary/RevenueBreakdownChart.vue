<script setup>
import { computed } from "vue";

const props = defineProps({
    daily: {
        type: Array,
        default: () => [],
    },
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
    <Card class="ui-card">
        <template #content>
            <div class="ui-card-body">
                <div class="ui-card-header">
                    <div>
                        <h3 class="ui-card-title">Revenue Breakdown</h3>
                        <p class="ui-card-subtitle">
                            Rental vs Penalty income distribution
                        </p>
                    </div>

                    <i
                        class="pi pi-chart-pie text-amber-500"
                        style="font-size: 1.5rem"
                    ></i>
                </div>

                <!-- Donut Chart -->
                <div class="flex justify-center items-center h-62">
                    <Chart
                        type="doughnut"
                        :data="breakdownData"
                        :options="chartOptions"
                        :plugins="[centerTextPlugin]"
                        class="w-full h-full"
                    />
                </div>

                <!-- Revenue Sources -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 rounded-lg bg-gray-100 dark:bg-gray-800">
                        <div class="text-xs text-gray-500">Rental Revenue</div>

                        <div class="text-lg font-semibold text-cyan-600">
                            ₱{{ rentalRevenue.toLocaleString() }}
                        </div>

                        <div class="text-xs text-gray-400">
                            {{ rentalPercent }}%
                        </div>
                    </div>

                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div class="text-xs text-gray-500">Penalty Revenue</div>

                        <div class="text-lg font-semibold text-amber-500">
                            ₱{{ penaltyRevenue.toLocaleString() }}
                        </div>

                        <div class="text-xs text-gray-400">
                            {{ penaltyPercent }}%
                        </div>
                    </div>
                </div>

                <!-- Additional Analytics -->

                <hr class="my-4 border-gray-200 dark:border-gray-700" />
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500 text-xs">Total Revenue</div>
                        <div class="font-semibold">
                            ₱{{ totalRevenue.toLocaleString() }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500 text-xs">Transactions</div>
                        <div class="font-semibold">
                            {{ totalTransactions }}
                        </div>
                    </div>
                </div>

                <hr class="my-4 border-gray-200 dark:border-gray-700" />

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500 text-xs">Primary Source</div>
                        <div class="font-semibold">
                            {{ dominantSource }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500 text-xs">
                            Rental : Penalty
                        </div>
                        <div class="font-semibold">{{ revenueRatio }} : 1</div>
                    </div>

                    <div>
                        <div class="text-gray-500 text-xs">
                            Penalty Dependence
                        </div>
                        <div class="font-semibold">
                            {{ penaltyDependence }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500 text-xs">
                            Avg Revenue / Transaction
                        </div>
                        <div class="font-semibold">₱{{ avgRevenuePerTxn }}</div>
                    </div>
                </div>
            </div>
        </template>
    </Card>
</template>
