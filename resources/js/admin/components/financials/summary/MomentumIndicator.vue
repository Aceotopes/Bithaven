<!-- NOT USED -->

<script setup>
const props = defineProps({
    value: Number,
});
</script>

<template>
    <div
        class="text-xs font-semibold flex items-center gap-1"
        :class="value >= 0 ? 'text-emerald-600' : 'text-red-500'"
    >
        <span v-if="value >= 0">▲</span>
        <span v-else>▼</span>

        <span> {{ Math.abs(value).toFixed(1) }}% </span>

        <span class="text-gray-400"> vs previous period </span>
    </div>
</template>
