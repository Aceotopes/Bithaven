<script setup>
import { ref, watch, computed } from "vue";
import StudentService from "../../services/studentService";

import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Dropdown from "primevue/dropdown";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";

const toast = useToast();

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
                life: 3000,
            });
        } else {
            await StudentService.createStudent(formData);

            toast.add({
                severity: "success",
                summary: "Created",
                detail: "Student added successfully.",
                life: 3000,
            });
        }

        emit("saved");
        emit("update:visible", false);
        resetForm();
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Something went wrong.",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="isEdit ? 'Edit Student' : 'Add Student'"
        :style="{ width: '600px' }"
        @update:visible="(val) => emit('update:visible', val)"
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
            <InputText
                v-model="form.rfid_uid"
                placeholder="RFID UID"
                class="w-full"
            />

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
