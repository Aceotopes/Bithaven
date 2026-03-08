<script setup>
import { ref, onMounted, onUnmounted } from "vue";

const now = ref(new Date());
let timer = null;

onMounted(() => {
    timer = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

function formatTime(date) {
    return date.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
}

function formatDate(date) {
    return date.toLocaleDateString([], {
        weekday: "long",
        month: "long",
        day: "numeric",
    });
}
</script>

<template>
    <footer
        class="fixed bottom-0 left-0 w-full bg-slate-700 text-slate-200 border-t border-slate-700 z-40 shadow-[0_-8px_30px_rgba(0,0,0,0.25)]"
    >
        <!-- TOP ROW -->
        <div
            class="flex items-center justify-between px-12 h-[42px] text-[22px]"
        >
            <!-- CLOCK -->
            <div class="font-mono text-slate-250">
                {{ formatDate(now) }} · {{ formatTime(now) }}
            </div>

            <!-- SYSTEM NAME -->
            <div class="font-semibold tracking-wide">
                <span class="text-slate-200"> BitHaven </span>
                <span class="text-slate-400"> Smart Locker System v1.0 </span>
            </div>
        </div>

        <!-- TICKER ROW -->
        <div
            class="overflow-hidden border-t border-slate-700 h-[42px] bg-slate-900"
        >
            <div class="ticker-track">
                <div class="ticker-block text-slate-350">
                    Developed by: Shenine Bautista • Rhoda Lagran • Janssen
                    Natino • Jarin Pastor • Alvin Ramelb • Argee Vizcarra
                </div>

                <div class="ticker-block text-slate-150">
                    Unclaimed lockers after expiration will incure a penalty |
                    always close the locker door after use | For inquiries,
                    contact the Computer Engineering Department.
                </div>

                <div class="ticker-block text-slate-350">
                    Students love affordable meals! Try the delicious and
                    budget-friendly steaks at Steak to Juan Laoag, Airport Road.
                </div>

                <div class="ticker-block text-slate-350">
                    Developed by: Shenine Bautista • Rhoda Lagran • Janssen
                    Natino • Jarin Pastor • Alvin Ramelb • Argee Vizcarra
                </div>

                <div class="ticker-block text-slate-150">
                    Unclaimed lockers after expiration will incure a penalty |
                    always close the locker door after use | For inquiries,
                    contact the Computer Engineering Department.
                </div>

                <div class="ticker-block text-slate-350">
                    Students love affordable meals! Try the delicious and
                    budget-friendly steaks at Steak to Juan Laoag, Airport Road.
                </div>
            </div>
        </div>
    </footer>
</template>

<style>
.ticker-wrapper {
    overflow: hidden;
    width: 100%;
    position: relative;
}

.ticker-track {
    display: flex;
    width: max-content;
    animation: tickerMove 40s linear infinite;
}

.ticker-block {
    white-space: nowrap;
    padding-right: 120px;
    font-size: 22px;
}

@keyframes tickerMove {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}
</style>
