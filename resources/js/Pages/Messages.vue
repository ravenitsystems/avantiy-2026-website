<script setup>
import { ref, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';
import { useToast } from '../composables/useToast';

const toast = useToast();
const messages = ref([]);
const loading = ref(true);

function formatDate(isoString) {
    if (!isoString) return '—';
    const d = new Date(isoString);
    return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
}

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/message/index');
        if (data.status === 'PASS' && data.data?.messages) {
            messages.value = data.data.messages;
        }
    } catch {
        toast.error('Failed to load messages.');
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-xl font-semibold text-site-heading">Messages</h1>
            <p class="mt-1 text-sm text-site-body">View and manage your system messages.</p>
        </div>

        <div v-if="loading" class="rounded-xl border border-slate-700 bg-slate-900/50 p-8 text-center text-site-body">
            Loading…
        </div>

        <div v-else-if="!messages.length" class="rounded-xl border border-slate-700 bg-slate-900/50 p-8 text-center text-site-body">
            No messages yet.
        </div>

        <ul v-else class="space-y-2">
            <li
                v-for="msg in messages"
                :key="msg.id"
                class="rounded-lg border border-slate-700 bg-slate-900/50 transition-colors hover:border-slate-600 hover:bg-slate-800/50"
            >
                <RouterLink
                    :to="{ name: 'dashboard-message', params: { id: msg.id } }"
                    class="block px-4 py-3"
                >
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-medium text-site-heading" :class="{ 'font-semibold': !msg.read }">
                            {{ msg.subject || 'No subject' }}
                        </p>
                        <span
                            v-if="!msg.read"
                            class="rounded bg-cta/20 px-2 py-0.5 text-xs text-cta"
                        >
                            New
                        </span>
                    </div>
                    <p class="mt-0.5 text-sm text-site-body">{{ formatDate(msg.sent_at) }}</p>
                </RouterLink>
            </li>
        </ul>
    </div>
</template>
