import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/js/kiosk/app.js", "resources/js/admin/app.js"],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],

    server: {
        host: true,
        port: 5173,
        strictPort: true,

        cors: {
            origin: "*", // Allow all origins
        },

        hmr: {
            host: "192.168.10.121",
        },
    },
});
