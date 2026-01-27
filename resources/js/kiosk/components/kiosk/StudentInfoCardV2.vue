<script setup>
// UI-ONLY SAMPLE DATA
// ----------------------------------------
// Replace these values later with
// props or store data when backend exists.
// ----------------------------------------

//mock student data

defineProps({
    student: Object,
});
</script>

<template>
    <section
        class="relative w-full bg-white/90 backdrop-blur-xl border border-black/10 rounded-[24px] px-16 py-10 shadow-[0_20px_50px_rgba(0,0,0,0.16)]"
    >
        <!-- Header -->
        <div class="flex justify-between items-center">
            <p
                class="text-[20px] tracking-[0.4em] uppercase text-gray-500 font-semibold"
            >
                Student Information
            </p>

            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <p class="text-[20px] text-gray-600">Verified</p>
            </div>
        </div>

        <!-- Divider -->
        <div class="my-8 h-px bg-black/10"></div>

        <!-- Content -->
        <div class="grid grid-cols-[auto_1fr] gap-14 items-center">
            <!-- Avatar -->
            <div
                class="w-36 h-36 rounded-full bg-gray-100 border border-black/10 flex items-center justify-center text-gray-400 text-[44px] font-mono"
            >
                AV
            </div>

            <!-- Student Details -->
            <div>
                <!-- Name -->
                <p
                    class="text-[34px] font-semibold text-gray-900 leading-tight"
                >
                    {{ student.fullName }}
                </p>

                <!-- Student Number -->
                <p
                    class="mt-3 font-mono text-[20px] text-gray-600 tracking-wide"
                >
                    STUDENT NO · {{ student.studentNumber }}
                </p>

                <!-- Meta Info -->
                <div class="mt-8 grid grid-cols-2 gap-16">
                    <div>
                        <p
                            class="text-[14px] tracking-widest uppercase text-gray-500"
                        >
                            Year Level
                        </p>
                        <p class="mt-1 text-[22px] text-gray-800">
                            {{ student.yearLevel }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-[14px] tracking-widest uppercase text-gray-500"
                        >
                            Department
                        </p>
                        <p class="mt-1 text-[22px] text-gray-800">
                            {{ student.department }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
