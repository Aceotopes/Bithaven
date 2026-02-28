<script setup>
import { ref, watch, computed, onBeforeUnmount } from "vue";
import StudentService from "../../services/studentService";
import axios from "axios";

import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Dropdown from "primevue/dropdown";
import Button from "primevue/button";
import FloatLabel from "primevue/floatlabel";
import { useToast } from "primevue/usetoast";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import InputMask from "primevue/inputmask";

const toast = useToast();

const scanId = ref(null);
let scanPollTimer = null;
const scanStatus = ref(null);

const props = defineProps({
    visible: Boolean,
    student: Object, // null for create, object for edit
});

const emit = defineEmits(["update:visible", "saved"]);

const isEdit = computed(() => !!props.student);

const form = ref({
    student_number: "",
    first_name: "",
    middle_name: "",
    last_name: "",
    year_level: "",
    department: "",
    rfid_uid: "",
    status: "ACTIVE",
});

const photoFile = ref(null);
const previewUrl = ref(null);
const loading = ref(false);

/* ------------------------------
   Watch for Edit Mode
------------------------------- */
watch(
    () => props.student,
    (val) => {
        if (val) {
            form.value = {
                student_number: val.student_number,
                first_name: val.first_name,
                middle_name: val.middle_name,
                last_name: val.last_name,
                year_level: val.year_level,
                department: val.department,
                rfid_uid: val.rfid_uid,
                status: val.status,
            };

            previewUrl.value = val.photo_url
                ? `http://127.0.0.1:8000/storage/${val.photo_url}`
                : null;

            photoFile.value = null;
        } else {
            resetForm();
        }
    },
    { immediate: true }
);

function handleCancel() {
    if (scanStatus.value === "WAITING") {
        cancelScan(); // cancel backend session
    }
    stopPolling();
    resetForm();
    emit("update:visible", false);
}

/* ------------------------------
   Reset
------------------------------- */
function resetForm() {
    form.value = {
        student_number: "",
        first_name: "",
        middle_name: "",
        last_name: "",
        year_level: "",
        department: "",
        rfid_uid: "",
        status: "ACTIVE",
    };
    photoFile.value = null;
    previewUrl.value = null;
}

/* ------------------------------
   Handle Image
------------------------------- */
function onFileChange(event) {
    const file = event.target.files[0];
    if (!file) return;

    photoFile.value = file;
    previewUrl.value = URL.createObjectURL(file);
}

function stopPolling() {
    clearInterval(scanPollTimer);
    scanPollTimer = null;
    scanId.value = null;
}

function handleDialogClose(val) {
    if (!val) {
        // Dialog is closing

        if (scanStatus.value === "WAITING") {
            cancelScan(); // cancel backend session
        }

        stopPolling();
        resetForm();
    }

    emit("update:visible", val);
}

/* ------------------------------
   Submit
------------------------------- */
async function submit() {
    if (loading.value) return;

    loading.value = true;

    const formData = new FormData();

    Object.keys(form.value).forEach((key) => {
        if (form.value[key] !== null && form.value[key] !== "") {
            formData.append(key, form.value[key]);
        }
    });

    if (photoFile.value) {
        formData.append("photo_url", photoFile.value);
    }

    try {
        if (isEdit.value) {
            await StudentService.updateStudent(props.student.id, formData);

            toast.add({
                severity: "success",
                summary: "Updated",
                detail: "Student updated successfully.",
                life: 5000,
            });
        } else {
            await StudentService.createStudent(formData);

            toast.add({
                severity: "success",
                summary: "Created",
                detail: "Student added successfully.",
                life: 5000,
            });
        }

        emit("saved");
        emit("update:visible", false);
        resetForm();
    } catch (error) {
        let message = "Something went wrong.";

        if (error.response) {
            // Laravel validation errors
            if (error.response.status === 422) {
                const errors = error.response.data.errors;

                message = Object.values(errors).flat().join(" ");
            }

            // Other server errors
            else if (error.response.data?.error) {
                message = error.response.data.error;
            }
        }

        toast.add({
            severity: "error",
            summary: "Error",
            detail: message,
            life: 5000,
        });
    } finally {
        loading.value = false;
    }
}

