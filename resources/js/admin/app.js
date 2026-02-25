import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";

import PrimeVue from "primevue/config";
import Aura from "@primeuix/themes/aura"; // use Aura instead of Lara
import ConfirmDialog from "primevue/confirmdialog";
import ConfirmationService from "primevue/confirmationservice";
import Tooltip from "primevue/tooltip";

import Chart from "primevue/chart";
import "chart.js/auto";

import "primeicons/primeicons.css";
import "../../css/app.css";

import axios from "axios";
axios.defaults.baseURL = "http://127.0.0.1:8000/api";

axios.defaults.headers.common["Authorization"] =
    "Bearer 3|iUc5mMZXfB6Q1KomtIGxWUxEyiQniXBuHSTACUqP4d78bf6e";

const app = createApp(App);
app.use(router);

app.use(PrimeVue, {
    theme: {
        preset: Aura,
        options: {
            darkModeSelector: ".dark",
        },
    },
});
app.use(ConfirmationService);
app.component("ConfirmDialog", ConfirmDialog);
app.directive("tooltip", Tooltip);
app.component("Chart", Chart);
app.mount("#admin-app");
