import { defineStore } from "pinia";
import axios from "axios";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        token: localStorage.getItem("admin_token") || null,
        admin: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isSuperAdmin: (state) => state.admin?.role === "SUPER_ADMIN",
    },

    actions: {
        async login(credentials) {
            const response = await axios.post("/admin/login", credentials);

            this.token = response.data.token;
            this.admin = response.data.admin;

            localStorage.setItem("admin_token", this.token);

            axios.defaults.headers.common[
                "Authorization"
            ] = `Bearer ${this.token}`;
        },

        async logout() {
            await axios.post("/admin/logout");

            this.token = null;
            this.admin = null;

            localStorage.removeItem("admin_token");
            delete axios.defaults.headers.common["Authorization"];
        },

        async initialize() {
            if (!this.token) return;

            axios.defaults.headers.common[
                "Authorization"
            ] = `Bearer ${this.token}`;

            try {
                const response = await axios.get("/admin/me");
                this.admin = response.data.admin;
            } catch (error) {
                this.logout();
            }
        },
    },
});