async function startScan() {
    try {
        const res = await axios.post("/admin/rfid/start");

        // CASE 1: Same admin already has session
        if (res.data.status === "ALREADY_ACTIVE") {
            scanId.value = res.data.scan_id;
            scanStatus.value = "WAITING";
            scanPollTimer = setInterval(pollScanResult, 2000);
            return;
        }

        // CASE 2: New session created
        if (res.data.status === "CREATED") {
            scanId.value = res.data.scan_id;
            scanStatus.value = "WAITING";
            scanPollTimer = setInterval(pollScanResult, 2000);

            toast.add({
                severity: "info",
                summary: "RFID Scan Started",
                detail: "Please tap the RFID card on the kiosk scanner.",
                life: 5000,
            });
        }

        if (res.data.status === "CANCELLED") {
            stopPolling();
            scanStatus.value = null;
        }
    } catch (err) {
        if (err.response?.status === 409) {
            toast.add({
                severity: "warn",
                summary: "Scanner Busy",
                detail: "RFID scanner is currently in use.",
                life: 5000,
            });
            return;
        }

        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Unable to start scan session.",
            life: 5000,
        });
    }
}

async function pollScanResult() {
    if (!scanId.value) return;

    try {
        const res = await axios.get(`/admin/rfid/${scanId.value}`);

        if (res.data.status === "COMPLETED") {
            form.value.rfid_uid = res.data.rfid_uid;

            scanStatus.value = "COMPLETED";

            stopPolling();

            toast.add({
                severity: "success",
                summary: "RFID Captured",
                detail: "Card successfully scanned.",
                life: 5000,
            });
        }

        if (res.data.status === "EXPIRED") {
            scanStatus.value = "EXPIRED";
            stopPolling();

            toast.add({
                severity: "warn",
                summary: "Scan Expired",
                detail: "RFID scan session expired.",
                life: 5000,
            });
        }
    } catch (err) {
        stopPolling();
    }
}

async function cancelScan() {
    if (!scanId.value) return;

    try {
        await axios.post(`/admin/rfid/${scanId.value}/cancel`);

        stopPolling();
        scanStatus.value = null;

        toast.add({
            severity: "info",
            summary: "Scan Cancelled",
            detail: "RFID scan has been cancelled.",
            life: 5000,
        });
    } catch (err) {
        console.error("Cancel failed", err);
    }
}

