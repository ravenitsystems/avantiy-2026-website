<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';

const dropdownOpen = ref(false);
const messages = ref([]);
const messagesLoading = ref(false);

const exampleMessages = [
    { id: 1, subject: 'Welcome to the dashboard', sent_at: new Date(Date.now() - 1000 * 60 * 15).toISOString(), read: false },
    { id: 2, subject: 'Your website backup is complete', sent_at: new Date(Date.now() - 1000 * 60 * 60 * 2).toISOString(), read: false },
    { id: 3, subject: 'Scheduled maintenance on Sunday', sent_at: new Date(Date.now() - 1000 * 60 * 60 * 24).toISOString(), read: false },
];

const unreadCount = computed(() => messages.value.filter((m) => !m.read).length);
const hasUnread = computed(() => unreadCount.value > 0);
const unreadMessages = computed(() => messages.value.filter((m) => !m.read));

function formatMessageDate(isoString) {
    if (!isoString) return '—';
    const d = new Date(isoString);
    const now = new Date();
    const sameDay = d.toDateString() === now.toDateString();
    if (sameDay) return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    return d.toLocaleDateString(undefined, { dateStyle: 'short' });
}

function toggle() {
    dropdownOpen.value = !dropdownOpen.value;
}

function close() {
    dropdownOpen.value = false;
}

function handleClickOutside(event) {
    if (!event.target.closest('[data-messages-menu]') && !event.target.closest('[data-messages-dropdown]')) {
        close();
    }
}

async function fetchMessages() {
    messagesLoading.value = true;
    try {
        const { data } = await axios.get('/api/messages');
        const payload = data?.data ?? data;
        const list = Array.isArray(payload) ? payload : (payload?.messages ?? []);
        messages.value = list.length > 0 ? list : exampleMessages;
    } catch {
        messages.value = exampleMessages;
    } finally {
        messagesLoading.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    fetchMessages();
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="relative" data-messages-menu>
        <button
            type="button"
            class="relative flex items-center gap-2 rounded-full p-1.5 text-site-body transition-colors hover:bg-slate-800/80 focus:outline-none focus:ring-2 focus:ring-cta/50"
            :aria-expanded="dropdownOpen"
            aria-haspopup="true"
            aria-label="System messages"
            @click="toggle"
        >
            <span class="relative flex h-9 w-9 items-center justify-center rounded-full bg-cta text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span
                    v-if="hasUnread"
                    class="absolute -right-0.5 -top-0.5 flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 py-0.5 text-[0.65rem] font-bold leading-none text-white ring-2 ring-black"
                    :aria-label="`${unreadCount} unread messages`"
                >
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
            </span>
        </button>

        <div
            v-show="dropdownOpen"
            data-messages-dropdown
            class="fixed left-4 right-4 top-[4.5rem] z-50 max-h-[calc(100vh-6rem)] sm:absolute sm:left-auto sm:right-0 sm:top-full sm:mt-2 sm:w-80 sm:max-h-80 origin-top-right rounded-lg border border-slate-700 bg-slate-900 shadow-xl focus:outline-none"
            role="menu"
        >
            <div class="border-b border-slate-700 px-4 py-3">
                <h3 class="text-sm font-semibold text-site-heading">System messages</h3>
                <p v-if="!messagesLoading" class="mt-0.5 text-xs text-site-body">
                    {{ unreadCount }} unread
                </p>
            </div>
            <div class="max-h-[calc(100vh-10rem)] overflow-y-auto py-1 sm:max-h-80">
                <div v-if="messagesLoading" class="px-4 py-6 text-center text-sm text-site-body">
                    Loading…
                </div>
                <template v-else-if="unreadMessages.length === 0">
                    <div class="px-4 py-6 text-center text-sm text-site-body">No unread messages</div>
                </template>
                <RouterLink
                    v-for="msg in unreadMessages"
                    :key="msg.id"
                    :to="`/dashboard/messages/${msg.id}`"
                    class="block border-b border-slate-700/80 px-4 py-3 text-left transition-colors last:border-b-0 hover:bg-slate-800/80"
                    @click="close"
                >
                    <p class="text-sm font-medium text-site-heading line-clamp-1">{{ msg.subject || 'No subject' }}</p>
                    <p class="mt-0.5 text-xs text-site-body">{{ formatMessageDate(msg.sent_at) }}</p>
                </RouterLink>
            </div>
        </div>
    </div>
</template>
