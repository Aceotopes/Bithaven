<script setup>
import { computed } from "vue";

const props = defineProps({
    daily: {
        type: Array,
        default: () => [],
    },
});

const totalRevenue = computed(() => {
    return props.daily.reduce((sum, d) => sum + Number(d.revenue || 0), 0);
});

function formatCurrency(value) {
    return "₱" + Number(value || 0).toLocaleString();
}

function avgPerTransaction(row) {
    if (!row.transactions) return 0;
    return (row.revenue / row.transactions).toFixed(2);
}

function contribution(row) {
    if (!totalRevenue.value) return 0;

    return ((row.revenue / totalRevenue.value) * 100).toFixed(1);
}
</script>

<template>
    <Card class="table-card">
        <template #content>
            <div class="table-header">
                <h3 class="ui-card-title">Daily Revenue</h3>
                <p class="ui-card-subtitle">
                    Detailed financial breakdown per day
                </p>
            </div>

            <div class="table-body">
                <DataTable
                    :value="daily"
                    stripedRows
                    responsiveLayout="scroll"
                    sortField="date"
                    :sortOrder="-1"
                    paginator
                    :rows="10"
                    :rowsPerPageOptions="[10, 20, 50]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} days"
                >
                    <!-- DATE -->

                    <Column field="date" header="Date" sortable />

                    <!-- TRANSACTIONS -->

                    <Column
                        field="transactions"
                        header="Transactions"
                        sortable
                    />

                    <!-- RENTAL -->

                    <Column header="Rental Revenue">
                        <template #body="slotProps">
                            <span class="text-cyan-600 font-medium">
                                {{ formatCurrency(slotProps.data.rental) }}
                            </span>
                        </template>
                    </Column>

                    <!-- PENALTY -->

                    <Column header="Penalty Revenue">
                        <template #body="slotProps">
                            <span class="text-amber-600 font-medium">
                                {{ formatCurrency(slotProps.data.penalty) }}
                            </span>
                        </template>
                    </Column>

                    <!-- TOTAL -->

                    <Column header="Total Revenue" sortable>
                        <template #body="slotProps">
                            <span class="font-semibold">
                                {{ formatCurrency(slotProps.data.revenue) }}
                            </span>
                        </template>
                    </Column>

                    <!-- AVG / TXN -->

                    <Column header="Avg / Transaction">
                        <template #body="slotProps">
                            <span class="text-gray-600">
                                ₱{{ avgPerTransaction(slotProps.data) }}
                            </span>
                        </template>
                    </Column>

                    <!-- CONTRIBUTION -->

                    <Column header="Contribution">
                        <template #body="slotProps">
                            <span class="text-gray-500">
                                {{ contribution(slotProps.data) }}%
                            </span>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </template>
    </Card>
</template>
