<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import MessagesMenu from '../components/MessagesMenu.vue';
import UserMenu from '../components/UserMenu.vue';
import { useSession } from '../composables/useSession';

const route = useRoute();
const router = useRouter();
const { fetchSession, userId, isAdmin, isTemplateAdmin, isActivityLogAdmin, user } = useSession();
const sidebarOpen = ref(false);

const SESSION_CHECK_INTERVAL_MS = 60_000; // 60 seconds
let sessionCheckTimer = null;

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
}

function closeSidebar() {
    sidebarOpen.value = false;
}

async function checkSession() {
    await fetchSession();
    if (userId.value === 0) {
        if (sessionCheckTimer) {
            clearInterval(sessionCheckTimer);
            sessionCheckTimer = null;
        }
        router.push({ name: 'dashboard-login' });
    }
}

onMounted(() => {
    sessionCheckTimer = setInterval(checkSession, SESSION_CHECK_INTERVAL_MS);
});

onUnmounted(() => {
    if (sessionCheckTimer) {
        clearInterval(sessionCheckTimer);
        sessionCheckTimer = null;
    }
});

const headerLogoUrl = '/images/header-logo.png';

const administrationOpen = ref(false);
const templatesOpen = ref(false);

const mainNavItems = [
    { path: '/dashboard', label: 'Home', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { path: '/dashboard/messages', label: 'Messages', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    { path: '/dashboard/websites', label: 'Websites', icon: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9a9 9 0 009 9m-9-9a9 9 0 009-9' },
    { path: '/dashboard/templates', label: 'Templates', icon: 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z' },
];

const clientsNavItem = {
    path: '/dashboard/clients',
    label: 'Clients',
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
};

const teamsNavIcon = 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 0112 0v1h-6zm-4-6a4 4 0 11-8 0 4 4 0 018 0z';

const administrationNavItem = computed(() => {
    const children = [
        { path: '/dashboard/administration/invoices', label: 'Invoice', name: 'dashboard-administration-invoices' },
        { path: '/dashboard/administration/activity-audit', label: 'Activity Audit', name: 'dashboard-administration-activity-audit' },
        ...(isActivityLogAdmin.value
            ? [{ path: '/dashboard/administration/activity-log', label: 'ACTIVITY LOG', name: 'dashboard-administration-activity-log' }]
            : []),
        { path: '/dashboard/administration/registered-users', label: 'Registered Users', name: 'dashboard-administration-registered-users' },
    ];
    if (isTemplateAdmin.value) {
        children.push({
            label: 'Templates',
            children: [
                { path: '/dashboard/administration/templates/categories', label: 'Categories', name: 'dashboard-administration-templates-categories' },
                { path: '/dashboard/administration/templates/templates', label: 'Templates', name: 'dashboard-administration-templates-templates' },
            ],
        });
    }
    return {
        label: 'Administration',
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        children,
    };
});

const navItems = computed(() => {
    const items = [...mainNavItems];
    if (user.value?.canAddClientAssociation) {
        items.push(clientsNavItem);
    }
    if (user.value?.activeTeamId) {
        items.push({
            path: '/dashboard/teams',
            label: 'Team Management',
            icon: teamsNavIcon,
        });
    }
    if (isAdmin.value) {
        items.push(administrationNavItem.value);
    }
    return items;
});

function isNavItemWithChildren(item) {
    return item.children && item.children.length > 0;
}

function isActiveNav(item) {
    if (item.path) return route.path === item.path;
    if (item.children) {
        return item.children.some((c) => {
            if (c.path) return route.path === c.path;
            if (c.children) return c.children.some((s) => route.path === s.path);
            return false;
        });
    }
    return false;
}

function toggleAdministration() {
    administrationOpen.value = !administrationOpen.value;
}

// Keep Administration open when viewing an admin child route
const isAdministrationRoute = computed(() =>
    route.path.startsWith('/dashboard/administration/')
);
watch(isAdministrationRoute, (on) => {
    if (on) administrationOpen.value = true;
}, { immediate: true });

// Keep Templates open when viewing a templates child route
const isTemplatesRoute = computed(() =>
    route.path.startsWith('/dashboard/administration/templates/')
);
watch(isTemplatesRoute, (on) => {
    if (on) templatesOpen.value = true;
}, { immediate: true });

function toggleTemplates() {
    templatesOpen.value = !templatesOpen.value;
}
</script>

<template>
    <div class="min-h-screen bg-black">
        <!-- Mobile overlay -->
        <div
            v-show="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/70 transition-opacity md:hidden"
            aria-hidden="true"
            @click="closeSidebar"
        />

        <!-- Sidebar: full viewport height, dark purple -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex h-screen w-64 flex-col border-r border-slate-800/80 shadow-xl transition-transform duration-200 ease-out md:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
            style="background-color: var(--color-sidebar);"
        >
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-800/80 px-3">
                <RouterLink to="/dashboard" class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-white/30 focus:ring-offset-2 focus:ring-offset-[var(--color-sidebar)] rounded">
                    <img
                        :src="headerLogoUrl"
                        alt="Avantiy"
                        class="h-8 w-auto max-w-[140px] object-contain object-left"
                    />
                </RouterLink>
                <button
                    type="button"
                    class="rounded-md p-2 text-site-body hover:bg-white/10 hover:text-site-heading md:hidden"
                    aria-label="Close menu"
                    @click="closeSidebar"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav class="mt-4 flex-1 space-y-1 overflow-y-auto px-3">
                <template v-for="(item, index) in navItems" :key="item.path || item.label + String(index)">
                    <RouterLink
                        v-if="!isNavItemWithChildren(item)"
                        :to="item.path"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                        :class="isActiveNav(item)
                            ? 'bg-white/15 text-site-heading'
                            : 'text-site-body hover:bg-white/10 hover:text-site-heading'"
                        @click="closeSidebar"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                        </svg>
                        {{ item.label }}
                    </RouterLink>
                    <div v-else class="space-y-0.5">
                        <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors text-left text-site-body hover:bg-white/10 hover:text-site-heading"
                            :aria-expanded="administrationOpen"
                            @click="toggleAdministration"
                        >
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                            </svg>
                            {{ item.label }}
                        </button>
                        <div v-show="administrationOpen" class="space-y-0.5">
                            <template v-for="child in item.children" :key="child.path || child.label">
                                <RouterLink
                                    v-if="child.path"
                                    :to="child.path"
                                    class="ml-8 flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors"
                                    :class="route.path === child.path
                                        ? 'bg-white/15 font-medium text-site-heading'
                                        : 'text-site-body hover:bg-white/10 hover:text-site-heading'"
                                    @click="closeSidebar"
                                >
                                    {{ child.label }}
                                </RouterLink>
                                <div v-else-if="child.children?.length" class="space-y-0.5">
                                    <button
                                        type="button"
                                        class="ml-8 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium uppercase tracking-wide transition-colors text-left text-slate-500 hover:bg-white/10 hover:text-site-heading"
                                        :aria-expanded="templatesOpen"
                                        @click="toggleTemplates"
                                    >
                                        {{ child.label }}
                                    </button>
                                    <div v-show="templatesOpen" class="space-y-0.5">
                                        <RouterLink
                                            v-for="sub in child.children"
                                            :key="sub.path"
                                            :to="sub.path"
                                            class="ml-12 flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                                            :class="route.path === sub.path
                                                ? 'bg-white/15 font-medium text-site-heading'
                                                : 'text-site-body hover:bg-white/10 hover:text-site-heading'"
                                            @click="closeSidebar"
                                        >
                                            {{ sub.label }}
                                        </RouterLink>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </nav>
        </aside>

        <!-- Main content wrapper -->
        <div class="md:pl-64">
            <!-- Header -->
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-800 bg-black px-4">
                <button
                    type="button"
                    class="rounded-md p-2 text-site-body hover:bg-slate-800/80 md:hidden"
                    aria-label="Open menu"
                    @click="toggleSidebar"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="flex-1 md:flex-none" />

                <div class="flex items-center gap-1">
                    <RouterLink
                        v-if="user?.activeTeamId"
                        :to="{ name: 'dashboard-teams' }"
                        class="mr-2 flex items-center gap-1.5 rounded-lg border border-cta/30 bg-cta/10 px-3 py-1.5 text-xs font-medium text-cta transition-colors hover:bg-cta/20"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 0112 0v1h-6zm-4-6a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        {{ user.activeTeamName }}
                    </RouterLink>
                    <span
                        v-else-if="user?.teamsList?.length"
                        class="mr-2 flex items-center gap-1.5 rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-medium text-site-body"
                    >
                        Personal
                    </span>
                    <MessagesMenu />
                    <UserMenu />
                </div>
            </header>

            <!-- Page content -->
            <main class="p-4 md:p-6">
                <router-view />
            </main>
        </div>
    </div>
</template>
