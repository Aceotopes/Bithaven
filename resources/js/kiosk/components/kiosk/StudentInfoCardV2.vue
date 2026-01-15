<script setup>
// UI-ONLY SAMPLE DATA
// ----------------------------------------
// Replace these values later with
// props or store data when backend exists.
// ----------------------------------------

const student = {
    name: "Juan Miguel Santos", // ← DB-ready
    studentNumber: "22-150570", // ← DB-ready
    yearLevel: "3rd Year", // ← DB-ready
    department: "Computer Engineering", // ← DB-ready
};
</script>

<template>
    <section
        class="max-w-[880px] mx-auto rounded-3xl bg-white/80 backdrop-blur-xl shadow-[0_16px_40px_rgba(0,0,0,0.15)] px-12 py-12"
    >
        <!-- Student Avatar -->
        <div class="flex justify-center">
            <div
                class="w-32 h-32 rounded-full bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-slate-600 text-5xl font-semibold"
            >
                J
                <!-- Replace later with <img /> -->
            </div>
        </div>

        <!-- Identity -->
        <div class="mt-6 text-center">
            <p class="text-[30px] font-semibold text-gray-900">
                {{ student.name }}
            </p>

            <p class="mt-1 font-mono text-[18px] text-gray-600">
                STUDENT NO: {{ student.studentNumber }}
            </p>
        </div>

        <!-- Divider -->
        <div
            class="mx-auto my-8 h-[2px] w-48 bg-gradient-to-r from-transparent via-black/20 to-transparent"
        ></div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 gap-12 text-center">
            <div>
                <p class="text-xs tracking-wide text-gray-500 uppercase">
                    Year Level
                </p>
                <p class="mt-1 text-[20px] font-medium text-gray-800">
                    {{ student.yearLevel }}
                </p>
            </div>

            <div>
                <p class="text-xs tracking-wide text-gray-500 uppercase">
                    Department
                </p>
                <p class="mt-1 text-[20px] font-medium text-gray-800">
                    {{ student.department }}
                </p>
            </div>
        </div>
    </section>
</template>
