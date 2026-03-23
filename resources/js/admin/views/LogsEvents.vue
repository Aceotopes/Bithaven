<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import FloatLabel from "primevue/floatlabel";

const loading = ref(false);

const logs = ref([]);
const events = ref([]);
const securityLogs = ref([]);

const startDate = ref(null);
const endDate = ref(null);
const type = ref("ALL");

const totalRecords = ref(0);
const lazyParams = ref({
    page: 1,
    rows: 15,
});

function formatDate(date) {
    if (!date) return null;

    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, "0");
    const d = String(date.getDate()).padStart(2, "0");

    return `${y}-${m}-${d}`;
}

async function fetchLogs() {
    loading.value = true;

    try {
        const res = await axios.get("/admin/logs", {
            params: {
                start_date: formatDate(startDate.value),
                end_date: formatDate(endDate.value),
                type: type.value,
                page: lazyParams.value.page,
                per_page: lazyParams.value.rows,
            },
        });

        logs.value = res.data.logs.data;
        totalRecords.value = res.data.logs.total;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

async function fetchEvents() {
    try {
        const res = await axios.get("/admin/logs/events");

        events.value = res.data;
    } catch (e) {
        console.error(e);
    }
}

async function fetchSecurityLogs() {
    try {
        const res = await axios.get("/admin/logs/security");

        securityLogs.value = res.data;
    } catch (e) {
        console.error(e);
    }
}

function applyFilters() {
    fetchLogs();
}

function resetFilters() {
    startDate.value = null;
    endDate.value = null;
    type.value = "ALL";

    fetchLogs();
}
function onPage(event) {
    lazyParams.value.page = event.page + 1;
    lazyParams.value.rows = event.rows;

    fetchLogs();
}

function getSeverity(level) {
    switch (level) {
        case "INFO":
            return "info";
        case "SUCCESS":
            return "success";
        case "WARNING":
            return "warn";
        case "ERROR":
            return "danger";
        default:
            return null;
    }
}

onMounted(() => {
    fetchLogs();
    fetchEvents();
    fetchSecurityLogs();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Filters -->

        <Card class="ui-card">
            <template #content>
                <div class="ui-card-body">
                    <div class="ui-card-header">
                        <div>
                            <h3 class="ui-card-title">System Logs & Events</h3>
                            <p class="ui-card-subtitle">
                                Monitor system activities and audit records
                            </p>
                        </div>

                        <i class="pi pi-history text-cyan-500"></i>
                    </div>

                    <div class="flex flex-wrap gap-3 items-end">
                        <FloatLabel variant="on">
                            <DatePicker
                                v-model="startDate"
                                showIcon
                                iconDisplay="input"
                                class="w-48"
                            />
                            <label>Start Date</label>
                        </FloatLabel>

                        <FloatLabel variant="on">
                            <DatePicker
                                v-model="endDate"
                                showIcon
                                iconDisplay="input"
                                class="w-48"
                            />
                            <label>End Date</label>
                        </FloatLabel>

                        <Select
                            v-model="type"
                            :options="[
                                'ALL',
                                'SYSTEM',
                                'SECURITY',
                                'ADMIN',
                                'PAYMENT',
                            ]"
                            class="w-40"
                        />

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
                </div>
                <div class="ui-card-body">
                    <Card class="table-card">
                        <template #content>
                            <!-- <div class="table-header">
                                <h3 class="ui-card-title">Activity Logs</h3>
                                <p class="ui-card-subtitle">
                                    Recorded system activities
                                </p>
                            </div> -->

                            <div class="table-body">
                                <DataTable
                                    :value="logs"
                                    :rows="lazyParams.rows"
                                    :totalRecords="totalRecords"
                                    :lazy="true"
                                    :rowsPerPageOptions="[10, 15, 25, 50]"
                                    scrollable
                                    scrollHeight="600px"
                                    paginator
                                    stripedRows
                                    responsiveLayout="scroll"
                                    class="text-sm"
                                    @page="onPage"
                                >
                                    <!-- Timestamp -->
                                    <Column header="Time">
                                        <template #body="slotProps">
                                            <div class="text-xs text-gray-500">
                                                {{ slotProps.data.timestamp }}
                                            </div>
                                        </template>
                                    </Column>

                                    <!-- Actor -->
                                    <Column header="Actor">
                                        <template #body="slotProps">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span class="font-medium">
                                                    {{
                                                        slotProps.data.actor ||
                                                        "System"
                                                    }}
                                                </span>
                                            </div>
                                        </template>
                                    </Column>

                                    <!-- Event -->
                                    <Column header="Event">
                                        <template #body="slotProps">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span class="font-medium">
                                                    {{ slotProps.data.event }}
                                                </span>
                                            </div>
                                        </template>
                                    </Column>

                                    <!-- Target -->
                                    <Column header="Target">
                                        <template #body="slotProps">
                                            <span
                                                class="text-gray-600 dark:text-gray-300"
                                            >
                                                {{
                                                    slotProps.data.target || "-"
                                                }}
                                            </span>
                                        </template>
                                    </Column>

                                    <!-- Level (IMPORTANT) -->
                                    <Column header="Level">
                                        <template #body="slotProps">
                                            <Tag
                                                :value="slotProps.data.level"
                                                :severity="
                                                    getSeverity(
                                                        slotProps.data.level
                                                    )
                                                "
                                                rounded
                                            />
                                        </template>
                                    </Column>

                                    <!-- Description -->
                                    <Column header="Description">
                                        <template #body="slotProps">
                                            <div
                                                class="text-xs text-gray-600 dark:text-gray-300 max-w-xs truncate"
                                            >
                                                {{ slotProps.data.description }}
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </div>
                        </template>
                    </Card>
                </div>
            </template>
        </Card>

        <!-- Activity Logs Table -->

        <!-- Row 2 -->

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <!-- System Events -->

            <!-- <Card class="ui-card">
                <template #content>
                    <div class="ui-card-body">
                        <div class="ui-card-header">
                            <div>
                                <h3 class="ui-card-title">
                                    Recent System Events
                                </h3>
                                <p class="ui-card-subtitle">
                                    Automated system activities
                                </p>
                            </div>

                            <i class="pi pi-bolt text-amber-500"></i>
                        </div>

                        <ul class="space-y-3 text-sm">
                            <li v-for="event in events" :key="event.id">
                                {{ event.text }}
                            </li>
                        </ul>
                    </div>
                </template>
            </Card> -->

            <!-- Security Logs -->

            <!-- <Card class="ui-card">
                <template #content>
                    <div class="ui-card-body">
                        <div class="ui-card-header">
                            <div>
                                <h3 class="ui-card-title">Security Logs</h3>
                                <p class="ui-card-subtitle">
                                    Authentication and access records
                                </p>
                            </div>

                            <i class="pi pi-shield text-emerald-500"></i>
                        </div>

                        <DataTable
                            :value="securityLogs"
                            :rows="5"
                            paginator
                            responsiveLayout="scroll"
                        >
                            <Column field="timestamp" header="Timestamp" />

                            <Column field="user" header="User" />

                            <Column field="event" header="Event" />

                            <Column field="ip" header="IP Address" />
                        </DataTable>
                    </div>
                </template>
            </Card> -->
        </div>
    </div>
</template>
