<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { useSession } from '../composables/useSession';

const route = useRoute();
const { userId } = useSession();

const headerLogoUrl = '/images/landing/header_logo.svg';

const productsOpen = ref(false);
const resourcesOpen = ref(false);
const mobileMenuOpen = ref(false);

const productsItems = [
    { path: '/', label: 'Website Builder' },
    { path: '/ecommerce', label: 'Ecommerce' },
    { path: '/ai-assistant', label: 'AI Assistant' },
    { path: '/seo', label: 'SEO' },
];

const resourcesItems = [
    { href: 'https://blog.avantiy.com', label: 'Blog', external: true, target: '_blank' },
    { href: 'https://blog.avantiy.com/product-updates', label: 'Product Updates', external: true, target: '_blank' },
    { href: 'https://blog.avantiy.com/how-to-guides', label: 'How-to Guides', external: true, target: '_blank' },
];

const topNavItems = [
    { path: '/templates', label: 'Templates' },
    { path: '/pricing', label: 'Pricing' },
];

// Public legal pages shown in the right side of the footer
const footerLegalLinks = [
    { path: '/terms', label: 'Terms Of Use' },
    { path: '/privacy', label: 'Privacy' },
    { path: '/saas-agreement', label: 'SAAS Agreement' },
];

const footerSocialLinks = [
    { href: 'https://www.instagram.com/buildwithavantiy/', label: 'Instagram', target: '_blank' },
    { href: 'https://www.facebook.com/buildwithavantiy', label: 'Facebook', target: '_blank' },
    { href: 'https://www.linkedin.com/company/buildwithavantiy', label: 'LinkedIn', target: '_blank' },
];

function isActivePath(path) {
    if (path === '/') return route.path === '/';
    return route.path === path || route.path.startsWith(path + '/');
}

function closeDropdowns() {
    productsOpen.value = false;
    resourcesOpen.value = false;
    mobileMenuOpen.value = false;
}

function toggleProducts() {
    productsOpen.value = !productsOpen.value;
    if (productsOpen.value) resourcesOpen.value = false;
}

function toggleResources() {
    resourcesOpen.value = !resourcesOpen.value;
    if (resourcesOpen.value) productsOpen.value = false;
}

onMounted(() => {
    document.addEventListener('click', closeDropdowns);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdowns);
});

const isLoggedIn = computed(() => userId.value !== 0);
</script>

