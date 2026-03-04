<script setup>
import { computed } from "vue";

const props = defineProps({
    message: String,
    type: {
        type: String,
        default: "success", // success | error | warning | info
    },
});

const styleMap = {
    success: {
        bg: "bg-emerald-600",
    },
    error: {
        bg: "bg-rose-600",
    },
    warning: {
        bg: "bg-amber-500",
    },
    info: {
        bg: "bg-sky-600",
    },
};

const currentStyle = computed(() => styleMap[props.type] || styleMap.success);
</script>

<template>
    <Transition name="toast-fade">
        <div
            v-if="message"
            class="fixed inset-0 flex items-start justify-center pt-20 z-50 pointer-events-none"
        >
            <div
                class="flex items-center gap-5 px-8 py-5 rounded-2xl text-white text-lg font-semibold shadow-[0_20px_60px_rgba(0,0,0,0.35)] ring-2 backdrop-blur-md min-w-[320px]"
                :class="currentStyle.bg"
            >
                <!-- Message -->
                <div class="tracking-wide">
                    {{ message }}
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.toast-fade-enter-active,
.toast-fade-leave-active {
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-fade-enter-from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
}

.toast-fade-leave-to {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
}
</style>
