<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import FloatLabel from "primevue/floatlabel";
import Password from "primevue/password";
import InputText from "primevue/inputtext";
import Dialog from "primevue/dialog";
import Button from "primevue/button";

const toast = useToast();
const confirm = useConfirm();

const cards = ref([]);
const loading = ref(false);
const search = ref("");

const showDialog = ref(false);
const editMode = ref(false);

const scanId = ref(null);
const scanning = ref(false);
const scanPollTimer = null;

const form = ref({
    id: null,
    card_label: "",
    rfid_uid: "",
    assigned_to: "",
    status: "ACTIVE",
});

const showPinDialog = ref(false);

const pinForm = ref({
    current_pin: "",
    new_pin: "",
    confirm_pin: "",
});

async function startScan() {
    try {
        const res = await axios.post("/admin/rfid/start");

        scanId.value = res.data.scan_id;
        scanning.value = true;
        scanPollTimer = setInterval(pollScanResult, 2000);

        toast.add({
            severity: "info",
            summary: "RFID Scan Started",
            detail: "Please tap the RFID card on the kiosk scanner.",
            life: 5000,
        });
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
    }
}

async function pollScanResult() {
    if (!scanId.value) return;

    try {
        const res = await axios.get(`/admin/rfid/${scanId.value}`);

        if (res.data.status === "COMPLETED") {
            form.value.rfid_uid = res.data.rfid_uid;

            stopScan();
            toast.add({
                severity: "success",
                summary: "Card Detected",
                detail: "RFID UID captured successfully.",
                life: 5000,
            });
        }

        if (res.data.status === "EXPIRED") {
            stopScan();
            toast.add({
                severity: "warn",
                summary: "Scan Expired",
                detail: "RFID scan timed out.",
                life: 5000,
            });
        }
    } catch (e) {
        stopScan();
    }
}

async function cancelScan() {
    if (!scanId.value) return;

    await axios.post(`/admin/rfid/${scanId.value}/cancel`);
    stopScan();
}

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
            life: 5000,
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
                life: 5000,
            });
        } else {
            await axios.post("/admin/cards", form.value);
            toast.add({
                severity: "success",
                summary: "Created",
                detail: "Card registered successfully.",
                life: 5000,
            });
        }

        showDialog.value = false;
        fetchCards();
    } catch (e) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: e.response?.data?.message || "Operation failed.",
            life: 5000,
        });
    }
}

async function updatePin() {
    if (pinForm.value.new_pin !== pinForm.value.confirm_pin) {
        toast.add({
            severity: "warn",
            summary: "Mismatch",
            detail: "PINs do not match.",
            life: 3000,
        });
        return;
    }

    try {
        await axios.post("/admin/settings/emergency-pin", {
            current_pin: pinForm.value.current_pin,
            new_pin: pinForm.value.new_pin,
        });

        toast.add({
            severity: "success",
            summary: "PIN Updated",
            detail: "Emergency PIN changed successfully.",
            life: 3000,
        });

        showPinDialog.value = false;

        pinForm.value = {
            current_pin: "",
            new_pin: "",
            confirm_pin: "",
        };
    } catch (err) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: err.response?.data?.message || "Failed to update PIN.",
            life: 4000,
        });
    }
}

function deleteCard(card) {
    confirm.require({
        group: "action",
        message: "Delete this card?",
        header: "Confirm Action",
        icon: "pi pi-exclamation-triangle",
        acceptClass: "p-button-danger",
        accept: async () => {
            await axios.delete(`/admin/cards/${card.id}`);
            toast.add({
                severity: "success",
                summary: "Deleted",
                detail: "Card deleted.",
                life: 5000,
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

function stopScan() {
    if (scanPollTimer) {
        clearInterval(scanPollTimer);
        scanPollTimer = null;
    }

    scanId.value = null;
    scanning.value = false;
}

watch(showDialog, (val) => {
    if (!val) {
        stopScan();
    }
});
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

            <div class="flex gap-2">
                <Button
                    icon="pi pi-key"
                    label="Change PIN"
                    class="!bg-cyan-500 hover:!bg-cyan-600 text-white"
                    @click="showPinDialog = true"
                />

                <Button
                    icon="pi pi-plus"
                    label="Register Card"
                    @click="openCreate"
                    class="!bg-cyan-500 hover:!bg-cyan-600 text-white"
                />
            </div>
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

                <div v-if="!editMode" class="space-y-2">
                    <label class="text-sm">RFID UID</label>

                    <div class="flex gap-2">
                        <InputText
                            v-model="form.rfid_uid"
                            class="w-full"
                            placeholder="Scan card to auto-fill"
                            :disabled="scanning"
                        />

                        <Button
                            icon="pi pi-qrcode"
                            variant="outlined"
                            :label="scanning ? 'Scanning' : 'Scan'"
                            :severity="scanning ? 'info' : 'info'"
                            @click="startScan"
                            :disabled="scanning"
                        />
                    </div>

                    <div v-if="scanning" class="text-sm text-amber-600">
                        Waiting for RFID tap...
                        <Button
                            label="Cancel"
                            text
                            severity="danger"
                            @click="cancelScan"
                        />
                    </div>
                </div>

                <div>
                    <label class="text-sm">Assigned To</label>
                    <InputText v-model="form.assigned_to" class="w-full" />
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Button
                        label="Cancel"
                        text
                        @click="showDialog = false"
                        class="!text-cyan-500"
                    />
                    <Button
                        label="Save"
                        @click="saveCard"
                        class="!bg-cyan-500 hover:!bg-cyan-600 !text-white border-none"
                    />
                </div>
            </div>
        </Dialog>

        <!-- Change Pin -->
        <Dialog
            v-model:visible="showPinDialog"
            modal
            header="Change Emergency PIN"
            class="w-[400px]"
        >
            <div class="flex flex-col items-center gap-6 mt-4">
                <FloatLabel class="w-80" variant="on">
                    <Password
                        v-model="pinForm.current_pin"
                        toggleMask
                        class="w-full"
                        fluid
                        :feedback="false"
                    />
                    <label>Current PIN</label>
                </FloatLabel>

                <FloatLabel class="w-80" variant="on">
                    <Password
                        v-model="pinForm.new_pin"
                        toggleMask
                        class="w-full"
                        fluid
                        :feedback="false"
                    />
                    <label>New PIN</label>
                </FloatLabel>

                <FloatLabel class="w-80" variant="on">
                    <Password
                        v-model="pinForm.confirm_pin"
                        toggleMask
                        class="w-full"
                        fluid
                        :feedback="false"
                    />
                    <label>Confirm PIN</label>
                </FloatLabel>

                <div class="flex gap-3 pt-2">
                    <Button
                        label="Cancel"
                        text
                        @click="showPinDialog = false"
                        class="!text-cyan-500"
                    />
                    <Button
                        label="Update PIN"
                        @click="updatePin"
                        class="!bg-red-500 !text-white"
                    />
                </div>
            </div>
        </Dialog>
    </div>
</template>
