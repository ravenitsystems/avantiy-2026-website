import './bootstrap';
import axios from 'axios';
import { createApp } from "vue";
import router from "./router";
import App from "./App.vue";
import { useSession } from "./composables/useSession";

const PUBLIC_ROUTE_NAMES = [
    "public-home",
    "public-ecommerce",
    "public-ai-assistant",
    "public-seo",
    "public-templates",
    "public-pricing",
    "public-terms",
    "public-privacy",
    "public-saas-agreement",
    "dashboard-login",
    "dashboard-register",
    "dashboard-reset-password",
];

// On 401, clear session and redirect to login (unless already on a public page)
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            const name = router.currentRoute.value?.name;
            if (name && !PUBLIC_ROUTE_NAMES.includes(name)) {
                useSession().clearSession();
                router.push({ name: "dashboard-login" });
            }
        }
        return Promise.reject(error);
    }
);

createApp(App).use(router).mount("#app");
