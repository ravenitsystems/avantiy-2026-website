<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import axios from 'axios';
import { marked } from 'marked';
import { useToast } from '../composables/useToast';

const route = useRoute();
const toast = useToast();
const message = ref(null);
const loading = ref(true);
const error = ref('');

const messageId = computed(() => route.params.id);

const bodyHtml = computed(() => {
    const raw = message.value?.body ?? message.value?.content ?? '';
    if (!raw) return '';
    return marked.parse(String(raw), { async: false });
});

async function fetchMessage() {
    if (!messageId.value) return;
    loading.value = true;
    error.value = '';
    try {
        const { data } = await axios.get(`/api/message/get/${messageId.value}`);
        if (data.status === 'PASS' && data.data) {
            message.value = data.data;
        } else {
            const msg = data.data?.message ?? 'Message not found.';
            error.value = msg;
            toast.error(msg);
            message.value = null;
        }
    } catch (e) {
        const msg = e.response?.status === 404 ? 'Message not found.' : 'Failed to load message.';
        error.value = msg;
        toast.error(msg);
        message.value = null;
    } finally {
        loading.value = false;
    }
}

onMounted(fetchMessage);
watch(messageId, fetchMessage);
</script>

<template>
    <div class="max-w-3xl rounded-lg border border-slate-700 bg-slate-900/50 p-4 md:p-6">
        <RouterLink
            :to="{ name: 'dashboard-messages' }"
            class="mb-4 inline-flex items-center text-sm font-medium text-site-body hover:text-cta"
        >
            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to messages
        </RouterLink>

        <div v-if="loading" class="py-8 text-center text-site-body">Loading message…</div>
        <div v-else-if="error" class="py-8 text-center text-red-400">{{ error }}</div>
        <template v-else-if="message">
            <header class="border-b border-slate-700 pb-4">
                <h1 class="text-xl font-semibold text-site-heading">{{ message.subject }}</h1>
                <p class="mt-1 text-sm text-site-body">
                    {{ message.sent_at ? new Date(message.sent_at).toLocaleString() : '—' }}
                </p>
            </header>
            <div class="mt-4 prose prose-invert max-w-none text-site-body [&_a]:text-cta [&_a:hover]:text-cta-hover" v-html="bodyHtml"></div>
            <div v-if="!bodyHtml" class="mt-4 text-site-body">No content.</div>
        </template>
    </div>
</template>
