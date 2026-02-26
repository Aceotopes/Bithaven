<script setup>
import { ref, watch, computed, onBeforeUnmount } from "vue";
import StudentService from "../../services/studentService";
import axios from "axios";

import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Dropdown from "primevue/dropdown";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";

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
        :header="isEdit ? 'Edit Student' : 'Add Student'"
        :style="{ width: '600px' }"
        @update:visible="handleDialogClose"
    >
        <div class="space-y-4">
            <!-- Photo -->
            <div class="flex flex-col items-center space-y-2">
                <img
                    v-if="previewUrl"
                    :src="previewUrl"
                    class="w-24 h-24 rounded-full object-cover border"
                />
                <input type="file" accept="image/*" @change="onFileChange" />
            </div>

            <InputText
                v-model="form.student_number"
                placeholder="Student Number"
                class="w-full"
            />
            <InputText
                v-model="form.first_name"
                placeholder="First Name"
                class="w-full"
            />
            <InputText
                v-model="form.middle_name"
                placeholder="Middle Name"
                class="w-full"
            />
            <InputText
                v-model="form.last_name"
                placeholder="Last Name"
                class="w-full"
            />
            <InputText
                v-model="form.year_level"
                placeholder="Year Level"
                class="w-full"
            />
            <InputText
                v-model="form.department"
                placeholder="Department"
                class="w-full"
            />
            <div class="flex gap-2 items-center">
                <InputText
                    v-model="form.rfid_uid"
                    placeholder="RFID UID"
                    class="w-full"
                />

                <Button
                    label="Scan"
                    icon="pi pi-id-card"
                    severity="info"
                    @click="startScan"
                    :loading="scanStatus === 'WAITING'"
                    v-if="scanStatus !== 'WAITING'"
                />

                <Button
                    label="Cancel"
                    icon="pi pi-times"
                    severity="danger"
                    @click="cancelScan"
                    v-if="scanStatus === 'WAITING'"
                />
            </div>

            <p v-if="scanStatus === 'WAITING'" class="text-sm text-gray-500">
                Waiting for student to scan at kiosk...
            </p>
            <p v-if="scanStatus === 'EXPIRED'" class="text-sm text-red-500">
                Scan expired. Please try again.
            </p>

            <Dropdown
                v-model="form.status"
                :options="['ACTIVE', 'INACTIVE', 'SUSPENDED']"
                placeholder="Status"
                class="w-full"
            />

            <div class="flex justify-end gap-2 pt-4">
                <Button label="Cancel" text @click="handleCancel" />
                <Button
                    :label="isEdit ? 'Update' : 'Create'"
                    :loading="loading"
                    @click="submit"
                />
            </div>
        </div>
    </Dialog>
</template>
