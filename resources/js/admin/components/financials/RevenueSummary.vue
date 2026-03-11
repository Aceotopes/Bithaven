<script setup>
import { ref, onMounted, watch, computed } from "vue";
import axios from "axios";

import RevenueTrendChart from "@/admin/components/financials/summary/RevenueTrendChart.vue";
import RevenueBreakdownChart from "@/admin/components/financials/summary/RevenueBreakdownChart.vue";
import RevenuePerHourChart from "@/admin/components/financials/summary/RevenuePerHourChart.vue";
import RevenueInsights from "@/admin/components/financials/summary/RevenueInsights.vue";

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
</script>

<template>
    <div class="space-y-4">
        <!-- Row 1 -->
        <div class="grid grid-cols-1 gap-4">
            <RevenueTrendChart :filters="filters" />
        </div>

        <!-- Row 2 -->
        <div class="grid grid-cols-1 2xl:grid-cols-2 gap-4">
            <RevenueBreakdownChart :daily="daily" />
            <RevenuePerHourChart :hourly="hourly" />
        </div>

        <!-- Row 3 -->
        <div class="grid grid-cols-1 gap-4">
            <RevenueInsights :daily="daily" />

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
