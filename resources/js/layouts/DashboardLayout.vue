<script setup>
import { ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import MessagesMenu from '../components/MessagesMenu.vue';
import UserMenu from '../components/UserMenu.vue';

const route = useRoute();
const sidebarOpen = ref(false);

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value;
}

function closeSidebar() {
    sidebarOpen.value = false;
}

const navItems = [
    { path: '/dashboard', label: 'Home', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { path: '/dashboard/messages', label: 'Messages', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    { path: '/dashboard/websites', label: 'Websites', icon: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9a9 9 0 009 9m-9-9a9 9 0 009-9' },
];
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
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-800/80 px-4 md:justify-center">
                <span class="text-lg font-semibold text-site-heading">Navigation</span>
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
                <RouterLink
                    v-for="item in navItems"
                    :key="item.path"
                    :to="item.path"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                    :class="route.path === item.path
                        ? 'bg-white/15 text-site-heading'
                        : 'text-site-body hover:bg-white/10 hover:text-site-heading'"
                    @click="closeSidebar"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </RouterLink>
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