onBeforeUnmount(() => {
    stopPolling();
});
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :style="{ width: '900px' }"
        @update:visible="handleDialogClose"
    >
        <!-- HEADER -->
        <template #header>
            <div>
                <h2 class="text-lg font-semibold">
                    {{ isEdit ? "Edit Student" : "Add Student" }}
                </h2>
                <p class="text-xs text-surface-500">
                    Student identity and academic configuration
                </p>
            </div>
        </template>

        <!-- BODY -->
        <div
            class="max-h-[75vh] overflow-y-auto bg-gray-100 dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700"
        >
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <!-- ================= LEFT COLUMN ================= -->
                <div
                    class="md:col-span-1 flex flex-col items-center text-center gap-6 mt-2"
                >
                    <!-- Avatar (Clickable) -->
                    <label class="relative group cursor-pointer">
                        <div
                            class="w-35 h-35 rounded-full overflow-hidden border border-surface-600 dark:border-surface-700 bg-surface-100 dark:bg-surface-800 flex items-center justify-center transition group-hover:opacity-80"
                        >
                            <img
                                v-if="previewUrl"
                                :src="previewUrl"
                                class="w-full h-full object-cover"
                            />
                            <i
                                v-else
                                class="pi pi-user text-2xl text-surface-500"
                                style="font-size: 2.5rem"
                            ></i>
                        </div>

                        <!-- Hover Overlay -->
                        <div
                            class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition"
                        >
                            <span class="text-white text-xs font-medium">
                                Change
                            </span>
                        </div>

                        <input
                            type="file"
                            accept="image/*"
                            @change="onFileChange"
                            class="hidden"
                        />
                    </label>

                    <!-- Student Number (Centered Below Avatar) -->
                    <div>
                        <FloatLabel variant="on">
                            <InputMask
                                v-model="form.student_number"
                                mask="99-999999"
                                class="text-center font-semibold"
                                size="small"
                            />
                            <label class="text-xs">Student Number *</label>
                        </FloatLabel>
                    </div>
                </div>

                <!-- ================= RIGHT COLUMN ================= -->
                <div class="md:col-span-2 space-y-5 mt-1">
                    <!-- STUDENT INFORMATION -->
                    <div>
                        <h3
                            class="text-m font-semibold uppercase tracking-wide text-surface-400 mb-2"
                        >
                            Student Information
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <FloatLabel variant="on">
                                <InputText
                                    v-model="form.first_name"
                                    class="w-full"
                                />
                                <label class="text-sm">First Name *</label>
                            </FloatLabel>

                            <FloatLabel variant="on">
                                <InputText
                                    v-model="form.middle_name"
                                    class="w-full"
                                />
                                <label class="text-sm">Middle Name</label>
                            </FloatLabel>

                            <FloatLabel variant="on">
                                <InputText
                                    v-model="form.last_name"
                                    class="w-full"
                                />
                                <label class="text-sm">Last Name *</label>
                            </FloatLabel>
                        </div>
                    </div>

                    <!-- ACADEMIC INFORMATION -->
                    <div>
                        <h3
                            class="text-s font-semibold uppercase tracking-wide text-surface-400 mb-2"
                        >
                            Academic Information
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FloatLabel variant="on">
                                <Dropdown
                                    v-model="form.year_level"
                                    :options="['I', 'II', 'III', 'IV']"
                                    class="w-full"
                                />
                                <label>Year Level *</label>
                            </FloatLabel>

                            <FloatLabel variant="on">
                                <Dropdown
                                    v-model="form.department"
                                    :options="[
                                        'Computer Engineering',
                                        'Civil Engineering',
                                        'Electrical Engineering',
                                        'Mechanical Engineering',
                                    ]"
                                    class="w-full"
                                />
                                <label>Department *</label>
                            </FloatLabel>
                        </div>
                    </div>

                    <!-- ACCESS & RFID -->
                    <div>
                        <h3
                            class="text-s font-semibold uppercase tracking-wide text-surface-400 mb-2"
                        >
                            Access Credentials
                        </h3>

                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end"
                        >
                            <!-- Status -->
                            <FloatLabel variant="on">
                                <Dropdown
                                    v-model="form.status"
                                    :options="['ACTIVE', 'SUSPENDED']"
                                    class="w-full"
                                />
                                <label>Status *</label>
                            </FloatLabel>

                            <!-- RFID + Scan -->
                            <div class="flex gap-3 items-end">
                                <!-- RFID Field -->
                                <FloatLabel variant="on" class="flex-1">
                                    <InputText
                                        v-model="form.rfid_uid"
                                        :disabled="scanStatus === 'WAITING'"
                                        class="w-full"
                                        :class="[
                                            scanStatus === 'WAITING'
                                                ? 'bg-surface-200 dark:bg-surface-800 cursor-not-allowed'
                                                : '',
                                            scanStatus === 'COMPLETED'
                                                ? 'border-green-500'
                                                : '',
                                        ]"
                                    />
                                    <label>RFID UID *</label>
                                </FloatLabel>

                                <!-- Scan Button -->
                                <Button
                                    icon="pi pi-qrcode"
                                    variant="outlined"
                                    severity="info"
                                    class="h-[42px]"
                                    label="Scan"
                                    @click="startScan"
                                    :loading="scanStatus === 'WAITING'"
                                    v-if="scanStatus !== 'WAITING'"
                                />

                                <!-- Cancel Button -->
                                <div v-if="scanStatus === 'WAITING'">
                                    <Button
                                        icon="pi pi-times"
                                        severity="danger"
                                        class="h-[42px]"
                                        @click="cancelScan"
                                        v-if="scanStatus === 'WAITING'"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <template #footer>
            <div
                class="flex justify-between items-center dark:border-surface-800"
            >
                <!-- <Button
                    label="Cancel"
                    severity="secondary"
                    icon="pi pi-times"
                    @click="handleCancel"
                /> -->

                <Button
                    :label="isEdit ? 'Update Student' : 'Create Student'"
                    icon="pi pi-check"
                    class="!bg-cyan-500 hover:!bg-cyan-600 !border-none !text-white"
                    :loading="loading"
                    @click="submit"
                />
            </div>
        </template>
    </Dialog>
</template>
