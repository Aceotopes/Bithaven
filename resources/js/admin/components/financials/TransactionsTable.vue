<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";

const props = defineProps({
    filters: Object,
});

const transactions = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const rows = ref(20);
const first = ref(0);
const search = ref("");

const sortField = ref("created_at");
const sortOrder = ref(-1);

async function fetchTransactions() {
    loading.value = true;

    try {
        const response = await axios.get("/admin/financials/transactions", {
            params: {
                start_date: props.filters.start_date,
                end_date: props.filters.end_date,
                type: props.filters.type,
                search: search.value,
                sort_field: sortField.value,
                sort_order: sortOrder.value,
                page: first.value / rows.value + 1,
            },
        });

        transactions.value = response.data.data;
        totalRecords.value = response.data.total;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
}

onMounted(fetchTransactions);

watch(
    () => props.filters,
    () => {
        fetchTransactions();
    },
    { deep: true }
);

watch(search, () => {
    first.value = 0;
    fetchTransactions();
});

function onPage(event) {
    first.value = event.first;
    rows.value = event.rows;
    fetchTransactions();
}

function formatCurrency(value) {
    return "₱" + Number(value || 0).toLocaleString();
}

function onSort(event) {
    sortField.value = event.sortField;
    sortOrder.value = event.sortOrder;
    fetchTransactions();
}
</script>

<template>
    <div class="flex justify-between items-center mb-3">
        <InputText
            v-model="search"
            placeholder="Search student or locker..."
            class="w-64"
        />
    </div>
    <DataTable
        :value="transactions"
        :loading="loading"
        paginator
        :rows="rows"
        :totalRecords="totalRecords"
        lazy
        :first="first"
        @page="onPage"
        @sort="onSort"
        stripedRows
        responsiveLayout="scroll"
    >
        <Column header="Session" sortable field="id">
            <template #body="slotProps">
                <span class="font-medium"> #{{ slotProps.data.id }} </span>
            </template>
        </Column>

        <Column field="student" header="Student" />

        <Column header="Locker">
            <template #body="slotProps">
                <span v-if="slotProps.data.locker">
                    #{{ slotProps.data.locker }}
                </span>

                <span v-else class="text-gray-400"> — </span>
            </template>
        </Column>

        <Column header="Duration">
            <template #body="slotProps">
                <span v-if="slotProps.data.duration">
                    {{ slotProps.data.duration }}
                </span>

                <span v-else class="text-gray-400"> — </span>
            </template>
        </Column>

        <Column field="amount_paid" header="Paid" sortable class="text-right">
            <template #body="slotProps">
                <span class="font-medium text-emerald-600">
                    {{ formatCurrency(slotProps.data.amount_paid) }}
                </span>
            </template>
        </Column>

        <Column header="Due" class="text-right">
            <template #body="slotProps">
                <span class="text-gray-700 dark:text-gray-200">
                    {{ formatCurrency(slotProps.data.amount_due) }}
                </span>
            </template>
        </Column>

        <Column header="Type">
            <template #body="slotProps">
                <Tag
                    v-if="slotProps.data.type === 'RENTAL'"
                    value="Rental"
                    severity="success"
                />

                <Tag
                    v-else-if="slotProps.data.type === 'PENALTY'"
                    value="Penalty"
                    severity="danger"
                />
            </template>
        </Column>

        <Column field="created_at" header="Date">
            <template #body="slotProps">
                <span class="text-sm text-gray-600 dark:text-gray-300">
                    {{ slotProps.data.created_at }}
                </span>
            </template>
        </Column>
    </DataTable>
</template>
