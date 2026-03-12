<script setup>
import { computed } from "vue";

const props = defineProps({
    lockers: {
        type: Array,
        default: () => [],
    },
});

function formatCurrency(value) {
    return "₱" + Number(value || 0).toLocaleString();
}

function avgRevenue(row) {
    if (!row.rentals) return 0;
    return (row.total_revenue / row.rentals).toFixed(2);
}

/* -------- FOOTER TOTALS -------- */

const totalRentals = computed(() =>
    props.lockers.reduce((sum, l) => sum + Number(l.rentals || 0), 0)
);

const totalPenalties = computed(() =>
    props.lockers.reduce((sum, l) => sum + Number(l.penalties || 0), 0)
);

const totalRentalRevenue = computed(() =>
    props.lockers.reduce((sum, l) => sum + Number(l.rental_revenue || 0), 0)
);

const totalPenaltyRevenue = computed(() =>
    props.lockers.reduce((sum, l) => sum + Number(l.penalty_revenue || 0), 0)
);

const totalRevenue = computed(() =>
    props.lockers.reduce((sum, l) => sum + Number(l.total_revenue || 0), 0)
);
</script>

<template>
    <DataTable
        :value="lockers"
        :rows="15"
        stripedRows
        responsiveLayout="scroll"
        sortField="locker_id"
        :sortOrder="1"
    >
        <!-- Locker -->

        <Column field="resolved_locker_id" header="Locker" sortable>
            <template #footer>
                <strong>Total</strong>
            </template>
        </Column>

        <!-- Rentals -->

        <Column field="rentals" header="Rentals" sortable>
            <template #footer>
                <strong>{{ totalRentals }}</strong>
            </template>
        </Column>

        <!-- Penalties -->

        <Column field="penalties" header="Penalties" sortable>
            <template #footer>
                <strong>{{ totalPenalties }}</strong>
            </template>
        </Column>

        <!-- Rental Revenue -->

        <Column header="Rental Revenue">
            <template #body="slotProps">
                <span class="text-cyan-600 font-medium">
                    {{ formatCurrency(slotProps.data.rental_revenue) }}
                </span>
            </template>

            <template #footer>
                <strong>{{ formatCurrency(totalRentalRevenue) }}</strong>
            </template>
        </Column>

        <!-- Penalty Revenue -->

        <Column header="Penalty Revenue">
            <template #body="slotProps">
                <span class="text-amber-600 font-medium">
                    {{ formatCurrency(slotProps.data.penalty_revenue) }}
                </span>
            </template>

            <template #footer>
                <strong>{{ formatCurrency(totalPenaltyRevenue) }}</strong>
            </template>
        </Column>

        <!-- Total Revenue -->

        <Column header="Total Revenue">
            <template #body="slotProps">
                <span class="font-semibold">
                    {{ formatCurrency(slotProps.data.total_revenue) }}
                </span>
            </template>

            <template #footer>
                <strong>{{ formatCurrency(totalRevenue) }}</strong>
            </template>
        </Column>

        <!-- Avg / Rental -->

        <Column header="Avg / Rental">
            <template #body="slotProps">
                ₱{{ avgRevenue(slotProps.data) }}
            </template>
        </Column>
    </DataTable>
</template>
