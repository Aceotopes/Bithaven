import { createApp } from "vue";
import App from "./App.vue";
import "../../css/kiosk.css";

import { Vue3Lottie } from "vue3-lottie";

const app = createApp(App);

app.component("Vue3Lottie", Vue3Lottie);

app.mount("#app");
