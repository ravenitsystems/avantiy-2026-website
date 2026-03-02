<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { RouterLink, useRouter, useRoute } from 'vue-router';
import { useSession } from '../composables/useSession';
import axios from 'axios';

const router = useRouter();
const route = useRoute();
const { user, clearSession, switchTeam, clearTeamContext } = useSession();
const dropdownOpen = ref(false);
const switchingTeamId = ref(null);

const displayName = computed(() => {
    const first = user.value?.firstName?.trim();
    const last = user.value?.lastName?.trim();
    if (first || last) return [first, last].filter(Boolean).join(' ');
    return user.value?.email || 'User';
});
const displayEmail = computed(() => user.value?.email || '');
const accountTypeLabel = computed(() => {
    const type = user.value?.accountType;
    if (!type) return '';
    return type.charAt(0).toUpperCase() + type.slice(1);
});
const avatarLetter = computed(() => {
    const name = user.value?.firstName?.trim();
    const email = user.value?.email;
    if (name) return name[0].toUpperCase();
    if (email) return email[0].toUpperCase();
    return 'U';
});

const teamsList = computed(() => user.value?.teamsList ?? []);
const hasTeams = computed(() => teamsList.value.length > 0);
const activeTeamId = computed(() => user.value?.activeTeamId ?? null);

function toggle() {
    dropdownOpen.value = !dropdownOpen.value;
}

function close() {
    dropdownOpen.value = false;
}

function handleClickOutside(event) {
    if (!event.target.closest('[data-user-menu]') && !event.target.closest('[data-user-dropdown]')) {
        close();
    }
}

async function selectPersonal() {
    if (!activeTeamId.value) return;
    switchingTeamId.value = 0;
    await clearTeamContext();
    switchingTeamId.value = null;
    close();
    if (route.path.startsWith('/dashboard/teams')) {
        router.push({ name: 'dashboard-home' });
    }
}

async function selectTeam(teamId) {
    if (activeTeamId.value === teamId) return;
    switchingTeamId.value = teamId;
    await switchTeam(teamId);
    switchingTeamId.value = null;
    close();
    router.push({ name: 'dashboard-teams' });
}

async function signOut() {
    close();
    try {
        await axios.post('/api/session/logout');
    } catch {
        // continue to clear local state
    }
    clearSession();
    router.push({ name: 'dashboard-login' });
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="relative" data-user-menu>
        <button
            type="button"
            class="flex items-center gap-2 rounded-full p-1.5 text-site-body transition-colors hover:bg-slate-800/80 focus:outline-none focus:ring-2 focus:ring-cta/50"
            :aria-expanded="dropdownOpen"
            aria-haspopup="true"
            @click="toggle"
        >
            <span class="sr-only">Open user menu</span>
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-cta text-sm font-medium text-white">
                {{ avatarLetter }}
            </span>
        </button>

        <div
            v-show="dropdownOpen"
            data-user-dropdown
            class="absolute right-0 mt-2 w-56 origin-top-right rounded-lg border border-slate-700 bg-slate-900 py-1 shadow-xl focus:outline-none"
            role="menu"
        >
            <div class="border-b border-slate-700 px-4 py-3">
                <p class="text-sm font-medium text-site-heading">{{ displayName }}</p>
                <p v-if="displayEmail" class="truncate text-xs text-site-body">{{ displayEmail }}</p>
                <p v-if="accountTypeLabel" class="mt-1 text-xs text-slate-400">{{ accountTypeLabel }} account</p>
            </div>
            <RouterLink
                to="/dashboard/profile"
                class="block px-4 py-2 text-sm text-site-body hover:bg-slate-800/80 hover:text-site-heading"
                role="menuitem"
                @click="close"
            >
                User profile
            </RouterLink>
            <RouterLink
                to="/dashboard/billing"
                class="block px-4 py-2 text-sm text-site-body hover:bg-slate-800/80 hover:text-site-heading"
                role="menuitem"
                @click="close"
            >
                Billing
            </RouterLink>
            <RouterLink
                to="/dashboard/account"
                class="block px-4 py-2 text-sm text-site-body hover:bg-slate-800/80 hover:text-site-heading"
                role="menuitem"
                @click="close"
            >
                Account settings
            </RouterLink>
            <RouterLink
                v-if="user?.canCreateTeam"
                to="/dashboard/teams/create"
                class="block px-4 py-2 text-sm text-site-body hover:bg-slate-800/80 hover:text-site-heading"
                role="menuitem"
                @click="close"
            >
                Create team
            </RouterLink>
            <template v-if="hasTeams">
                <div class="border-t border-slate-700 px-4 pt-2 pb-1">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">Context</p>
                </div>
                <button
                    type="button"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm transition-colors"
                    :class="!activeTeamId ? 'text-cta font-medium' : 'text-site-body hover:bg-slate-800/80 hover:text-site-heading'"
                    role="menuitem"
                    :disabled="switchingTeamId !== null"
                    @click="selectPersonal"
                >
                    <svg v-if="!activeTeamId" class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span v-else class="h-3.5 w-3.5 shrink-0" />
                    Personal
                </button>
                <button
                    v-for="t in teamsList"
                    :key="t.id"
                    type="button"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm transition-colors"
                    :class="activeTeamId === t.id ? 'text-cta font-medium' : 'text-site-body hover:bg-slate-800/80 hover:text-site-heading'"
                    role="menuitem"
                    :disabled="switchingTeamId !== null"
                    @click="selectTeam(t.id)"
                >
                    <svg v-if="activeTeamId === t.id" class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span v-else class="h-3.5 w-3.5 shrink-0" />
                    {{ t.name }}
                </button>
            </template>
            <div class="border-t border-slate-700">
                <button
                    type="button"
                    class="block w-full px-4 py-2 text-left text-sm text-site-body hover:bg-slate-800/80 hover:text-site-heading"
                    role="menuitem"
                    @click="signOut"
                >
                    Sign out
                </button>
            </div>
        </div>
    </div>
</template>
