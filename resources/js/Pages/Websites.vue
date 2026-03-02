<script setup>
import { ref, watch, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';

const ownedSites = ref([]);
const errorModalOpen = ref(false);
const errorModalMessage = ref('');
const editLoadingId = ref(null);
const meta = ref({ total: 0, per_page: 12, current_page: 1, last_page: 1 });
const loading = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);
const perPage = ref(12);

async function fetchSites() {
    loading.value = true;
    try {
        const params = { page: currentPage.value, per_page: perPage.value };
        if (searchQuery.value) params.search = searchQuery.value;
        const { data } = await axios.get('/api/website/index', { params });
        const payload = data?.data ?? data;
        ownedSites.value = payload?.websites ?? [];
        meta.value = payload?.meta ?? { total: 0, per_page: 12, current_page: 1, last_page: 1 };
    } catch {
        ownedSites.value = [];
    } finally {
        loading.value = false;
    }
}

function applySearch() {
    currentPage.value = 1;
    fetchSites();
}

function goToPage(p) {
    if (p >= 1 && p <= meta.value.last_page) {
        currentPage.value = p;
        fetchSites();
    }
}

async function editWebsite(site) {
    editLoadingId.value = site.id;
    errorModalMessage.value = '';
    errorModalOpen.value = false;
    try {
        const { data } = await axios.get(`/api/website/editurl/${site.id}`);
        const payload = data?.data ?? data;
        if (payload?.redirect_url) {
            window.location.href = payload.redirect_url;
            return;
        }
        if (payload?.message) {
            errorModalMessage.value = payload.message;
            errorModalOpen.value = true;
        } else {
            errorModalMessage.value = 'An unexpected error occurred.';
            errorModalOpen.value = true;
        }
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Failed to open editor.';
        errorModalMessage.value = msg;
        errorModalOpen.value = true;
    } finally {
        editLoadingId.value = null;
    }
}

function closeErrorModal() {
    errorModalOpen.value = false;
}

function formatDate(isoString) {
    if (!isoString) return '—';
    return new Date(isoString).toLocaleDateString(undefined, { dateStyle: 'short' });
}

watch([currentPage, perPage], () => {
    if (loading.value) return;
    fetchSites();
});

onMounted(() => {
    fetchSites();
});
</script>

<template>
    <div>
        <h1 class="text-xl font-semibold text-site-heading">Websites</h1>
        <p class="mt-1 text-sm text-site-body">Manage your sites.</p>

        <div class="mt-6 space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <input
                    v-model="searchQuery"
                    type="text"
                    class="max-w-xs rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body placeholder-slate-500"
                    placeholder="Search sites…"
                    @keydown.enter="applySearch"
                />
                <div class="flex items-center gap-2">
                    <RouterLink
                        :to="{ name: 'dashboard-templates' }"
                        class="rounded-lg bg-cta px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-cta-hover"
                    >
                        Create site
                    </RouterLink>
                    <button
                        type="button"
                        class="rounded-lg border border-slate-600 px-3 py-2 text-sm text-site-body hover:bg-slate-800"
                        @click="applySearch"
                    >
                        Search
                    </button>
                </div>
            </div>

            <div v-if="loading" class="py-12 text-center text-site-body">Loading…</div>
            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="site in ownedSites"
                    :key="site.id"
                    class="flex flex-col overflow-hidden rounded-lg border border-slate-800 bg-slate-900/50 transition-colors hover:border-slate-700"
                >
                    <div class="aspect-video shrink-0 bg-slate-800">
                        <img
                            :src="`/media/websites/t_${site.duda_id}.png`"
                            :alt="site.site_name"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <div class="flex flex-1 flex-col p-4">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-medium text-site-heading">{{ site.site_name }}</h3>
                            <span
                                class="shrink-0 rounded px-2 py-0.5 text-xs font-medium"
                                :class="site.is_published
                                    ? 'bg-green-900/60 text-green-300'
                                    : 'bg-slate-700 text-site-body'"
                            >
                                {{ site.is_published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-site-body">{{ site.domain || 'No domain' }}</p>
                        <p class="mt-1 text-xs text-site-body">Created {{ formatDate(site.created_at) }}</p>
                        <div class="mt-3">
                            <button
                                type="button"
                                class="rounded-lg border border-slate-600 px-3 py-1.5 text-sm font-medium text-site-body transition-colors hover:bg-slate-800 disabled:opacity-50"
                                :disabled="editLoadingId === site.id"
                                @click="editWebsite(site)"
                            >
                                {{ editLoadingId === site.id ? 'Opening…' : 'Edit Website' }}
                            </button>
                        </div>
                    </div>
                </article>
            </div>

            <p v-if="!loading && ownedSites.length === 0" class="text-site-body">
                No sites found.
            </p>

            <div v-if="meta.total > 0" class="flex items-center justify-between border-t border-slate-700 pt-4">
                <div class="text-sm text-slate-400">
                    Showing {{ (meta.current_page - 1) * meta.per_page + 1 }}–{{ Math.min(meta.current_page * meta.per_page, meta.total) }} of {{ meta.total }}
                </div>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="rounded px-2 py-1 text-sm text-site-body hover:bg-slate-700 disabled:opacity-50"
                        :disabled="meta.current_page <= 1"
                        @click="goToPage(meta.current_page - 1)"
                    >
                        Previous
                    </button>
                    <span class="px-2 py-1 text-sm text-slate-400">
                        Page {{ meta.current_page }} of {{ meta.last_page }}
                    </span>
                    <button
                        type="button"
                        class="rounded px-2 py-1 text-sm text-site-body hover:bg-slate-700 disabled:opacity-50"
                        :disabled="meta.current_page >= meta.last_page"
                        @click="goToPage(meta.current_page + 1)"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Error modal -->
        <div
            v-show="errorModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="error-modal-title"
        >
            <div class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h2 id="error-modal-title" class="text-lg font-semibold text-red-400">Error</h2>
                <p class="mt-2 text-sm text-site-body">{{ errorModalMessage }}</p>
                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-site-body hover:bg-slate-800"
                        @click="closeErrorModal"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
