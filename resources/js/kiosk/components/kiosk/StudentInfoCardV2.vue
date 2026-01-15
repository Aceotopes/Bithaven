<script setup>
// UI-ONLY SAMPLE DATA
// ----------------------------------------
// Replace these values later with
// props or store data when backend exists.
// ----------------------------------------

const student = {
    name: "Ace Argee F. Vizcarra", // ← DB-ready
    studentNumber: "22-150570", // ← DB-ready
    yearLevel: "4th Year", // ← DB-ready
    department: "Computer Engineering", // ← DB-ready
};
</script>

<template>
    <section
        class="relative max-w-[920px] mx-auto bg-white/90 backdrop-blur-xl border border-black/10 rounded-[24px] px-14 py-12 shadow-[0_24px_60px_rgba(0,0,0,0.18)]"
    >
        <!-- Header -->
        <div class="flex justify-between items-center">
            <p
                class="text-[16px] tracking-[0.4em] uppercase text-gray-500 font-semibold"
            >
                Student Information
            </p>

            <p class="text-[16px] tracking-wide text-gray-400">Verified</p>
        </div>

        <!-- Divider -->
        <div class="my-8 h-px bg-black/10"></div>

        <!-- Content -->
        <div class="grid grid-cols-[auto_1fr] gap-12 items-center">
            <!-- Avatar -->
            <div
                class="w-32 h-32 rounded-full bg-gray-100 border border-black/10 flex items-center justify-center text-gray-500 text-4xl font-mono"
            >
                AV
                <!-- Replace with <img /> later -->
            </div>

            <!-- Student Details -->
            <div>
                <p class="text-[30px] font-semibold text-gray-900">
                    {{ student.name }}
                </p>

                <p class="mt-2 font-mono text-[18px] text-gray-600">
                    STUDENT NO: {{ student.studentNumber }}
                </p>

                <div class="mt-6 grid grid-cols-2 gap-12">
                    <div>
                        <p
                            class="text-sm tracking-widest uppercase text-gray-500"
                        >
                            Year Level
                        </p>
                        <p class="mt-1 text-[20px] text-gray-800">
                            {{ student.yearLevel }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-sm tracking-widest uppercase text-gray-500"
                        >
                            Department
                        </p>
                        <p class="mt-1 text-[20px] text-gray-800">
                            {{ student.department }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
