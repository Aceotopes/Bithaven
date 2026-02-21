import { createRouter, createWebHistory } from "vue-router";

import HomeView from "../views/Homeview.vue";

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
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
