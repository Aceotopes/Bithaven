<script setup>
import { ref, watch, computed } from "vue";
import { useAuthStore } from "@/admin/stores/auth";
import axios from "axios";
import { useToast } from "primevue/usetoast";
import FloatLabel from "primevue/floatlabel";
const props = defineProps({
    visible: Boolean,
});

const emit = defineEmits(["update:visible"]);

const auth = useAuthStore();
const toast = useToast();

const fileInput = ref(null);
const selectedFile = ref(null);
const removePhotoFlag = ref(false);

const adminInitial = computed(
    () => auth.admin?.name?.charAt(0)?.toUpperCase() || "A"
);

const previewPhoto = ref(null);

function triggerFileInput() {
    fileInput.value.click();
}

function onFileChange(event) {
    const file = event.target.files[0];
    if (!file) return;

    selectedFile.value = file;
    removePhotoFlag.value = false;
    previewPhoto.value = URL.createObjectURL(file);
}

function removePhoto() {
    selectedFile.value = null;
    removePhotoFlag.value = true;
    previewPhoto.value = null;
}

const activeTab = ref("profile");

const profileForm = ref({
    name: "",
    username: "",
    photo: null,
});

const passwordForm = ref({
    current_password: "",
    new_password: "",
    new_password_confirmation: "",
});

watch(
    () => props.visible,
    (val) => {
        if (val && auth.admin) {
            profileForm.value.name = auth.admin.name;
            profileForm.value.username = auth.admin.username;
        }
    }
);
watch(
    () => auth.admin,
    (admin) => {
        if (admin?.photo_url) {
            previewPhoto.value = `/storage/${admin.photo_url}`;
        } else {
            previewPhoto.value = null;
        }
    },
    { immediate: true }
);

function close() {
    emit("update:visible", false);
}

async function updateProfile() {
    const formData = new FormData();

    formData.append("name", profileForm.value.name);
    formData.append("username", profileForm.value.username);

    if (selectedFile.value) {
        formData.append("photo", selectedFile.value);
    }

    if (removePhotoFlag.value) {
        formData.append("remove_photo", "1");
    }

    try {
        const res = await axios.post("/admin/profile/update", formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        auth.admin = res.data.admin;

        toast.add({
            severity: "success",
            summary: "Updated",
            detail: "Profile updated successfully.",
            life: 3000,
        });
    } catch (err) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to update profile.",
            life: 3000,
        });
    }
}

async function changePassword() {
    if (
        passwordForm.value.new_password !==
        passwordForm.value.new_password_confirmation
    ) {
        toast.add({
            severity: "warn",
            summary: "Mismatch",
            detail: "Passwords do not match.",
            life: 3000,
        });
        return;
    }

    try {
        await axios.post("/admin/profile/change-password", passwordForm.value);

        toast.add({
            severity: "success",
            summary: "Password Changed",
            detail: "Your password has been updated.",
            life: 3000,
        });

        passwordForm.value = {
            current_password: "",
            new_password: "",
            new_password_confirmation: "",
        };

        close();
    } catch (err) {
        console.log(err.response?.data);

        toast.add({
            severity: "error",
            summary: "Error",
            detail: err.response?.data?.message || "Password change failed.",
            life: 4000,
        });
    }
}
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :style="{ width: '400px' }"
        @update:visible="emit('update:visible', $event)"
    >
        <template #header>
            <div class="flex items-center gap-3">
                <i class="pi pi-user text-lg"></i>
                <span class="font-semibold text-lg">Account Settings</span>
            </div>
        </template>

        <div class="space-y-6 mt-4">
            <!-- Tabs -->
            <div class="flex gap-4 border-b pb-2 border-gray-200">
                <button
                    :class="
                        activeTab === 'profile'
                            ? 'font-semibold border-b-2 border-cyan-500'
                            : ''
                    "
                    @click="activeTab = 'profile'"
                >
                    Profile
                </button>
                <button
                    :class="
                        activeTab === 'password'
                            ? 'font-semibold border-b-2 border-cyan-500'
                            : ''
                    "
                    @click="activeTab = 'password'"
                >
                    Change Password
                </button>
            </div>

            <!-- AVATAR UPLOAD -->
            <div class="flex justify-center mb-6">
                <div class="relative group">
                    <div
                        class="w-28 h-28 rounded-full overflow-hidden cursor-pointer border-4 border-gray-200 hover:opacity-80 transition"
                        @click="triggerFileInput"
                    >
                        <img
                            v-if="previewPhoto"
                            :src="previewPhoto"
                            class="w-full h-full object-cover"
                        />

                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center bg-cyan-500 text-white font-semibold"
                            style="font-size: 3rem"
                        >
                            {{ adminInitial }}
                        </div>
                    </div>

                    <button
                        v-if="previewPhoto"
                        @click.stop="removePhoto"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-7 h-7 flex items-center justify-center shadow hover:bg-red-600"
                    >
                        ✕
                    </button>

                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="onFileChange"
                    />
                </div>
            </div>

            <!-- Profile Tab -->
            <div v-if="activeTab === 'profile'" class="space-y-4">
                <div class="flex flex-col items-center gap-6">
                    <FloatLabel class="w-80" variant="on">
                        <InputText v-model="profileForm.name" class="w-full" />
                        <label>Name</label>
                    </FloatLabel>

                    <div>
                        <FloatLabel class="w-80" variant="on">
                            <label class="text-sm">Username</label>
                            <InputText
                                v-model="profileForm.username"
                                class="w-full"
                            />
                        </FloatLabel>
                    </div>
                </div>

                <div class="flex justify-center gap-2 pt-4">
                    <Button
                        label="Cancel"
                        text
                        @click="close"
                        class="!text-cyan-500"
                    />
                    <Button
                        label="Save Changes"
                        @click="updateProfile"
                        class="!bg-cyan-500 !text-white"
                    />
                </div>
            </div>

            <!-- Password Tab -->

            <div
                v-if="activeTab === 'password'"
                class="flex flex-col items-center gap-6"
            >
                <!-- Fields -->
                <FloatLabel class="w-80" variant="on">
                    <Password
                        v-model="passwordForm.current_password"
                        toggleMask
                        class="w-full"
                        fluid
                        :feedback="false"
                    />
                    <label>Current Password</label>
                </FloatLabel>

                <FloatLabel class="w-80" variant="on">
                    <Password
                        v-model="passwordForm.new_password"
                        toggleMask
                        class="w-full"
                        fluid
                    />
                    <label>New Password</label>
                </FloatLabel>

                <FloatLabel class="w-80" variant="on">
                    <Password
                        v-model="passwordForm.new_password_confirmation"
                        toggleMask
                        class="w-full"
                        fluid
                    />
                    <label>Confirm New Password</label>
                </FloatLabel>

                <!-- Buttons -->
                <div class="flex gap-3 pt-2">
                    <Button
                        label="Cancel"
                        text
                        @click="close"
                        class="!text-cyan-500"
                    />
                    <Button
                        label="Update Password"
                        @click="changePassword"
                        class="!bg-cyan-500 !text-white"
                    />
                </div>
            </div>
        </div>
    </Dialog>
</template>
