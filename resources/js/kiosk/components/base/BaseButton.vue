<script setup>
defineProps({
    disabled: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <button
        :class="[
            'px-8 py-4 rounded-xl text-xl font-semibold transition',
            'bg-indigo-600 text-white',
            'active:scale-95',
            disabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-indigo-500',
        ]"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>
