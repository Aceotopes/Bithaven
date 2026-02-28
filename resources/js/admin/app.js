import { createApp } from "vue";
import { createPinia } from "pinia";
import { useAuthStore } from "./stores/auth";
import App from "./App.vue";
import router from "./router";
import axios from "axios";

import PrimeVue from "primevue/config";
import Aura from "@primeuix/themes/aura"; // use Aura instead of Lara
import ConfirmationService from "primevue/confirmationservice";
import ToastService from "primevue/toastservice";

import ConfirmDialog from "primevue/confirmdialog";
import Dialog from "primevue/dialog";
import Tooltip from "primevue/tooltip";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Card from "primevue/card";
import Toast from "primevue/toast";
import Menu from "primevue/menu";
import Avatar from "primevue/avatar";
import Tag from "primevue/tag";
import Password from "primevue/password";

import Chart from "primevue/chart";
import "chart.js/auto";

import "primeicons/primeicons.css";
import "../../css/app.css";

axios.defaults.baseURL = "/api";

const app = createApp(App);

const pinia = createPinia();
app.use(pinia);

const auth = useAuthStore(pinia);
auth.initialize();

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
app.component("Menu", Menu);
app.component("Avatar", Avatar);
app.component("Dialog", Dialog);
app.component("Tag", Tag);
app.component("Password", Password);

app.mount("#admin-app");
