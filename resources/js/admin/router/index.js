import { createRouter, createWebHistory } from "vue-router";

import HomeView from "../views/Homeview.vue";
import LiveOperationsView from "../views/LiveOperationsView.vue";
import StudentsView from "../views/StudentsView.vue";

const routes = [
    {
        path: "/",
        redirect: "/admin/home",
    },
    {
        path: "/admin/home",
        name: "home",
        component: HomeView,
    },
    {
        path: "/admin/live",
        name: "live",
        component: LiveOperationsView,
    },
    {
        path: "/admin/students",
        name: "students",
        component: StudentsView,
    },
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
