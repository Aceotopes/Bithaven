<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";

const toast = useToast();
const confirm = useConfirm();

const cards = ref([]);
const loading = ref(false);
const search = ref("");

const showDialog = ref(false);
const editMode = ref(false);

const form = ref({
    id: null,
    card_label: "",
    rfid_uid: "",
    assigned_to: "",
    status: "ACTIVE",
});

async function fetchCards() {
    loading.value = true;
    try {
        const res = await axios.get("/admin/cards");
        cards.value = res.data;
        console.log("Cards response:", res.data);
    } catch (e) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to load cards.",
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
}

onMounted(fetchCards);

const filteredCards = computed(() => {
    if (!Array.isArray(cards.value)) return [];

    return cards.value.filter((c) => {
        const label = c.card_label ?? "";
        const uid = c.rfid_uid ?? "";

        return (
            label.toLowerCase().includes(search.value.toLowerCase()) ||
            uid.toLowerCase().includes(search.value.toLowerCase())
        );
    });
});

function openCreate() {
    editMode.value = false;
    form.value = {
        id: null,
        card_label: "",
        rfid_uid: "",
        assigned_to: "",
        status: "ACTIVE",
    };
    showDialog.value = true;
}

function openEdit(card) {
    editMode.value = true;
    form.value = { ...card };
    showDialog.value = true;
}

async function saveCard() {
    try {
        if (editMode.value) {
            await axios.put(`/admin/cards/${form.value.id}`, form.value);
            toast.add({
                severity: "success",
                summary: "Updated",
                detail: "Card updated successfully.",
                life: 2000,
            });
        } else {
            await axios.post("/admin/cards", form.value);
            toast.add({
                severity: "success",
                summary: "Created",
                detail: "Card registered successfully.",
                life: 2000,
            });
        }

        showDialog.value = false;
        fetchCards();
    } catch (e) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: e.response?.data?.message || "Operation failed.",
            life: 3000,
        });
    }
}

function deleteCard(card) {
    confirm.require({
        message: "Delete this card?",
        header: "Confirm",
        icon: "pi pi-exclamation-triangle",
        acceptClass: "p-button-danger",
        accept: async () => {
            await axios.delete(`/admin/cards/${card.id}`);
            toast.add({
                severity: "success",
                summary: "Deleted",
                detail: "Card deleted.",
                life: 2000,
            });
            fetchCards();
        },
    });
}

function toggleStatus(card) {
    const newStatus = card.status === "ACTIVE" ? "DISABLED" : "ACTIVE";
    axios
        .put(`/admin/cards/${card.id}`, { status: newStatus })
        .then(fetchCards);
}

function statusSeverity(status) {
    return status === "ACTIVE" ? "success" : "danger";
}
</script>

<template>
    <div class="space-y-6">
        <ConfirmDialog />
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold">Kiosk Admin Cards</h2>
                <p class="text-sm text-gray-500">
                    Manage RFID cards for kiosk administrative access.
                </p>
            </div>

            <Button
                icon="pi pi-plus"
                label="Register Card"
                @click="openCreate"
            />
        </div>

        <!-- Search -->
        <InputText
            v-model="search"
            placeholder="Search cards..."
            class="w-72"
        />

        <!-- Table -->
        <DataTable
            :value="filteredCards"
            :loading="loading"
            dataKey="id"
            stripedRows
        >
            <Column field="id" header="ID" style="width: 70px" sortable />

            <Column field="card_label" header="Label" sortable />

            <Column field="rfid_uid" header="RFID UID" />

            <Column field="assigned_to" header="Assigned To" />

            <Column header="Status">
                <template #body="{ data }">
                    <Tag
                        :value="data.status"
                        :severity="statusSeverity(data.status)"
                    />
                </template>
            </Column>

            <Column header="Actions" style="width: 200px">
                <template #body="{ data }">
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            text
                            @click="openEdit(data)"
                        />
                        <Button
                            icon="pi pi-power-off"
                            text
                            :severity="
                                data.status === 'ACTIVE' ? 'warning' : 'success'
                            "
                            @click="toggleStatus(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            text
                            severity="danger"
                            @click="deleteCard(data)"
                        />
                    </div>
                </template>
            </Column>

            <template #empty>
                <div class="text-center py-8 text-gray-500">
                    No cards registered.
                </div>
            </template>
        </DataTable>

        <!-- Create / Edit Dialog -->
        <Dialog
            v-model:visible="showDialog"
            modal
            :header="editMode ? 'Edit Card' : 'Register Card'"
            class="w-[500px]"
        >
            <div class="space-y-4">
                <div>
                    <label class="text-sm">Card Label</label>
                    <InputText v-model="form.card_label" class="w-full" />
                </div>

                <div v-if="!editMode">
                    <label class="text-sm">RFID UID</label>
                    <InputText v-model="form.rfid_uid" class="w-full" />
                </div>

                <div>
                    <label class="text-sm">Assigned To</label>
                    <InputText v-model="form.assigned_to" class="w-full" />
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Button label="Cancel" text @click="showDialog = false" />
                    <Button label="Save" @click="saveCard" />
                </div>
            </div>
        </Dialog>
    </div>
</template>
