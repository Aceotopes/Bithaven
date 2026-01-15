<script setup>
import SystemHeader from "@/kiosk/components/kiosk/SystemHeader.vue";
import StudentInfoCard from "../components/kiosk/StudentInfoCard.vue";
import StudentInfoCardV2 from "../components/kiosk/StudentInfoCardV2.vue";
import LockerStatusCard from "../components/kiosk/LockerStatusCard.vue";

// ===================== UI State Logic (TEMP) =====================
import { computed, ref } from "vue";
// TEMP: match this to LockerStatusCard for UI testing
const rentalStatus = ref("NO_RENTAL");
// 'NO_RENTAL' | 'ACTIVE_RENTAL' | 'EXPIRED_RENTAL'

// Button state logic (UI-only)
const canRent = computed(() => rentalStatus.value === "NO_RENTAL");
const canEnd = computed(() => rentalStatus.value === "ACTIVE_RENTAL");
const canPay = computed(() => rentalStatus.value === "EXPIRED_RENTAL");
// ================================================================
</script>

<template>
    <div
        class="relative min-h-screen w-full overflow-hidden bg-gradient-to-b from-slate-50 via-slate-100 to-slate-200"
    >
        <!-- Ambient Background -->
        <div class="absolute inset-0 pointer-events-none">
            <!-- Ambient Glow -->
            <div
                class="absolute -top-40 left-1/2 -translate-x-1/2 w-[720px] h-[720px] bg-emerald-400/25 rounded-full blur-3xl"
            />

            <!-- Engineering Grid -->
            <div
                class="absolute inset-0 bg-[linear-gradient(rgba(0,0,0,0.04)_1px,transparent_1px), linear-gradient(90deg,rgba(0,0,0,0.04)_1px,transparent_1px)] bg-[size:48px_48px]"
            />
        </div>

        <!-- Header -->
        <SystemHeader />

        <!-- Main Content Area -->
        <main
            class="relative z-10 max-w-[920px] mx-auto px-12 pt-10 space-y-10"
        >
            <!-- <StudentInfoCard /> -->
            <StudentInfoCardV2 />

            <!-- Locker Status state -->
            <LockerStatusCard />

            <!-- Action Buttons -->
            <section
                class="max-w-[920px] mx-auto mt-10 bg-white/90 backdrop-blur-xl border border-black/10 rounded-[24px] px-14 py-10 shadow-[0_24px_60px_rgba(0,0,0,0.18)]"
            >
                <!-- Panel Header -->
                <div class="flex justify-between items-center">
                    <p
                        class="text-[16px] tracking-[0.4em] uppercase text-gray-500 font-semibold"
                    >
                        System Actions
                    </p>

                    <p class="text-[16px] tracking-wide text-gray-400">
                        Control Panel
                    </p>
                </div>

                <!-- Divider (same as Locker Status Card) -->
                <div class="my-8 h-px bg-black/10"></div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-3 gap-10">
                    <!-- Rent Locker -->
                    <button
                        class="h-20 rounded-2xl text-[22px] font-semibold transition border"
                        :class="
                            canRent
                                ? 'bg-emerald-600 text-white border-emerald-600'
                                : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed'
                        "
                        :disabled="!canRent"
                    >
                        Rent Locker
                    </button>

                    <!-- End Rental -->
                    <button
                        class="h-20 rounded-2xl text-[22px] font-semibold transition border"
                        :class="
                            canEnd
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed'
                        "
                        :disabled="!canEnd"
                    >
                        End Rental
                    </button>

                    <!-- Settle Penalty -->
                    <button
                        class="h-20 rounded-2xl text-[22px] font-semibold transition border"
                        :class="
                            canPay
                                ? 'bg-amber-600 text-white border-amber-600'
                                : 'bg-gray-200 text-gray-400 border-gray-300 cursor-not-allowed'
                        "
                        :disabled="!canPay"
                    >
                        Settle Penalty
                    </button>
                </div>
            </section>
        </main>
    </div>
</template>
