import { createRouter, createWebHistory } from "vue-router";
import { useSession } from "./composables/useSession";

const PUBLIC_ROUTE_NAMES = ["dashboard-login", "dashboard-register", "dashboard-reset-password"];

const routes = [
    {
        path: "/dashboard",
        component: () => import("./layouts/DashboardWrapper.vue"),
        children: [
            {
                path: "login",
                name: "dashboard-login",
                component: () => import("./Pages/Login.vue"),
            },
            {
                path: "register",
                name: "dashboard-register",
                component: () => import("./Pages/Register.vue"),
            },
            {
                path: "reset-password",
                name: "dashboard-reset-password",
                component: () => import("./Pages/ResetPassword.vue"),
            },
            {
                path: "",
                component: () => import("./layouts/DashboardLayout.vue"),
                children: [
                    {
                        path: "",
                        name: "dashboard-home",
                        component: () => import("./Pages/Home.vue"),
                    },
                    {
                        path: "messages",
                        name: "dashboard-messages",
                        component: () => import("./Pages/Messages.vue"),
                    },
                    {
                        path: "messages/:id",
                        name: "dashboard-message",
                        component: () => import("./Pages/MessageView.vue"),
                    },
                    {
                        path: "websites",
                        name: "dashboard-websites",
                        component: () => import("./Pages/Websites.vue"),
                    },
                ],
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const isPublic = to.name && PUBLIC_ROUTE_NAMES.includes(to.name);
    const { fetchSession, userId } = useSession();

    try {
        await fetchSession();
        const id = userId.value;

        if (isPublic && id !== 0) {
            return { name: "dashboard-home" };
        }
        if (!isPublic && id === 0) {
            return { name: "dashboard-login" };
        }
    } catch {
        if (!isPublic) {
            return { name: "dashboard-login" };
        }
    }
});

export default router;
