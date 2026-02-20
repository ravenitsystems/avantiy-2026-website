<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const message = ref(null);
const loading = ref(true);
const error = ref(null);

const messageId = computed(() => route.params.id);

onMounted(async () => {
    try {
        const { data } = await axios.get(`/api/messages/${messageId.value}`);
        message.value = data;
    } catch (e) {
        error.value = e.response?.status === 404 ? 'Message not found.' : 'Failed to load message.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="rounded-lg border border-slate-700 bg-slate-900/50 p-4 md:p-6">
        <RouterLink
            to="/dashboard"
            class="mb-4 inline-flex items-center text-sm font-medium text-site-body hover:text-cta"
        >
            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to dashboard
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
            <div class="mt-4 prose prose-invert max-w-none text-site-body [&_a]:text-cta [&_a:hover]:text-cta-hover" v-html="message.body || message.content || ''"></div>
            <div v-if="!message.body && !message.content" class="mt-4 text-site-body">No content.</div>
        </template>
    </div>
</template>
