<script setup>
import { ref, computed, watch } from "vue";
import Dialog from "primevue/dialog";
import Card from "primevue/card";

const props = defineProps({
    events: Array,
});

const selectedFilter = ref("ALL");
const highlightedEvents = ref(new Set());
const selectedEvent = ref(null);

const eventConfig = {
    RENTAL_PAID: {
        label: "Rental Started",
        icon: "pi pi-check-circle",
        color: "cyan",
    },
    RENTAL_ENDED: {
        label: "Rental Ended",
        icon: "pi pi-stop-circle",
        color: "emerald",
    },
    RENTAL_EXPIRED: {
        label: "Rental Expired",
        icon: "pi pi-clock",
        color: "amber",
    },
    PENALTY_PAID: {
        label: "Penalty Settled",
        icon: "pi pi-credit-card",
        color: "blue",
    },
};

const filteredEvents = computed(() => {
    if (selectedFilter.value === "ALL") return props.events;

    if (selectedFilter.value === "RENTAL") {
        return props.events.filter((e) =>
            ["RENTAL_PAID", "RENTAL_ENDED", "RENTAL_EXPIRED"].includes(
                e.event_type
            )
        );
    }

    if (selectedFilter.value === "PENALTY") {
        return props.events.filter((e) => e.event_type === "PENALTY_PAID");
    }

    return props.events;
});

watch(
    () => props.events,
    (newEvents, oldEvents) => {
        const oldIds = new Set((oldEvents || []).map((e) => e.id));

        newEvents.forEach((event) => {
            if (!oldIds.has(event.id)) {
                highlightedEvents.value.add(event.id);
                setTimeout(() => {
                    highlightedEvents.value.delete(event.id);
                }, 7000);
            }
        });
    }
);

function getInitials(student) {
    if (!student) return "?";
    return (
        student.first_name?.charAt(0)?.toUpperCase() +
        student.last_name?.charAt(0)?.toUpperCase()
    );
}

function formatTime(timestamp) {
    const diff = (Date.now() - new Date(timestamp)) / 1000;
    if (diff < 60) return "Just now";
    if (diff < 3600) return Math.floor(diff / 60) + "m ago";
    if (diff < 86400) return Math.floor(diff / 3600) + "h ago";
    return Math.floor(diff / 86400) + "d ago";
}

function formatDate(date) {
    return new Date(date).toLocaleString();
}
</script>

<template>
    <Card class="kpi-card bg-white dark:bg-gray-800">
        <template #content>
            <div class="kpi-body">
                <!-- Header -->
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="kpi-label">Today's Kiosk Events</h3>
                        <p class="kpi-meta">Live operational activity</p>
                    </div>

                    <router-link
                        to="/admin/logs"
                        class="text-xs text-cyan-600 dark:text-cyan-400 hover:underline"
                    >
                        View all →
                    </router-link>
                </div>

                <!-- Filters -->
                <div class="flex gap-2 mb-4">
                    <button
                        v-for="type in ['ALL', 'RENTAL', 'PENALTY']"
                        :key="type"
                        @click="selectedFilter = type"
                        class="text-xs px-3 py-1 rounded-md border transition"
                        :class="{
                            'bg-cyan-600 text-white border-cyan-600':
                                selectedFilter === type,
                            'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300':
                                selectedFilter !== type,
                        }"
                    >
                        {{ type }}
                    </button>
                </div>

                <!-- Scrollable List -->
                <div class="max-h-[40vh] overflow-y-auto pr-2">
                    <!-- Empty State -->
                    <div
                        v-if="filteredEvents.length === 0"
                        class="text-center py-10 text-gray-400 dark:text-gray-500"
                    >
                        <i class="pi pi-inbox text-3xl mb-2"></i>
                        <div class="text-sm">No kiosk activity today</div>
                    </div>

                    <!-- Events -->
                    <div
                        v-for="event in filteredEvents"
                        :key="event.id"
                        @click="selectedEvent = event"
                        class="flex items-center gap-3 py-2 border-b border-gray-200 dark:border-gray-700 last:border-0 cursor-pointer transition-colors"
                        :class="{
                            'bg-cyan-50 dark:bg-cyan-900/20':
                                highlightedEvents.has(event.id),
                            'hover:bg-gray-50 dark:hover:bg-gray-800/40': true,
                        }"
                    >
                        <!-- Avatar -->
                        <div
                            class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0"
                        >
                            <img
                                v-if="event.student?.photo_url"
                                :src="event.student.photo_url"
                                class="w-full h-full object-cover"
                            />

                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center bg-cyan-500 text-white text-[10px] font-semibold"
                            >
                                {{ getInitials(event.student) }}
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div
                                class="flex items-center gap-2 text-[13px] text-gray-900 dark:text-gray-100"
                            >
                                <i
                                    :class="[
                                        eventConfig[event.event_type]?.icon,
                                        'text-xs',
                                        eventConfig[event.event_type]?.color ===
                                            'cyan' &&
                                            'text-cyan-600 dark:text-cyan-400',
                                        eventConfig[event.event_type]?.color ===
                                            'emerald' &&
                                            'text-emerald-600 dark:text-emerald-400',
                                        eventConfig[event.event_type]?.color ===
                                            'amber' &&
                                            'text-amber-600 dark:text-amber-400',
                                        eventConfig[event.event_type]?.color ===
                                            'blue' &&
                                            'text-blue-600 dark:text-blue-400',
                                    ]"
                                ></i>

                                <span class="font-medium truncate">
                                    {{ event.student?.first_name }}
                                    {{ event.student?.last_name }}
                                </span>

                                <span class="text-gray-400">•</span>

                                <span
                                    class="truncate text-gray-600 dark:text-gray-300"
                                >
                                    {{ eventConfig[event.event_type]?.label }}
                                </span>
                            </div>

                            <div
                                class="text-xs text-gray-500 dark:text-gray-400 truncate"
                            >
                                Locker {{ event.locker?.locker_number }}
                            </div>
                        </div>

                        <!-- Time -->
                        <div
                            class="text-[11px] text-gray-400 dark:text-gray-500 whitespace-nowrap"
                        >
                            {{ formatTime(event.created_at) }}
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Card>

    <!-- Expand Modal -->
    <Dialog
        v-model:visible="selectedEvent"
        modal
        header="Event Details"
        class="w-96"
    >
        <div v-if="selectedEvent">
            <div class="mb-2 font-semibold">
                {{ selectedEvent.student?.first_name }}
                {{ selectedEvent.student?.last_name }}
            </div>
            <div class="text-sm text-gray-500">
                {{ eventConfig[selectedEvent.event_type]?.label }}
            </div>
            <div class="mt-3 text-xs text-gray-600 dark:text-gray-300">
                Full timestamp: {{ formatDate(selectedEvent.created_at) }}
            </div>
        </div>
    </Dialog>
</template>
