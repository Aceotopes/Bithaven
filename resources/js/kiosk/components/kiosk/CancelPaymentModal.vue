<script setup>
defineProps({
    show: Boolean,
});

const emit = defineEmits(["confirm", "close"]);
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    >
        <!-- PANEL -->
        <div
            class="w-[620px] rounded-[28px] bg-white px-14 py-12 text-center border border-black/10 shadow-[0_40px_100px_rgba(0,0,0,0.35)]"
        >
            <!-- ICON -->
            <div
                class="mx-auto mb-6 w-20 h-20 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 text-[36px]"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="w-13 h-13"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"
                    />
                </svg>
            </div>

            <!-- PRIMARY QUESTION -->
            <h2 class="text-[34px] font-semibold text-gray-900">
                Cancel Payment?
            </h2>

            <!-- CONTEXT -->
            <p class="mt-4 text-[20px] text-gray-600 max-w-[480px] mx-auto">
                You are about to cancel the current payment process.
            </p>

            <!-- IRREVERSIBLE NOTICE (GROUPED) -->
            <div
                class="mt-8 rounded-2xl bg-gray-100 px-8 py-6 text-[18px] text-gray-600 leading-relaxed"
            >
                Coins already inserted cannot be refunded or undone. Any
                progress made during this payment will not be saved.
            </div>

            <!-- OUTCOME -->
            <p class="mt-6 text-[18px] text-gray-500">
                You will be returned to the previous screen.
            </p>

            <!-- ACTIONS -->
            <div class="mt-12 flex gap-8">
                <button
                    class="flex-1 h-16 rounded-2xl bg-gray-200 text-gray-800 text-[20px] font-semibold transition active:scale-[0.97]"
                    @click="emit('close')"
                >
                    Continue Payment
                </button>

                <button
                    class="flex-1 h-16 rounded-2xl bg-amber-600 text-white text-[20px] font-semibold transition active:scale-[0.97]"
                    @click="emit('confirm')"
                >
                    Cancel Payment
                </button>
            </div>
        </div>
    </div>
</template>
