<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useSession } from '../composables/useSession';

const { user } = useSession();
const dropdownOpen = ref(false);

const displayName = computed(() => user.value?.firstName || user.value?.email || 'User');
const displayEmail = computed(() => user.value?.email || '');
const avatarLetter = computed(() => {
    const name = user.value?.firstName?.trim();
    const email = user.value?.email;
    if (name) return name[0].toUpperCase();
    if (email) return email[0].toUpperCase();
    return 'U';
});

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
            </div>
            <a
                href="#"
                class="block px-4 py-2 text-sm text-site-body hover:bg-slate-800/80 hover:text-site-heading"
                role="menuitem"
                @click.prevent="close"
            >
                Profile
            </a>
            <a
                href="#"
                class="block px-4 py-2 text-sm text-site-body hover:bg-slate-800/80 hover:text-site-heading"
                role="menuitem"
                @click.prevent="close"
            >
                Settings
            </a>
            <div class="border-t border-slate-700">
                <a
                    href="#"
                    class="block px-4 py-2 text-sm text-site-body hover:bg-slate-800/80 hover:text-site-heading"
                    role="menuitem"
                    @click.prevent="close"
                >
                    Sign out
                </a>
            </div>
        </div>
    </div>
</template>
