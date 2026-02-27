<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { useAuthStore } from "../../stores/auth";

import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Dropdown from "primevue/dropdown";
import ConfirmDialog from "primevue/confirmdialog";

const toast = useToast();
const confirm = useConfirm();
const auth = useAuthStore();

const admins = ref([]);
const loading = ref(false);

const dialogVisible = ref(false);
const isEdit = ref(false);
const selectedAdmin = ref(null);

const form = ref({
    username: "",
    name: "",
    password: "",
    role: "ADMIN",
    status: "ACTIVE",
});

const roles = ["ADMIN", "SUPER_ADMIN"];
const statuses = ["ACTIVE", "INACTIVE"];

const filteredAdmins = computed(() => {
    if (!auth.admin) return admins.value;
    return admins.value.filter((admin) => admin.id !== auth.admin.id);
});

/* =========================
   FETCH ADMINS
========================= */
async function fetchAdmins() {
    loading.value = true;
    try {
        const res = await axios.get("/admin/admins");
        admins.value = res.data;
    } catch (err) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to load admins",
        });
    } finally {
        loading.value = false;
    }
}

onMounted(fetchAdmins);

/* =========================
   OPEN CREATE
========================= */
function openCreate() {
    isEdit.value = false;
    form.value = {
        username: "",
        name: "",
        password: "",
        role: "ADMIN",
        status: "ACTIVE",
    };
    dialogVisible.value = true;
}

/* =========================
   OPEN EDIT
========================= */
function openEdit(admin) {
    isEdit.value = true;
    selectedAdmin.value = admin;

    form.value = {
        username: admin.username,
        name: admin.name,
        password: "",
        role: admin.role,
        status: admin.status,
    };

    dialogVisible.value = true;
}

/* =========================
   SAVE (CREATE / UPDATE)
========================= */
async function saveAdmin() {
    try {
        if (isEdit.value) {
            await axios.put(
                `/admin/admins/${selectedAdmin.value.id}`,
                form.value
            );

            toast.add({
                severity: "success",
                summary: "Updated",
                detail: "Admin updated successfully",
            });
        } else {
            await axios.post("/admin/admins", form.value);

            toast.add({
                severity: "success",
                summary: "Created",
                detail: "Admin created successfully",
            });
        }

        dialogVisible.value = false;
        fetchAdmins();
    } catch (err) {
        if (err.response?.status === 422) {
            toast.add({
                severity: "warn",
                summary: "Validation Error",
                detail:
                    err.response.data.message || "Please check input fields.",
                life: 4000,
            });
        } else {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Something went wrong",
            });
        }
    }
}

/* =========================
   DELETE
========================= */
function deleteAdmin(admin) {
    confirm.require({
        message: `Are you sure you want to delete ${admin.name}?`,
        header: "Delete Administrator",
        icon: "pi pi-trash",
        acceptLabel: "Yes, Delete",
        rejectLabel: "Cancel",
        acceptIcon: "pi pi-check",
        rejectIcon: "pi pi-times",
        acceptClass: "p-button-danger",
        rejectClass: "p-button-secondary",
        accept: async () => {
            try {
                await axios.delete(`/admin/admins/${admin.id}`);
                toast.add({
                    severity: "success",
                    summary: "Deleted",
                    detail: "Admin deleted successfully",
                });
                fetchAdmins();
            } catch (err) {
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        err.response?.data?.message ||
                        "Cannot delete this admin",
                });
            }
        },
    });
}
</script>

<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold">Manage Admins</h3>
            <Button label="New Admin" icon="pi pi-plus" @click="openCreate" />
        </div>

        <DataTable
            :value="filteredAdmins"
            :loading="loading"
            dataKey="id"
            responsiveLayout="scroll"
        >
            <Column field="id" header="ID" sortable />
            <Column field="username" header="Username" sortable />
            <Column field="name" header="Name" sortable />
            <Column field="role" header="Role">
                <template #body="{ data }">
                    <Tag
                        :value="data.role"
                        :severity="
                            data.role === 'SUPER_ADMIN' ? 'danger' : 'info'
                        "
                    />
                </template>
            </Column>
            <Column field="status" header="Status" sortable />
            <Column header="Actions">
                <template #body="{ data }">
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            text
                            @click="openEdit(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            text
                            severity="danger"
                            @click="deleteAdmin(data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <!-- CREATE / EDIT DIALOG -->
        <Dialog
            v-model:visible="dialogVisible"
            modal
            :header="isEdit ? 'Edit Admin' : 'Create Admin'"
            :style="{ width: '450px' }"
        >
            <div class="space-y-4">
                <InputText
                    v-model="form.username"
                    placeholder="Username"
                    :disabled="isEdit"
                    class="w-full"
                />

                <InputText
                    v-model="form.name"
                    placeholder="Full Name"
                    class="w-full"
                />

                <InputText
                    v-model="form.password"
                    type="password"
                    placeholder="Password"
                    class="w-full"
                />

                <Dropdown v-model="form.role" :options="roles" class="w-full" />

                <Dropdown
                    v-if="isEdit"
                    v-model="form.status"
                    :options="statuses"
                    class="w-full"
                />

                <div class="flex justify-end gap-2 pt-4">
                    <Button
                        label="Cancel"
                        text
                        @click="dialogVisible = false"
                    />
                    <Button label="Save" @click="saveAdmin" />
                </div>
            </div>
        </Dialog>
    </div>
</template>
