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
                    {
                        path: "templates",
                        name: "dashboard-templates",
                        component: () => import("./Pages/Templates.vue"),
                    },
                    {
                        path: "profile",
                        name: "dashboard-profile",
                        component: () => import("./Pages/Profile.vue"),
                    },
                    {
                        path: "billing",
                        name: "dashboard-billing",
                        component: () => import("./Pages/Billing.vue"),
                    },
                    {
                        path: "payment-methods",
                        name: "dashboard-payment-methods",
                        redirect: { name: "dashboard-billing", query: { tab: "payment-methods" } },
                    },
                    {
                        path: "account",
                        name: "dashboard-account",
                        component: () => import("./Pages/Account.vue"),
                    },
                    {
                        path: "clients",
                        name: "dashboard-clients",
                        component: () => import("./Pages/Clients.vue"),
                    },
                    {
                        path: "teams",
                        name: "dashboard-teams",
                        component: () => import("./Pages/TeamView.vue"),
                    },
                    {
                        path: "teams/create",
                        name: "dashboard-teams-create",
                        component: () => import("./Pages/CreateTeam.vue"),
                    },
                    {
                        path: "administration/invoices",
                        name: "dashboard-administration-invoices",
                        component: () => import("./Pages/administration/Invoices.vue"),
                    },
                    {
                        path: "administration/activity-audit",
                        name: "dashboard-administration-activity-audit",
                        component: () => import("./Pages/administration/ActivityAudit.vue"),
                    },
                    {
                        path: "administration/activity-log",
                        name: "dashboard-administration-activity-log",
                        component: () => import("./Pages/administration/ActivityLog.vue"),
                    },
                    {
                        path: "administration/registered-users",
                        name: "dashboard-administration-registered-users",
                        component: () => import("./Pages/administration/RegisteredUsers.vue"),
                    },
                    {
                        path: "administration/templates",
                        redirect: { name: "dashboard-administration-templates-templates" },
                    },
                    {
                        path: "administration/templates/categories",
                        name: "dashboard-administration-templates-categories",
                        component: () => import("./Pages/administration/TemplateCategories.vue"),
                    },
                    {
                        path: "administration/templates/templates",
                        name: "dashboard-administration-templates-templates",
                        component: () => import("./Pages/administration/Templates.vue"),
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

const ADMINISTRATION_ROUTE_NAMES = [
    "dashboard-administration-invoices",
    "dashboard-administration-activity-audit",
    "dashboard-administration-registered-users",
];

const TEMPLATE_ADMIN_ROUTE_NAMES = [
    "dashboard-administration-templates-categories",
    "dashboard-administration-templates-templates",
];

const ACTIVITY_LOG_ROUTE_NAMES = ["dashboard-administration-activity-log"];

const CLIENT_ACCESS_ROUTE_NAMES = ["dashboard-clients"];
const TEAMS_CREATE_ROUTE_NAMES = ["dashboard-teams-create"];
const TEAMS_ROUTE_NAMES = ["dashboard-teams"];

router.beforeEach(async (to) => {
    const isPublic = to.name && PUBLIC_ROUTE_NAMES.includes(to.name);
    const isAdminRoute = to.name && ADMINISTRATION_ROUTE_NAMES.includes(to.name);
    const isTemplateAdminRoute = to.name && TEMPLATE_ADMIN_ROUTE_NAMES.includes(to.name);
    const isActivityLogRoute = to.name && ACTIVITY_LOG_ROUTE_NAMES.includes(to.name);
    const isClientRoute = to.name && CLIENT_ACCESS_ROUTE_NAMES.includes(to.name);
    const isTeamsCreateRoute = to.name && TEAMS_CREATE_ROUTE_NAMES.includes(to.name);
    const isTeamsRoute = to.name && TEAMS_ROUTE_NAMES.includes(to.name);
    const { fetchSession, userId, isAdmin, isTemplateAdmin, isActivityLogAdmin, user } = useSession();

    try {
        await fetchSession();
        const id = userId.value;

        if (isPublic && id !== 0) {
            return { name: "dashboard-home" };
        }
        if (!isPublic && id === 0) {
            return { name: "dashboard-login" };
        }
        if (isAdminRoute && !isAdmin.value) {
            return { name: "dashboard-home" };
        }
        if (isTemplateAdminRoute && !isTemplateAdmin.value) {
            return { name: "dashboard-home" };
        }
        if (isActivityLogRoute && !isActivityLogAdmin.value) {
            return { name: "dashboard-home" };
        }
        if (isClientRoute && !user.value?.canAddClientAssociation) {
            return { name: "dashboard-home" };
        }
        if (isTeamsCreateRoute && !user.value?.canCreateTeam) {
            return { name: "dashboard-home" };
        }
        if (isTeamsRoute && !user.value?.activeTeamId && !to.query.accept_token) {
            return { name: "dashboard-home" };
        }
    } catch {
        if (!isPublic) {
            return { name: "dashboard-login" };
        }
    }
});

export default router;
