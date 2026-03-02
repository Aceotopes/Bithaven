<script setup>
const emit = defineEmits(["exit-admin"]);
</script>

<template>
    <div
        class="w-full h-full bg-gray-900 text-white flex flex-col items-center justify-center"
    >
        <h1 class="text-4xl font-bold mb-6">ADMIN MODE</h1>

        <p class="text-lg mb-10 opacity-80">Administrative controls enabled.</p>

        <button
            class="px-8 py-4 bg-red-600 rounded-xl text-xl font-semibold"
            @click="$emit('exit-admin')"
        >
            Exit Admin Mode
        </button>
    </div>
</template>
