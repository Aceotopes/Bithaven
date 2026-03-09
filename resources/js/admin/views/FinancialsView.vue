<script setup>
import { ref } from "vue";
import Card from "primevue/card";
import FloatLabel from "primevue/floatlabel";
import ProgressSpinner from "primevue/progressspinner";

import TransactionsTable from "@/admin/components/financials/TransactionsTable.vue";

const activeTab = ref("transactions");

const loading = ref(false);

const filters = ref({
    start_date: null,
    end_date: null,
    type: "ALL",
});

function resetFilters() {
    filters.value = {
        start_date: null,
        end_date: null,
        type: "ALL",
    };
}

const appliedFilters = ref({ ...filters.value });

function applyFilters() {
    appliedFilters.value = { ...filters.value };
}
</script>

<template>
    <div class="space-y-5">
        <!-- Financial Reports Card -->
        <Card class="ui-card">
            <template #content>
                <div class="ui-card-body">
                    <!-- Header -->
                    <div class="ui-card-header">
                        <div>
                            <h3 class="ui-card-title">Financial Reports</h3>
                            <p class="ui-card-subtitle">
                                Kiosk payment sessions and revenue records
                            </p>
                        </div>

                        <i
                            class="pi pi-wallet ui-card-icon text-emerald-500"
                        ></i>
                    </div>

                    <!-- Filters -->
                    <div class="ui-card-section flex flex-wrap gap-3 items-end">
                        <div class="flex flex-col gap-1">
                            <FloatLabel variant="on">
                                <DatePicker
                                    v-model="filters.start_date"
                                    showIcon
                                    iconDisplay="input"
                                    dateFormat="yy-mm-dd"
                                    class="w-55"
                                />
                                <label>Start Date</label>
                            </FloatLabel>
                        </div>

                        <div class="flex flex-col gap-1">
                            <FloatLabel variant="on">
                                <DatePicker
                                    v-model="filters.end_date"
                                    showIcon
                                    iconDisplay="input"
                                    dateFormat="yy-mm-dd"
                                    class="w-55"
                                />
                                <label>End Date</label>
                            </FloatLabel>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="ui-card-subtitle"
                                >Transaction Type</label
                            >
                            <Select
                                v-model="filters.type"
                                placeholder="Select type"
                                :options="['ALL', 'RENTAL', 'PENALTY']"
                                class="w-40"
                            />
                        </div>

                        <Button
                            label="Apply"
                            icon="pi pi-filter"
                            severity="secondary"
                            outlined
                            @click="applyFilters"
                        />
                        <Button
                            label="Reset"
                            icon="pi pi-refresh"
                            severity="secondary"
                            outlined
                            @click="resetFilters"
                        />
                    </div>

                    <!-- KPIs -->
                    <div class="ui-card-section">
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4"
                        >
                            <div class="kpi-card">
                                <div class="kpi-body">
                                    <span class="kpi-label">Total Revenue</span>
                                    <div class="kpi-value">₱0</div>
                                </div>
                            </div>

                            <div class="kpi-card">
                                <div class="kpi-body">
                                    <span class="kpi-label"
                                        >Rental Revenue</span
                                    >
                                    <div class="kpi-value">₱0</div>
                                </div>
                            </div>

                            <div class="kpi-card">
                                <div class="kpi-body">
                                    <span class="kpi-label"
                                        >Penalty Revenue</span
                                    >
                                    <div class="kpi-value">₱0</div>
                                </div>
                            </div>

                            <div class="kpi-card">
                                <div class="kpi-body">
                                    <span class="kpi-label">Transactions</span>
                                    <div class="kpi-value">0</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div
                        class="ui-card-section flex gap-6 border-b border-gray-200 dark:border-gray-700 text-sm font-medium"
                    >
                        <button
                            @click="activeTab = 'transactions'"
                            :class="
                                activeTab === 'transactions'
                                    ? 'text-cyan-600 border-b-2 border-cyan-600 pb-2'
                                    : 'text-gray-500 pb-2'
                            "
                        >
                            Transactions
                        </button>

                        <button
                            @click="activeTab = 'summary'"
                            :class="
                                activeTab === 'summary'
                                    ? 'text-cyan-600 border-b-2 border-cyan-600 pb-2'
                                    : 'text-gray-500 pb-2'
                            "
                        >
                            Revenue Summary
                        </button>

                        <button
                            @click="activeTab = 'lockers'"
                            :class="
                                activeTab === 'lockers'
                                    ? 'text-cyan-600 border-b-2 border-cyan-600 pb-2'
                                    : 'text-gray-500 pb-2'
                            "
                        >
                            Locker Revenue
                        </button>

                        <button
                            @click="activeTab = 'penalties'"
                            :class="
                                activeTab === 'penalties'
                                    ? 'text-cyan-600 border-b-2 border-cyan-600 pb-2'
                                    : 'text-gray-500 pb-2'
                            "
                        >
                            Penalty Collections
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="ui-card-section min-h-[320px]">
                        <div v-if="loading" class="flex justify-center py-20">
                            <ProgressSpinner />
                        </div>
                        <div v-else>
                            <div v-if="activeTab === 'transactions'">
                                <Card class="table-card">
                                    <template #content>
                                        <div class="table-header">
                                            <h3 class="ui-card-title">
                                                Transactions
                                            </h3>
                                            <p class="ui-card-subtitle">
                                                Completed payment sessions
                                            </p>
                                        </div>

                                        <div class="table-body">
                                            <TransactionsTable
                                                :filters="appliedFilters"
                                            />
                                        </div>
                                    </template>
                                </Card>
                            </div>

                            <div v-else-if="activeTab === 'summary'">
                                <Card class="table-card">
                                    <template #content>
                                        <div class="table-header">
                                            <h3 class="ui-card-title">
                                                Revenue
                                            </h3>
                                            <p class="ui-card-subtitle">
                                                Completed payment sessions
                                            </p>
                                        </div>

                                        <div class="table-body">
                                            <!-- DataTable will go here -->
                                        </div>
                                    </template>
                                </Card>
                            </div>

                            <div v-else-if="activeTab === 'lockers'">
                                <Card class="table-card">
                                    <template #content>
                                        <div class="table-header">
                                            <h3 class="ui-card-title">
                                                Lockers
                                            </h3>
                                            <p class="ui-card-subtitle">
                                                Completed payment sessions
                                            </p>
                                        </div>

                                        <div class="table-body">
                                            <!-- DataTable will go here -->
                                        </div>
                                    </template>
                                </Card>
                            </div>

                            <div
                                v-else-if="activeTab === 'penalties'"
                                class="text-gray-500"
                            >
                                <Card class="table-card">
                                    <template #content>
                                        <div class="table-header">
                                            <h3 class="ui-card-title">
                                                Penalties
                                            </h3>
                                            <p class="ui-card-subtitle">
                                                Completed payment sessions
                                            </p>
                                        </div>

                                        <div class="table-body">
                                            <!-- DataTable will go here -->
                                        </div>
                                    </template>
                                </Card>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
