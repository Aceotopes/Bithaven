<script setup>
import { computed } from "vue";

const props = defineProps({
    daily: Array,
});

const breakdownData = computed(() => {
    const rental = props.daily.reduce(
        (sum, d) => sum + Number(d.rental || 0),
        0
    );

    const penalty = props.daily.reduce(
        (sum, d) => sum + Number(d.penalty || 0),
        0
    );

    return {
        labels: ["Rental", "Penalty"],
        datasets: [
            {
                data: [rental, penalty],
                backgroundColor: ["#48CAE4", "#FFB703"],
                borderWidth: 0,
            },
        ],
    };
});
</script>

<template>
    <Card class="ui-card">
        <template #content>
            <div class="ui-card-body">
                <div class="ui-card-header">
                    <div>
                        <h3 class="ui-card-title">Revenue Breakdown</h3>
                        <p class="ui-card-subtitle">Rental vs Penalty income</p>
                    </div>

                    <i class="pi pi-chart-pie text-amber-500"></i>
                </div>

                <div class="h-64">
                    <Chart type="doughnut" :data="breakdownData" />
                </div>
            </div>
        </template>
    </Card>
</template>
