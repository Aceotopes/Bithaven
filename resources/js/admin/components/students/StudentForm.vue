<script setup>
import { ref, watch, computed } from "vue";
import StudentService from "../../services/studentService";

import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Dropdown from "primevue/dropdown";
import Button from "primevue/button";

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
            form.value = { ...val };
            previewUrl.value = val.photo_url
                ? `http://127.0.0.1:8000/storage/${val.photo_url}`
                : null;
        } else {
            resetForm();
        }
    },
    { immediate: true }
);

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
    loading.value = true;

    const formData = new FormData();

    Object.keys(form.value).forEach((key) => {
        if (form.value[key] !== null) {
            formData.append(key, form.value[key]);
        }
    });

    if (photoFile.value) {
        formData.append("photo_url", photoFile.value);
    }

    try {
        if (isEdit.value) {
            await StudentService.updateStudent(props.student.id, formData);
        } else {
            await StudentService.createStudent(formData);
        }

        emit("saved");
        emit("update:visible", false);
        resetForm();
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
                <Button
                    label="Cancel"
                    text
                    @click="emit('update:visible', false)"
                />
                <Button
                    :label="isEdit ? 'Update' : 'Create'"
                    :loading="loading"
                    @click="submit"
                />
            </div>
        </div>
    </Dialog>
</template>
