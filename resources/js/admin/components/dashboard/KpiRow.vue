<script setup>
import Card from "primevue/card";

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
});

function formatCurrency(value) {
    return "₱ " + Number(value || 0).toLocaleString();
}
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Students -->
        <Card class="kpi-card bg-white dark:bg-gray-800">
            <template #content>
                <div class="kpi-body border-l-2 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <span class="kpi-label">Students</span>
                        <i
                            class="pi pi-users kpi-icon text-indigo-600 dark:text-indigo-400"
                        ></i>
                    </div>

                    <div class="kpi-value">
                        {{ props.stats.registered_students }}
                    </div>

                    <div class="kpi-meta">Registered accounts</div>
                </div>
            </template>
        </Card>

        <!-- Active Rentals -->
        <Card class="kpi-card bg-white dark:bg-gray-800">
            <template #content>
                <div class="kpi-body border-l-2 border-blue-500">
                    <div class="flex items-center justify-between">
                        <span class="kpi-label">Active Rentals</span>
                        <i
                            class="pi pi-box kpi-icon text-blue-600 dark:text-blue-400"
                        ></i>
                    </div>

                    <div class="kpi-value">
                        {{ props.stats.active_rentals }}
                    </div>

                    <div class="kpi-meta">Lockers currently in use</div>
                </div>
            </template>
        </Card>

        <!-- Revenue Today -->
        <Card class="kpi-card bg-white dark:bg-gray-800">
            <template #content>
                <div class="kpi-body border-l-2 border-emerald-500">
                    <div class="flex items-center justify-between">
                        <span class="kpi-label">Revenue Today</span>
                        <i
                            class="pi pi-wallet kpi-icon text-emerald-600 dark:text-emerald-400"
                        ></i>
                    </div>

                    <div class="kpi-value">
                        {{ formatCurrency(props.stats.today_revenue) }}
                    </div>

                    <div class="kpi-meta">Completed sessions</div>
                </div>
            </template>
        </Card>

        <!-- Active Penalties -->
        <Card class="kpi-card bg-white dark:bg-gray-800">
            <template #content>
                <div class="kpi-body border-l-2 border-rose-500">
                    <div class="flex items-center justify-between">
                        <span class="kpi-label">Active Penalties</span>
                        <i
                            class="pi pi-exclamation-circle kpi-icon text-rose-600 dark:text-rose-400"
                        ></i>
                    </div>

                    <div class="kpi-value">
                        {{ props.stats.active_penalties }}
                    </div>

                    <div class="kpi-meta">Unresolved cases</div>
                </div>
            </template>
        </Card>
    </div>
</template>
