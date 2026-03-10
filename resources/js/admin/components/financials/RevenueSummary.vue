<script setup>
import { ref, onMounted, watch, computed } from "vue";
import axios from "axios";

import RevenueTrendChart from "@/admin/components/financials/summary/RevenueTrendChart.vue";
import RevenueBreakdownChart from "@/admin/components/financials/summary/RevenueBreakdownChart.vue";
import RevenuePerHourChart from "@/admin/components/financials/summary/RevenuePerHourChart.vue";

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({
            start_date: null,
            end_date: null,
        }),
    },
});
// const range = ref("7D");

const loading = ref(false);

const daily = ref([]);
const hourly = ref([]);

const momentum = ref(0);

function formatDate(date) {
    if (!date) return null;

    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, "0");
    const d = String(date.getDate()).padStart(2, "0");

    return `${y}-${m}-${d}`;
}

async function fetchSummary() {
    loading.value = true;

    try {
        const res = await axios.get("/admin/financials/revenue-summary", {
            params: {
                start_date: props.filters
                    ? formatDate(props.filters.start_date)
                    : null,
                end_date: props.filters
                    ? formatDate(props.filters.end_date)
                    : null,
            },
        });
        momentum.value = res.data.momentum;
        daily.value = res.data.daily;
        hourly.value = res.data.hourly;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

onMounted(fetchSummary);

watch(
    () => props.filters,
    () => {
        if (props.filters) {
            fetchSummary();
        }
    },
    { deep: true }
);

// watch(range, () => {
//     fetchSummary();
// });

const hourlyChartData = computed(() => ({
    labels: hourly.value.map((h) => `${h.hour}:00`),
    datasets: [
        {
            label: "Revenue per Hour",
            data: hourly.value.map((h) => h.revenue),
            backgroundColor: "#caf0f8",
            borderRadius: 6,
        },
    ],
}));

const avgRevenue = computed(() => {
    if (!daily.value.length) return 0;
    const total = daily.value.reduce((sum, d) => sum + Number(d.revenue), 0);
    return (total / daily.value.length).toFixed(2);
});

const peakDay = computed(() => {
    if (!daily.value.length) return null;
    return daily.value.reduce((max, d) => (d.revenue > max.revenue ? d : max));
});
</script>

<template>
    <div class="space-y-4">
        <!-- Row 1 -->
        <div class="grid grid-cols-1 gap-4">
            <RevenueTrendChart :filters="filters" :momentum="momentum" />
        </div>

        <!-- Row 2 -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <RevenueBreakdownChart :daily="daily" />
            <RevenuePerHourChart :hourly="hourly" />
        </div>

        <!-- Row 3 -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <!-- Revenue Insights -->
            <Card class="ui-card">
                <template #content>
                    <div class="ui-card-body">
                        <div class="ui-card-header">
                            <div>
                                <h3 class="ui-card-title">Revenue Insights</h3>
                                <p class="ui-card-subtitle">Derived metrics</p>
                            </div>
                            <i class="pi pi-chart-bar text-emerald-500"></i>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-500"
                                    >Average Revenue / Day</span
                                >
                                <span class="font-semibold">
                                    ₱{{ avgRevenue }}
                                </span>
                            </div>

                            <div class="flex justify-between" v-if="peakDay">
                                <span class="text-gray-500"
                                    >Peak Revenue Day</span
                                >
                                <span class="font-semibold">
                                    {{ peakDay.date }} — ₱{{ peakDay.revenue }}
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-500"
                                    >Total Days Recorded</span
                                >
                                <span class="font-semibold">
                                    {{ daily.length }}
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Daily Revenue Table -->
            <Card class="table-card">
                <template #content>
                    <div class="table-header">
                        <h3 class="ui-card-title">Daily Revenue</h3>
                        <p class="ui-card-subtitle">
                            Detailed revenue breakdown
                        </p>
                    </div>

                    <div class="table-body">
                        <DataTable
                            :value="daily"
                            stripedRows
                            responsiveLayout="scroll"
                        >
                            <Column field="date" header="Date" />

                            <Column
                                field="transactions"
                                header="Transactions"
                            />

                            <Column header="Revenue">
                                <template #body="slotProps">
                                    ₱{{
                                        Number(
                                            slotProps.data.revenue
                                        ).toLocaleString()
                                    }}
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