<template>
    <div class="min-h-screen bg-black flex flex-col">
        <header class="sticky top-0 z-50 border-b border-slate-800/80 bg-black/95 backdrop-blur supports-[backdrop-filter]:bg-black/80">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <RouterLink
                    to="/"
                    class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-white/30 focus:ring-offset-2 focus:ring-offset-black rounded"
                >
                    <img
                        :src="headerLogoUrl"
                        alt="Avantiy"
                        class="h-8 w-auto max-w-[140px] object-contain object-left"
                    />
                </RouterLink>

                <nav class="hidden md:flex md:items-center md:gap-1" aria-label="Main">
                    <div class="relative">
                        <button
                            type="button"
                            class="rounded-lg px-3 py-2 text-sm font-medium transition-colors text-site-body hover:bg-white/10 hover:text-site-heading flex items-center gap-1"
                            :class="{ 'bg-white/10 text-site-heading': productsOpen }"
                            :aria-expanded="productsOpen"
                            aria-haspopup="true"
                            @click.stop="toggleProducts"
                        >
                            Products
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            v-show="productsOpen"
                            class="absolute left-0 top-full mt-1 w-48 rounded-lg border border-slate-800 bg-black py-1 shadow-xl"
                            @click.stop
                        >
                            <RouterLink
                                v-for="item in productsItems"
                                :key="item.path"
                                :to="item.path"
                                class="block px-4 py-2 text-sm transition-colors"
                                :class="isActivePath(item.path) ? 'bg-white/15 text-site-heading' : 'text-site-body hover:bg-white/10 hover:text-site-heading'"
                                @click="closeDropdowns"
                            >
                                {{ item.label }}
                            </RouterLink>
                        </div>
                    </div>

                    <RouterLink
                        v-for="item in topNavItems"
                        :key="item.path"
                        :to="item.path"
                        class="rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                        :class="isActivePath(item.path) ? 'text-site-heading bg-white/10' : 'text-site-body hover:bg-white/10 hover:text-site-heading'"
                    >
                        {{ item.label }}
                    </RouterLink>

                    <!-- Resources dropdown -->
                    <div class="relative">
                        <button
                            type="button"
                            class="rounded-lg px-3 py-2 text-sm font-medium transition-colors text-site-body hover:bg-white/10 hover:text-site-heading flex items-center gap-1"
                            :class="{ 'bg-white/10 text-site-heading': resourcesOpen }"
                            :aria-expanded="resourcesOpen"
                            aria-haspopup="true"
                            @click.stop="toggleResources"
                        >
                            Resources
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            v-show="resourcesOpen"
                            class="absolute left-0 top-full mt-1 w-48 rounded-lg border border-slate-800 bg-black py-1 shadow-xl"
                            @click.stop
                        >
                            <a
                                v-for="item in resourcesItems"
                                :key="item.href"
                                :href="item.href"
                                :target="item.target"
                                rel="noopener noreferrer"
                                class="block px-4 py-2 text-sm text-site-body hover:bg-white/10 hover:text-site-heading transition-colors"
                                @click="closeDropdowns"
                            >
                                {{ item.label }}
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Mobile menu button -->
                <button
                    type="button"
                    class="rounded-lg p-2 text-site-body hover:bg-white/10 md:hidden"
                    aria-label="Open menu"
                    aria-expanded="mobileMenuOpen"
                    @click.stop="mobileMenuOpen = !mobileMenuOpen"
                >
                    <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="flex items-center gap-2">
                    <RouterLink
                        v-if="isLoggedIn"
                        to="/dashboard"
                        class="rounded-lg border border-cta/30 bg-cta/10 px-3 py-2 text-sm font-medium text-cta transition-colors hover:bg-cta/20"
                    >
                        Dashboard
                    </RouterLink>
                    <template v-else>
                        <RouterLink
                            to="/dashboard/login"
                            class="rounded-lg px-3 py-2 text-sm font-medium text-site-body transition-colors hover:bg-white/10 hover:text-site-heading"
                        >
                            Login
                        </RouterLink>
                        <RouterLink
                            to="/dashboard/register"
                            class="rounded-lg bg-cta px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-cta-hover"
                        >
                            Create account
                        </RouterLink>
                    </template>
                </div>
            </div>

            <!-- Mobile menu panel -->
            <div v-show="mobileMenuOpen" class="border-t border-slate-800/80 bg-black px-4 py-4 md:hidden" @click.stop>
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-site-body">Products</p>
                    <RouterLink v-for="item in productsItems" :key="item.path" :to="item.path" class="block rounded-lg px-3 py-2 text-site-body hover:bg-white/10 hover:text-site-heading" @click="closeDropdowns">
                        {{ item.label }}
                    </RouterLink>
                    <RouterLink v-for="item in topNavItems" :key="item.path" :to="item.path" class="block rounded-lg px-3 py-2 text-site-body hover:bg-white/10 hover:text-site-heading" @click="closeDropdowns">
                        {{ item.label }}
                    </RouterLink>
                    <p class="pt-2 text-xs font-semibold uppercase tracking-wider text-site-body">Resources</p>
                    <a v-for="item in resourcesItems" :key="item.href" :href="item.href" :target="item.target" rel="noopener noreferrer" class="block rounded-lg px-3 py-2 text-site-body hover:bg-white/10 hover:text-site-heading" @click="closeDropdowns">
                        {{ item.label }}
                    </a>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <router-view />
        </main>

        <footer class="border-t border-slate-800/80 bg-black py-4">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Social icons (left) -->
                <div class="flex items-center gap-4">
                    <a
                        v-for="social in footerSocialLinks"
                        :key="social.href"
                        :href="social.href"
                        :target="social.target"
                        rel="noopener noreferrer"
                        :aria-label="social.label"
                        class="text-site-body hover:text-site-heading transition-colors"
                    >
                        <svg v-if="social.label === 'Instagram'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"></path>
                            <circle cx="17.5" cy="6.5" r="1.5"></circle>
                        </svg>
                        <svg v-else-if="social.label === 'Facebook'" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13.5 22v-7h2.5a1 1 0 00.96-.73l.75-3A1 1 0 0016.75 10H13.5V8c0-.83.67-1.5 1.5-1.5h2a1 1 0 001-1V3.75A.75.75 0 0017.25 3H15c-2.76 0-5 2.24-5 5v2H8a1 1 0 00-1 .88l-.5 3.5A1 1 0 007.5 15H10v7h3.5z" />
                        </svg>
                        <svg v-else-if="social.label === 'LinkedIn'" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6.94 6.5A1.94 1.94 0 115 4.56 1.95 1.95 0 016.94 6.5zM5.25 9h3.4v10H5.25zM10.75 9h3.26v1.47h.05A3.57 3.57 0 0117 8.86c3.03 0 3.59 2 3.59 4.59V19H17.2v-4.7c0-1.12 0-2.56-1.56-2.56s-1.8 1.22-1.8 2.48V19h-3.09z" />
                        </svg>
                    </a>
                </div>

                <!-- Legal links (right) -->
                <div class="flex items-center gap-4 text-xs sm:text-sm">
                    <RouterLink
                        v-for="link in footerLegalLinks"
                        :key="link.path"
                        :to="link.path"
                        class="text-site-body hover:text-site-heading transition-colors"
                    >
                        {{ link.label }}
                    </RouterLink>
                </div>
            </div>
        </footer>
    </div>
</template>
