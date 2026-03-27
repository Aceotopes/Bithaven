import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";

import LoginView from "../views/LoginView.vue";
import AppLayout from "../layout/AppLayout.vue";
import HomeView from "../views/homeview.vue";
import LiveOperationsView from "../views/LiveOperationsView.vue";
import StudentsView from "../views/StudentsView.vue";
import FinancialsView from "../views/FinancialsView.vue";
import LogsEvents from "../views/LogsEvents.vue";

const routes = [
    {
        path: "/",
        redirect: "/admin/login",
    },
    {
        path: "/admin/login",
        component: LoginView,
    },
    {
        path: "/admin",
        component: AppLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: "",
                redirect: "/admin/home", // 👈 THIS FIXES IT
            },
            {
                path: "home",
                component: HomeView,
            },
            {
                path: "live",
                component: LiveOperationsView,
            },
            {
                path: "students",
                component: StudentsView,
            },
            {
                path: "financials",
                component: FinancialsView,
            },
            {
                path: "logs",
                component: LogsEvents,
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    const auth = useAuthStore();

    if (to.meta.requiresAuth && !auth.token) {
        return "/admin/login";
    }

    return true;
});

export default router;
