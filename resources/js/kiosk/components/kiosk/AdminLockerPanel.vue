<script setup>
const props = defineProps({
    lockers: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["select-locker"]);
</script>

<template>
    <section
        class="relative z-20 w-full mx-auto bg-white/90 backdrop-blur-xl border border-black/10 rounded-[24px] px-14 py-12 shadow-[0_24px_60px_rgba(0,0,0,0.18)]"
    >
        <!-- Top strip -->
        <div class="flex justify-between items-center">
            <p
                class="text-[24px] tracking-[0.4em] uppercase text-cyan-500 font-semibold"
            >
                Admin Control
            </p>

            <p class="text-[24px] tracking-wide text-gray-500">
                Total Lockers
                <span class="font-mono font-semibold text-gray-900">
                    {{ lockers.length }}
                </span>
            </p>
        </div>

        <div class="my-8 h-px bg-black/10"></div>

        <!-- 3x3 grid -->
        <div class="grid grid-cols-5 gap-8 py-8">
            <div
                v-for="locker in lockers"
                :key="locker.id"
                @click="emit('select-locker', locker.locker_number)"
                class="relative h-56 rounded-2xl border-2 transition-all duration-150 select-none flex flex-col justify-between overflow-hidden"
            >
                <div
                    class="h-10 flex items-center justify-center text-[13px] font-semibold tracking-widest uppercase bg-gray-800 text-white"
                >
                    {{ locker.status }}
                </div>

                <div class="flex-1 flex flex-col items-center justify-center">
                    <p class="font-mono text-[52px] font-bold text-gray-900">
                        {{ String(locker.locker_number).padStart(2, "0") }}
                    </p>

                    <p
                        class="mt-1 text-[13px] tracking-widest uppercase text-gray-400"
                    >
                        Locker
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
