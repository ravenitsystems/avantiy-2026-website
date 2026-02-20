import './bootstrap';
import { createApp } from "vue";
import router from "./router";
import App from "./App.vue";

console.log("APP START");

createApp(App).use(router).mount("#app");
