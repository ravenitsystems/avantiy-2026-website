<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const activeTab = ref('owned');
const ownedSites = ref([]);
const sharedSites = ref([]);
const loading = ref(false);
const createLoading = ref(false);
const createFormOpen = ref(false);
const createSiteName = ref('');
const createDomain = ref('');
const createError = ref('');

const tabs = [
    { id: 'owned', label: 'Customer Owned Sites' },
    { id: 'shared', label: 'Shared With Me' },
];

async function fetchOwnedSites() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/website/index');
        const payload = data?.data ?? data;
        ownedSites.value = payload?.websites ?? [];
    } catch {
        ownedSites.value = [];
    } finally {
        loading.value = false;
    }
}

function formatDate(isoString) {
    if (!isoString) return '—';
    return new Date(isoString).toLocaleDateString(undefined, { dateStyle: 'short' });
}

function openCreateForm() {
    createFormOpen.value = true;
    createSiteName.value = '';
    createDomain.value = '';
    createError.value = '';
}

function closeCreateForm() {
    createFormOpen.value = false;
}

async function submitCreate() {
    createError.value = '';
    const name = createSiteName.value?.trim();
    if (!name) {
        createError.value = 'Site name is required.';
        return;
    }
    createLoading.value = true;
    try {
        await axios.post('/api/website/create', {
            site_name: name,
            domain: createDomain.value?.trim() ?? '',
        });
        closeCreateForm();
        await fetchOwnedSites();
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Could not create site.';
        createError.value = msg;
    } finally {
        createLoading.value = false;
    }
}

onMounted(() => {
    fetchOwnedSites();
});
</script>

<template>
    <div>
        <h1 class="text-xl font-semibold text-site-heading">Websites</h1>
        <p class="mt-1 text-sm text-site-body">Manage your customer-owned sites and sites shared with you.</p>

        <!-- Tabs -->
        <div class="mt-6 border-b border-slate-800">
            <nav class="-mb-px flex gap-6" aria-label="Tabs">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    class="border-b-2 py-3 text-sm font-medium transition-colors"
                    :class="activeTab === tab.id
                        ? 'border-site-accent text-site-heading'
                        : 'border-transparent text-site-body hover:border-slate-600 hover:text-site-heading'"
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                </button>
            </nav>
        </div>

        <!-- Tab panels -->
        <div class="mt-6">
            <div v-show="activeTab === 'owned'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-site-body">Sites you own</span>
                    <button
                        type="button"
                        class="rounded-lg bg-cta px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-cta-hover"
                        @click="openCreateForm"
                    >
                        Create site
                    </button>
                </div>
                <div v-if="loading" class="py-6 text-center text-site-body">Loading…</div>
                <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="site in ownedSites"
                        :key="site.id"
                        class="rounded-lg border border-slate-800 bg-slate-900/50 p-4 transition-colors hover:border-slate-700"
                    >
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
                        <p class="mt-1 text-sm text-site-body">
                            {{ site.domain || 'No domain' }}
                        </p>
                        <p class="mt-1 text-xs text-site-body">Created {{ formatDate(site.created_at) }}</p>
                    </article>
                </div>
                <p v-if="!loading && activeTab === 'owned' && ownedSites.length === 0" class="text-site-body">
                    No customer-owned sites yet. Create one to get started.
                </p>
            </div>

            <div v-show="activeTab === 'shared'" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="site in sharedSites"
                        :key="site.id"
                        class="rounded-lg border border-slate-800 bg-slate-900/50 p-4 transition-colors hover:border-slate-700"
                    >
                        <h3 class="font-medium text-site-heading">{{ site.site_name }}</h3>
                        <p class="mt-1 text-sm text-site-body">{{ site.domain || 'No domain' }}</p>
                    </article>
                </div>
                <p v-if="activeTab === 'shared' && sharedSites.length === 0" class="text-site-body">
                    No sites shared with you yet.
                </p>
            </div>
        </div>

        <!-- Create site modal/form -->
        <div
            v-show="createFormOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="create-site-title"
        >
            <div class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h2 id="create-site-title" class="text-lg font-semibold text-site-heading">Create a site</h2>
                <p class="mt-1 text-sm text-site-body">New sites are created as drafts and are not viewable on the internet until published.</p>
                <form class="mt-4 space-y-4" @submit.prevent="submitCreate">
                    <div v-if="createError" class="rounded-lg bg-red-900/40 px-3 py-2 text-sm text-red-300">
                        {{ createError }}
                    </div>
                    <div>
                        <label for="create-site-name" class="block text-sm font-medium text-site-body">Site name</label>
                        <input
                            id="create-site-name"
                            v-model="createSiteName"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading"
                            placeholder="My website"
                        />
                    </div>
                    <div>
                        <label for="create-domain" class="block text-sm font-medium text-site-body">Domain (optional)</label>
                        <input
                            id="create-domain"
                            v-model="createDomain"
                            type="text"
                            class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading"
                            placeholder="example.com"
                        />
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-site-body hover:bg-slate-800"
                            @click="closeCreateForm"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="createLoading"
                            class="rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white hover:bg-cta-hover disabled:opacity-60"
                        >
                            {{ createLoading ? 'Creating…' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
