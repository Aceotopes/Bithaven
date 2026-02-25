import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";

import PrimeVue from "primevue/config";
import Aura from "@primeuix/themes/aura"; // use Aura instead of Lara
import ConfirmationService from "primevue/confirmationservice";
import ToastService from "primevue/toastservice";

import ConfirmDialog from "primevue/confirmdialog";
import Tooltip from "primevue/tooltip";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Card from "primevue/card";
import Toast from "primevue/toast";

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
app.use(ToastService);
app.directive("tooltip", Tooltip);
app.component("ConfirmDialog", ConfirmDialog);
app.component("Chart", Chart);
app.component("Button", Button);
app.component("InputText", InputText);
app.component("Select", Select);
app.component("DataTable", DataTable);
app.component("Column", Column);
app.component("Card", Card);
app.component("Toast", Toast);

app.mount("#admin-app");
