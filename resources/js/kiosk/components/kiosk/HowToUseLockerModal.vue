<script setup>
defineProps({
    show: Boolean,
});

const emit = defineEmits(["close"]);
</script>

<template>
    <teleport to="body">
        <transition name="fade">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
            >
                <!-- PANEL -->
                <div
                    class="relative w-[980px] max-w-[94%] bg-white/90 backdrop-blur-xl border border-black/10 rounded-[24px] px-16 py-14 shadow-[0_32px_80px_rgba(0,0,0,0.35)]"
                >
                    <!-- SYSTEM STRIP -->
                    <div class="flex justify-between items-center mb-8">
                        <p
                            class="text-[18px] tracking-[0.4em] uppercase text-emerald-600 font-semibold"
                        >
                            Instructions
                        </p>

                        <!-- <p class="text-[18px] text-gray-500">
                            Smart Locker System
                        </p> -->
                    </div>

                    <!-- TITLE -->
                    <h1 class="text-[42px] font-bold text-gray-900 text-center">
                        How to Use Bithaven Smart Locker
                    </h1>

                    <p
                        class="mt-4 text-[22px] text-gray-600 text-center max-w-[760px] mx-auto"
                    >
                        Follow the steps below to securely store and retrieve
                        your belongings.
                    </p>

                    <!-- DIVIDER -->
                    <div class="my-10 h-px bg-black/10"></div>

                    <!-- STEPS -->
                    <div
                        class="grid grid-cols-1 gap-6 text-[22px] text-gray-700 max-w-[820px] mx-auto"
                    >
                        <div class="flex gap-5 items-start">
                            <span
                                class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-semibold"
                            >
                                1
                            </span>
                            <p>
                                Select an available locker from the selection
                                screen.
                            </p>
                        </div>

                        <div class="flex gap-5 items-start">
                            <span
                                class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-semibold"
                            >
                                2
                            </span>
                            <p>Choose how long you want to rent the locker.</p>
                        </div>

                        <div class="flex gap-5 items-start">
                            <span
                                class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-semibold"
                            >
                                3
                            </span>
                            <p>
                                Insert the required payment into the coin slot.
                            </p>
                        </div>

                        <div class="flex gap-5 items-start">
                            <span
                                class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-semibold"
                            >
                                4
                            </span>
                            <p>
                                The locker will open automatically once payment
                                is complete.
                            </p>
                        </div>

                        <div class="flex gap-5 items-start">
                            <span
                                class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-semibold"
                            >
                                5
                            </span>
                            <p>
                                Please return and retrieve your items before the
                                rental time expires.
                            </p>
                        </div>
                    </div>

                    <!-- PENALTY SECTION -->
                    <div
                        class="mt-12 rounded-2xl border border-amber-200 bg-amber-50 px-12 py-8 text-center"
                    >
                        <p class="text-[26px] font-semibold text-amber-800">
                            Late Return & Penalty
                        </p>

                        <p
                            class="mt-3 text-[20px] text-amber-700 max-w-[760px] mx-auto"
                        >
                            If the locker is not returned before the rental
                            period ends, a penalty will be applied.
                        </p>

                        <p
                            class="mt-2 text-[18px] text-amber-600 max-w-[760px] mx-auto"
                        >
                            The penalty amount increases over time and must be
                            settled before the locker can be unlocked again.
                        </p>
                    </div>

                    <!-- CTA -->
                    <div class="mt-14 flex justify-center">
                        <button
                            class="px-24 h-20 rounded-2xl bg-emerald-600 text-white text-[26px] font-semibold transition active:scale-[0.97]"
                            @click="emit('close')"
                        >
                            Proceed
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>
