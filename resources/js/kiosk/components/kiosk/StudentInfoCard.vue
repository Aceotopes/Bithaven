<script setup>
// UI-ONLY SAMPLE DATA
// ----------------------------------------
// When backend is connected later, replace
// these constants with props or store data.
// ----------------------------------------

const student = {
    name: "Ace Argee F. Vizcarra", // ← replace with DB value later
    studentNumber: "22-150570", // ← replace with DB value later
    yearLevel: "3rd Year", // ← replace with DB value later
    department: "Computer Engineering Dept", // ← replace with DB value later
};
</script>

<template>
    <section
        class="max-w-[880px] mx-auto rounded-3xl bg-white/80 backdrop-blur-xl shadow-[0_16px_40px_rgba(0,0,0,0.15)] px-12 py-10"
    >
        <div class="flex items-center gap-10 mx-auto">
            <!-- Student Picture -->
            <div
                class="w-28 h-28 rounded-full bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-slate-500 text-4xl font-semibold"
            >
                <!-- Placeholder Initial -->
                A
                <!-- 
          Replace this block with <img /> later:
          <img :src="student.photoUrl" ... />
        -->
            </div>

            <!-- Student Details -->
            <div class="max-w-[560px]">
                <!-- Full Name -->
                <p class="text-[28px] font-semibold text-gray-900">
                    {{ student.name }}
                </p>

                <!-- Student Number -->
                <p class="mt-1 font-mono text-[18px] text-gray-600">
                    STUDENT NO: {{ student.studentNumber }}
                </p>

                <!-- Divider -->
                <div
                    class="my-4 h-[2px] w-32 bg-gradient-to-r from-transparent via-black/20 to-transparent"
                ></div>

                <!-- Additional Info -->
                <div class="flex gap-12">
                    <div>
                        <p
                            class="text-xs tracking-wide text-gray-500 uppercase"
                        >
                            Year Level
                        </p>
                        <p class="text-[18px] font-medium text-gray-800">
                            {{ student.yearLevel }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs tracking-wide text-gray-500 uppercase"
                        >
                            Department
                        </p>
                        <p class="text-[18px] font-medium text-gray-800">
                            {{ student.department }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
