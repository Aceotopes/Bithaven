<script setup>
defineProps({
    show: Boolean,
    lockerNumber: Number,
});

const emit = defineEmits(["cancel", "confirm"]);
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    >
        <!-- CONTAINER -->
        <div
            class="w-[620px] bg-white rounded-[32px] border border-black/10 shadow-[0_48px_120px_rgba(0,0,0,0.4)] px-14 py-12 text-center"
        >
            <!-- ICON -->
            <div
                class="mx-auto mb-8 w-20 h-20 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-[40px]"
            >
                !
            </div>

            <!-- TITLE -->
            <h2 class="text-[34px] font-semibold text-gray-900">
                End Locker Rental
            </h2>

            <!-- CONTEXT -->
            <p class="mt-4 text-[20px] text-gray-600">
                You are about to end your rental for
            </p>

            <!-- LOCKER NUMBER -->
            <p
                class="mt-3 text-[32px] font-mono font-bold text-gray-900 tracking-wide"
            >
                Locker {{ lockerNumber }}
            </p>

            <!-- WARNING -->
            <div
                class="mt-8 px-8 py-6 rounded-2xl bg-gray-100 text-[18px] text-gray-600 leading-relaxed"
            >
                Please make sure you have removed all your belongings.
                <span class="block mt-2 font-semibold text-gray-800">
                    This action cannot be undone.
                </span>
            </div>

            <!-- ACTIONS -->
            <div class="mt-12 flex justify-center gap-8">
                <button
                    class="px-10 py-4 rounded-2xl bg-gray-200 text-gray-800 text-[20px] font-semibold transition active:scale-[0.97]"
                    @click="$emit('cancel')"
                >
                    Cancel
                </button>

                <button
                    class="px-10 py-4 rounded-2xl bg-red-600 text-white text-[20px] font-semibold transition active:scale-[0.97]"
                    @click="$emit('confirm')"
                >
                    End Rental
                </button>
            </div>
        </div>
    </div>
</template>
