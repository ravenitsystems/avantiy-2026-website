import { createRouter, createWebHistory } from "vue-router";
import { useSession } from "./composables/useSession";

const locale = typeof window !== 'undefined' ? (window.__LOCALE || 'en') : 'en';
const base = `/${locale}`;

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

/** Routes that redirect to dashboard when the user is already logged in */
const GUEST_ONLY_ROUTE_NAMES = ["dashboard-login", "dashboard-register", "dashboard-reset-password"];

const routes = [
    {
        path: "/",
        component: () => import("./layouts/PublicLayout.vue"),
        children: [
            {
                path: "",
                name: "public-home",
                component: () => import("./Pages/PublicHome.vue"),
            },
            {
                path: "ecommerce",
                name: "public-ecommerce",
                component: () => import("./Pages/PublicEcommerce.vue"),
            },
            {
                path: "ai-assistant",
                name: "public-ai-assistant",
                component: () => import("./Pages/PublicAiAssistant.vue"),
            },
            {
                path: "seo",
                name: "public-seo",
                component: () => import("./Pages/PublicSeo.vue"),
            },
            {
                path: "templates",
                name: "public-templates",
                component: () => import("./Pages/PublicTemplates.vue"),
            },
            {
                path: "pricing",
                name: "public-pricing",
                component: () => import("./Pages/PublicPricing.vue"),
            },
            {
                path: "terms",
                name: "public-terms",
                component: () => import("./Pages/PublicTerms.vue"),
            },
            {
                path: "privacy",
                name: "public-privacy",
                component: () => import("./Pages/PublicPrivacy.vue"),
            },
            {
                path: "saas-agreement",
                name: "public-saas-agreement",
                component: () => import("./Pages/PublicSaasAgreement.vue"),
            },
        ],
    },
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
                        path: "administration/duda-api-logs",
                        name: "dashboard-administration-duda-api-logs",
                        component: () => import("./Pages/administration/DudaApiLogs.vue"),
                    },
                    {
                        path: "administration/epicurus/customers",
                        name: "dashboard-administration-epicurus-customers",
                        component: () => import("./Pages/administration/EpicurusCustomers.vue"),
                    },
                    {
                        path: "administration/epicurus/activity-logs",
                        name: "dashboard-administration-epicurus-activity-logs",
                        component: () => import("./Pages/administration/EpicurusActivityLogs.vue"),
                    },
                    {
                        path: "administration/epicurus/manifest",
                        name: "dashboard-administration-epicurus-manifest",
                        component: () => import("./Pages/administration/EpicurusManifest.vue"),
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
                    {
                        path: "administration/currencies",
                        name: "dashboard-administration-currencies",
                        component: () => import("./Pages/administration/Currencies.vue"),
                    },
                    {
                        path: "administration/countries",
                        name: "dashboard-administration-countries",
                        component: () => import("./Pages/administration/Countries.vue"),
                    },
                ],
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(base),
    routes,
});

const ADMINISTRATION_ROUTE_NAMES = [
    "dashboard-administration-invoices",
    "dashboard-administration-activity-log",
    "dashboard-administration-registered-users",
];

const CUSTOMER_ADMIN_ROUTE_NAMES = [
    "dashboard-administration-registered-users",
    "dashboard-administration-invoices",
    "dashboard-administration-activity-log",
];

const SITE_ADMIN_ROUTE_NAMES = [
    "dashboard-administration-templates-categories",
    "dashboard-administration-templates-templates",
    "dashboard-administration-currencies",
    "dashboard-administration-countries",
];

const DUDA_API_LOGS_ROUTE_NAMES = ["dashboard-administration-duda-api-logs"];

const EPICURUS_CUSTOMERS_ROUTE_NAMES = ["dashboard-administration-epicurus-customers"];
const EPICURUS_ACTIVITY_LOGS_ROUTE_NAMES = ["dashboard-administration-epicurus-activity-logs"];
const EPICURUS_MANIFEST_ROUTE_NAMES = ["dashboard-administration-epicurus-manifest"];

const CLIENT_ACCESS_ROUTE_NAMES = ["dashboard-clients"];
const TEAMS_CREATE_ROUTE_NAMES = ["dashboard-teams-create"];
const TEAMS_ROUTE_NAMES = ["dashboard-teams"];

router.beforeEach(async (to) => {
    const isPublic = to.name && PUBLIC_ROUTE_NAMES.includes(to.name);
    const isAdminRoute = to.name && ADMINISTRATION_ROUTE_NAMES.includes(to.name);
    const isCustomerAdminRoute = to.name && CUSTOMER_ADMIN_ROUTE_NAMES.includes(to.name);
    const isSiteAdminRoute = to.name && SITE_ADMIN_ROUTE_NAMES.includes(to.name);
    const isDudaApiLogsRoute = to.name && DUDA_API_LOGS_ROUTE_NAMES.includes(to.name);
    const isEpicurusCustomersRoute = to.name && EPICURUS_CUSTOMERS_ROUTE_NAMES.includes(to.name);
    const isEpicurusActivityLogsRoute = to.name && EPICURUS_ACTIVITY_LOGS_ROUTE_NAMES.includes(to.name);
    const isEpicurusManifestRoute = to.name && EPICURUS_MANIFEST_ROUTE_NAMES.includes(to.name);
    const isClientRoute = to.name && CLIENT_ACCESS_ROUTE_NAMES.includes(to.name);
    const isTeamsCreateRoute = to.name && TEAMS_CREATE_ROUTE_NAMES.includes(to.name);
    const isTeamsRoute = to.name && TEAMS_ROUTE_NAMES.includes(to.name);
    const {
        fetchSession,
        userId,
        isAdmin,
        hasCustomerAdmin,
        hasSiteAdmin,
        hasDudaApiLogs,
        hasEpicurusCustomers,
        hasEpicurusActivityLogs,
        hasEpicurusManifest,
        user,
    } = useSession();

    try {
        await fetchSession();
        const id = userId.value;

        if (isPublic && id !== 0) {
            const isGuestOnly = to.name && GUEST_ONLY_ROUTE_NAMES.includes(to.name);
            if (isGuestOnly) {
                return { name: "dashboard-home" };
            }
        }
        if (!isPublic && id === 0) {
            return { name: "dashboard-login" };
        }
        if (isAdminRoute && !isAdmin.value) {
            return { name: "dashboard-home" };
        }
        if (isCustomerAdminRoute && !hasCustomerAdmin.value) {
            return { name: "dashboard-home" };
        }
        if (isSiteAdminRoute && !hasSiteAdmin.value) {
            return { name: "dashboard-home" };
        }
        if (isDudaApiLogsRoute && !hasDudaApiLogs.value) {
            return { name: "dashboard-home" };
        }
        if (isEpicurusCustomersRoute && !hasEpicurusCustomers.value) {
            return { name: "dashboard-home" };
        }
        if (isEpicurusActivityLogsRoute && !hasEpicurusActivityLogs.value) {
            return { name: "dashboard-home" };
        }
        if (isEpicurusManifestRoute && !hasEpicurusManifest.value) {
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
