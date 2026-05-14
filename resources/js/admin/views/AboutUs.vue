<script setup>
import Card from "primevue/Card";
import AboutImg from "@/admin/assets/AboutUs.png";
</script>

<template>
    <Card class="kpi-card">
        <template #content>
            <img :src="AboutImg" alt="card image" class="kpi-image" />
        </template>
    </Card>
</template>
