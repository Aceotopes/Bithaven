<script setup>
import { computed, ref, watch, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({
            start_date: null,
            end_date: null,
        }),
    },
});

const range = ref("7D");
const daily = ref([]);
const growth = ref(0);

async function fetchTrend() {
    const res = await axios.get("/admin/financials/revenue-summary", {
        params: {
            range: range.value,
            start_date: formatDate(props.filters?.start_date),
            end_date: formatDate(props.filters?.end_date),
        },
    });

    daily.value = res.data.daily;
    growth.value = Number(res.data.growth ?? 0);
}

const chartData = computed(() => ({
    labels: daily.value.map((d) =>
        new Date(d.date).toLocaleDateString("en-US", {
            month: "short",
            day: "2-digit",
        })
    ),

    datasets: [
        {
            type: "line",
            label: "Revenue",
            data: daily.value.map((d) => d.revenue),
            borderColor: "#0096C7",
            backgroundColor: "rgba(0,150,199,0.15)",
            tension: 0.45,
            borderWidth: 3,
            pointRadius: 3,
            fill: true,
            yAxisID: "yRevenue",
        },
        {
            type: "bar",
            label: "Transactions",
            data: daily.value.map((d) => d.transactions),
            backgroundColor: "#90e0ef",
            borderRadius: 6,
            barThickness: 20,
            yAxisID: "yTransactions",
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,

    interaction: {
        mode: "index",
        intersect: false,
    },

    animation: {
        duration: 900,
        easing: "easeOutQuart",
    },

    animations: {
        y: {
            from: 0,
            delay(ctx) {
                return ctx.dataIndex * 100;
            },
        },
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
                    if (context.dataset.label === "Revenue") {
                        return "Revenue: ₱" + context.parsed.y.toLocaleString();
                    }

                    if (context.dataset.label === "Average Revenue") {
                        return "Average: ₱" + context.parsed.y.toLocaleString();
                    }

                    return "Transactions: " + context.parsed.y;
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

        yTransactions: {
            position: "left",
            beginAtZero: true,

            ticks: {
                stepSize: 1,
                color: "#6b7280",
                font: { size: 11 },
            },

            grid: {
                color: "rgba(156,163,175,0.15)",
            },
        },

        yRevenue: {
            position: "right",
            beginAtZero: true,

            ticks: {
                color: "#6b7280",
                font: { size: 11 },
                callback: (value) => "₱" + value,
            },

            grid: {
                drawOnChartArea: false,
            },
        },
    },
};

function changeRange(value) {
    range.value = value;
}

// watch(
//     () => props.daily,
//     () => {
//         chartKey.value++;
//     }
// );

function formatDate(date) {
    if (!date) return null;

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
}

watch(range, () => {
    fetchTrend();
});

watch(
    () => [props.filters?.start_date, props.filters?.end_date],
    () => {
        fetchTrend();
    }
);
onMounted(fetchTrend);
</script>

<template>
    <Card class="ui-card xl:col-span-2">
        <template #content>
            <div class="ui-card-body">
                <div class="ui-card-header">
                    <div>
                        <h3 class="ui-card-title">Transactions vs Revenue</h3>

                        <div class="flex items-center gap-2 mt-1">
                            <p class="ui-card-subtitle">
                                Daily kiosk activity trend
                            </p>

                            <span
                                class="text-xs font-semibold px-2 py-0.5 rounded"
                                :class="
                                    growth >= 0
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-red-100 text-red-700'
                                "
                            >
                                <i
                                    :class="
                                        growth >= 0
                                            ? 'pi pi-arrow-up'
                                            : 'pi pi-arrow-down'
                                    "
                                    class="mr-1"
                                ></i>

                                {{ Math.abs(growth || 0).toFixed(1) }}%
                            </span>
                            <i
                                class="pi pi-info-circle text-gray-400 text-xs ml-1"
                                v-tooltip="'Compared to the previous period'"
                            ></i>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button
                            v-for="r in ['7D', '1M', '3M', '1Y']"
                            :key="r"
                            @click="changeRange(r)"
                            class="text-xs px-2 py-1 rounded border transition"
                            :class="
                                range === r
                                    ? 'bg-cyan-600 text-white border-cyan-600'
                                    : 'border-gray-300 text-gray-500 hover:bg-gray-100'
                            "
                        >
                            {{ r }}
                        </button>
                    </div>
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
</template>
