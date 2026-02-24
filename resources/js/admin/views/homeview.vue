<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import ProgressSpinner from "primevue/progressspinner";
import KpiRow from "@/admin/components/dashboard/KpiRow.vue";
import RevenueSection from "@/admin/components/dashboard/RevenueSection.vue";
import UtilizationSection from "@/admin/components/dashboard/UtilizationSection.vue";
import KioskEvents from "@/admin/components/dashboard/KioskEvents.vue";

const loading = ref(true);
const stats = ref({});
const events = ref([]);

async function fetchDashboard() {
    try {
        const response = await axios.get("/api/admin/dashboard/summary");
        stats.value = response.data;
        events.value = response.data.recent_events || [];
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
}

onMounted(fetchDashboard);
</script>

<template>
    <div>
        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-20">
            <ProgressSpinner />
        </div>

        <div v-else class="space-y-10">
            <!-- FIRST ROW - KPI CARDS -->
            <KpiRow :stats="stats" />

            <!-- SECOND ROW - REVENUE TREND & RENTAL STATUS -->
            <RevenueSection :stats="stats" />

            <!-- THIRD ROW - LOCKER UTILIZATION & DEMAND VELOCITY -->
            <UtilizationSection :stats="stats" />
            <!-- Recent Activity -->
            <KioskEvents :events="events" />
        </div>
    </div>
</template>
