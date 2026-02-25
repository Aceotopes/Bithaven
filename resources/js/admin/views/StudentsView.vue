<script setup>
import { ref, onMounted, computed } from "vue";
import { useConfirm } from "primevue/useconfirm";
import StudentService from "../services/studentService";
import StudentForm from "../components/students/StudentForm.vue";

const students = ref([]);
const totalRecords = ref(0);
const loading = ref(false);

const search = ref("");
const status = ref(null);
const currentPage = ref(1);

const dialogVisible = ref(false);
const editMode = ref(false);
const selectedStudent = ref(null);

const confirm = useConfirm();

async function fetchStudents(page = 1) {
    loading.value = true;

    const { data } = await StudentService.getStudents({
        page,
        search: search.value,
        status: status.value,
    });

    students.value = data.data;
    totalRecords.value = data.total;
    currentPage.value = data.current_page;

    loading.value = false;
}

function getInitials(student) {
    const first = student.first_name?.charAt(0) ?? "";
    const last = student.last_name?.charAt(0) ?? "";
    return (first + last).toUpperCase();
}

function onPage(event) {
    fetchStudents(event.page + 1);
}

function openCreate() {
    editMode.value = false;
    selectedStudent.value = null;
    dialogVisible.value = true;
}

function openEdit(student) {
    editMode.value = true;
    selectedStudent.value = { ...student };
    dialogVisible.value = true;
}

function confirmDelete(student) {
    confirm.require({
        message: "Are you sure you want to delete this student?",
        header: "Confirm",
        accept: async () => {
            await StudentService.deleteStudent(student.id);
            fetchStudents(currentPage.value);
        },
    });
}

// FOR SUMMARY
const totalStudents = computed(() => totalRecords.value);

const totalRegistered = computed(
    () => students.value.filter((s) => s.rfid_uid).length
);

const totalActive = computed(
    () => students.value.filter((s) => s.status === "ACTIVE").length
);

const totalSuspended = computed(
    () => students.value.filter((s) => s.status === "SUSPENDED").length
);

onMounted(() => fetchStudents());
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold">Students</h1>
            <Button
                label="Add Student"
                icon="pi pi-plus"
                class="bg-cyan-500 border-cyan-500 hover:bg-cyan-600"
                @click="openCreate"
            />
        </div>

        <!-- Filters -->
        <div class="flex gap-4">
            <InputText
                v-model="search"
                placeholder="Search..."
                @input="fetchStudents(1)"
            />

            <Dropdown
                v-model="status"
                :options="['ACTIVE', 'INACTIVE', 'SUSPENDED']"
                placeholder="Filter Status"
                @change="fetchStudents(1)"
            />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Total Students -->
            <div class="kpi-card">
                <div class="kpi-body">
                    <div class="kpi-label">Total Students</div>
                    <div class="kpi-value text-cyan-600">
                        {{ totalStudents }}
                    </div>
                </div>
            </div>

            <!-- Registered -->
            <div class="kpi-card">
                <div class="kpi-body">
                    <div class="kpi-label">Registered (RFID)</div>
                    <div class="kpi-value text-cyan-600">
                        {{ totalRegistered }}
                    </div>
                </div>
            </div>

            <!-- Active -->
            <div class="kpi-card">
                <div class="kpi-body">
                    <div class="kpi-label">Active</div>
                    <div class="kpi-value text-cyan-600">
                        {{ totalActive }}
                    </div>
                </div>
            </div>

            <!-- Suspended -->
            <div class="kpi-card">
                <div class="kpi-body">
                    <div class="kpi-label">Suspended</div>
                    <div class="kpi-value text-cyan-600">
                        {{ totalSuspended }}
                    </div>
                </div>
            </div>
        </div>
        <!-- Table -->
        <DataTable
            :value="students"
            :lazy="true"
            :paginator="true"
            :rows="10"
            :totalRecords="totalRecords"
            :loading="loading"
            @page="onPage"
        >
            <Column header="Student">
                <template #body="{ data }">
                    <div class="flex items-center gap-3">
                        <!-- Avatar -->
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center text-white font-medium"
                            :class="data.photo_url ? '' : 'bg-cyan-500'"
                        >
                            <img
                                v-if="data.photo_url"
                                :src="`http://127.0.0.1:8000/storage/${data.photo_url}`"
                                class="w-10 h-10 rounded-full object-cover"
                            />
                            <span v-else>
                                {{ getInitials(data) }}
                            </span>
                        </div>

                        <!-- Name + Student # -->
                        <div>
                            <div class="font-medium">
                                {{ data.first_name }} {{ data.last_name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ data.student_number }}
                            </div>
                        </div>
                    </div>
                </template>
            </Column>
            <Column field="year_level" header="Year" />
            <Column field="department" header="Department" />
            <Column field="rfid_uid" header="RFID">
                <template #body="{ data }">
                    <span
                        v-if="data.rfid_uid"
                        class="px-2 py-1 text-xs rounded bg-cyan-100 text-cyan-700"
                    >
                        {{ data.rfid_uid }}
                    </span>
                    <span v-else class="text-gray-400 text-sm">
                        Not Registered
                    </span>
                </template>
            </Column>

            <Column field="status" header="Status">
                <template #body="{ data }">
                    <span
                        class="px-2 py-1 text-xs rounded font-medium"
                        :class="{
                            'bg-cyan-100 text-cyan-700':
                                data.status === 'ACTIVE',
                            'bg-yellow-100 text-yellow-700':
                                data.status === 'INACTIVE',
                            'bg-red-100 text-red-700':
                                data.status === 'SUSPENDED',
                        }"
                    >
                        {{ data.status }}
                    </span>
                </template>
            </Column>

            <Column header="Actions">
                <template #body="{ data }">
                    <Button icon="pi pi-pencil" text @click="openEdit(data)" />
                    <Button
                        icon="pi pi-trash"
                        text
                        severity="danger"
                        @click="confirmDelete(data)"
                    />
                </template>
            </Column>
        </DataTable>

        <ConfirmDialog />
    </div>
    <StudentForm
        v-model:visible="dialogVisible"
        :student="selectedStudent"
        @saved="fetchStudents(currentPage)"
    />
</template>
