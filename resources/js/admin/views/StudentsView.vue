<script setup>
import { ref, onMounted, computed } from "vue";
import { useConfirm } from "primevue/useconfirm";
import StudentService from "../services/studentService";
import StudentForm from "../components/students/StudentForm.vue";
import { useToast } from "primevue/usetoast";
import axios from "axios";

const toast = useToast();

const students = ref([]);
const totalRecords = ref(0);
const loading = ref(false);
const rows = ref(10);

const statusOptions = [
    { label: "Active", value: "ACTIVE" },
    { label: "Suspended", value: "SUSPENDED" },
];

const search = ref("");
const status = ref(null);
const currentPage = ref(1);

const dialogVisible = ref(false);
const editMode = ref(false);
const selectedStudent = ref(null);

const confirm = useConfirm();

const summary = ref({
    total_students: 0,
    total_registered: 0,
    total_active: 0,
    total_suspended: 0,
});

async function fetchSummary() {
    try {
        const res = await axios.get("/admin/students/summary");
        summary.value = res.data;
    } catch (err) {
        console.error("Failed to fetch summary", err);
    }
}

async function fetchStudents(page = 1) {
    loading.value = true;

    const { data } = await StudentService.getStudents({
        page: page,
        per_page: rows.value, // IMPORTANT
        search: search.value,
        status: status.value,
    });

    students.value = data.data;
    totalRecords.value = data.total;
    currentPage.value = data.current_page;

    loading.value = false;
}
let searchTimer = null;
function handleSearchInput() {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        fetchStudents(1);
    }, 400); // 400ms delay
}

function getInitials(student) {
    const first = student.first_name?.charAt(0) ?? "";
    const last = student.last_name?.charAt(0) ?? "";
    return (first + last).toUpperCase();
}

function onPage(event) {
    currentPage.value = event.page + 1; // convert to 1-based
    rows.value = event.rows;

    fetchStudents(currentPage.value);
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
            try {
                await StudentService.deleteStudent(student.id);
                fetchStudents(currentPage.value);

                toast.add({
                    severity: "success",
                    summary: "Deleted",
                    detail: "Student deleted successfully.",
                    life: 5000,
                });
            } catch (error) {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Unable to delete student.",
                    life: 5000,
                });
            }
        },
    });
}

function handleSaved() {
    fetchStudents(currentPage.value);
    selectedStudent.value = null;
    fetchSummary();
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

onMounted(() => {
    fetchStudents();
    fetchSummary();
});
</script>

<template>
    <Card class="kpi-card bg-white dark:bg-gray-800">
        <template #content>
            <div class="kpi-body space-y-6">
                <!-- Header -->
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="kpi-label">Students</h3>
                        <p class="kpi-meta">
                            Manage student records and RFID registration
                        </p>
                    </div>

                    <Button
                        label="Add Student"
                        icon="pi pi-plus"
                        class="!bg-cyan-500 !hover:bg-cyan-600 !border-none !text-white"
                        @click="openCreate"
                    />
                </div>

                <!-- Filters -->
                <div class="flex gap-4">
                    <InputText
                        v-model="search"
                        placeholder="Search..."
                        class="w-64"
                        @input="handleSearchInput"
                    />

                    <Select
                        v-model="status"
                        :options="statusOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Filter Status"
                        class="w-48"
                        @change="fetchStudents(1)"
                    />
                </div>

                <!-- Summary -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="kpi-card">
                        <div class="kpi-body">
                            <div class="kpi-label">Total Students</div>
                            <div class="kpi-value text-cyan-600">
                                {{ summary.total_students }}
                            </div>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-body">
                            <div class="kpi-label">Registered (RFID)</div>
                            <div class="kpi-value text-cyan-600">
                                {{ summary.total_registered }}
                            </div>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-body">
                            <div class="kpi-label">Active</div>
                            <div class="kpi-value text-cyan-600">
                                {{ summary.total_active }}
                            </div>
                        </div>
                    </div>

                    <div class="kpi-card">
                        <div class="kpi-body">
                            <div class="kpi-label">Suspended</div>
                            <div class="kpi-value text-cyan-600">
                                {{ summary.total_suspended }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <DataTable
                    :value="students"
                    :lazy="true"
                    :paginator="true"
                    :rows="rows"
                    :totalRecords="totalRecords"
                    :first="(currentPage - 1) * rows"
                    :loading="loading"
                    @page="onPage"
                    stripedRows
                >
                    <Column header="Student">
                        <template #body="{ data }">
                            <div class="flex items-center gap-3">
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

                                <div>
                                    <div class="font-medium">
                                        {{ data.first_name }}
                                        {{ data.last_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ data.student_number }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column field="year_level" header="YEAR" />
                    <Column field="department" header="DEPARTMENT" />

                    <Column header="UID">
                        <template #body="{ data }">
                            <span
                                v-if="data.rfid_uid"
                                class="px-2 py-1 text-xs rounded-lg bg-gray-200"
                            >
                                {{ data.rfid_uid }}
                            </span>
                            <span v-else class="text-gray-400 text-sm">
                                Not Registered
                            </span>
                        </template>
                    </Column>

                    <Column header="Status">
                        <template #body="{ data }">
                            <span
                                class="px-2 py-1 text-xs rounded font-medium"
                                :class="{
                                    'bg-emerald-100 text-emerald-700':
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
                            <Button
                                icon="pi pi-pencil"
                                text
                                @click="openEdit(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                text
                                severity="danger"
                                @click="confirmDelete(data)"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </template>
    </Card>

    <!-- KEEPING FORM EXACTLY -->
    <StudentForm
        v-model:visible="dialogVisible"
        :student="selectedStudent"
        @saved="handleSaved"
    />
</template>
