<script setup>
import { ref, computed, watch } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import { useSession } from '../composables/useSession';

const toast = useToast();
const { user } = useSession();
const templates = ref([]);
const categories = ref([]);
const meta = ref({ total: 0, per_page: 24, current_page: 1, last_page: 1 });
const loading = ref(false);
const createSiteModalOpen = ref(false);
const createSiteAiModalOpen = ref(false);
const selectedTemplate = ref(null);
const createForm = ref({
    site_name: '',
    site_description: '',
});
const createSubmitting = ref(false);
const createSiteOverlayVisible = ref(false);
const createSiteErrorMessage = ref('');

const filters = ref({
    category_id: '',
    has_store: '',
    has_blog: '',
    has_booking: '',
    ai_enabled: '',
});
const page = ref(1);

const filterParams = computed(() => {
    const params = { page: page.value };
    if (filters.value.category_id) params.category_id = filters.value.category_id;
    if (filters.value.has_store !== '') params.has_store = filters.value.has_store;
    if (filters.value.has_blog !== '') params.has_blog = filters.value.has_blog;
    if (filters.value.has_booking !== '') params.has_booking = filters.value.has_booking;
    if (filters.value.ai_enabled !== '') params.ai_enabled = filters.value.ai_enabled;
    return params;
});

async function fetchTemplates() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/template/index', { params: filterParams.value });
        const payload = data?.data ?? data;
        templates.value = payload?.templates ?? [];
        categories.value = payload?.categories ?? [];
        meta.value = payload?.meta ?? { total: 0, per_page: 24, current_page: 1, last_page: 1 };
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Failed to load templates.';
        toast.error(msg);
        templates.value = [];
    } finally {
        loading.value = false;
    }
}

watch(
    () => ({ ...filters.value, page: page.value }),
    (newVal, oldVal) => {
        const filtersChanged = oldVal
            && (newVal.category_id !== oldVal.category_id
                || newVal.has_store !== oldVal.has_store
                || newVal.has_blog !== oldVal.has_blog
                || newVal.has_booking !== oldVal.has_booking
                || newVal.ai_enabled !== oldVal.ai_enabled);
        if (filtersChanged) {
            page.value = 1;
        }
        fetchTemplates();
    },
    { immediate: true, deep: true }
);

function openPreview(template) {
    if (template?.preview_url) {
        window.open(template.preview_url, '_blank', 'noopener,noreferrer');
    }
}

function openCreateSiteModal(template) {
    selectedTemplate.value = template;
    createForm.value = { site_name: '', site_description: '' };
    createSiteModalOpen.value = true;
}

function openCreateSiteAiModal(template) {
    selectedTemplate.value = template;
    createSiteAiModalOpen.value = true;
}

function closeCreateSiteModal() {
    createSiteModalOpen.value = false;
    selectedTemplate.value = null;
}

async function finishCreateSite() {
    const name = createForm.value.site_name?.trim();
    if (!name) {
        toast.error('Site name is required.');
        return;
    }
    if (!selectedTemplate.value?.template_id) {
        toast.error('No template selected.');
        return;
    }
    createSubmitting.value = true;
    createSiteErrorMessage.value = '';
    createSiteModalOpen.value = false;
    createSiteOverlayVisible.value = true;
    try {
        const { data } = await axios.post('/api/website/createfromtemplate', {
            site_name: name,
            site_description: createForm.value.site_description?.trim() ?? '',
            template_id: selectedTemplate.value.template_id,
            team_id: user.value?.activeTeamId ?? null,
        }, { timeout: 200000 });
        const payload = data?.data ?? data;
        const editorLink = payload?.redirect_url ?? payload?.link;
        if (editorLink) {
            window.location.href = editorLink;
            return;
        }
        createSiteErrorMessage.value = payload?.message ?? 'Could not create site.';
    } catch (err) {
        createSiteErrorMessage.value = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Could not create site.';
    } finally {
        createSubmitting.value = false;
    }
}

function dismissCreateSiteOverlay() {
    createSiteOverlayVisible.value = false;
    createSiteErrorMessage.value = '';
    selectedTemplate.value = null;
}

function closeCreateSiteAiModal() {
    createSiteAiModalOpen.value = false;
    selectedTemplate.value = null;
}

function goToPage(p) {
    if (p >= 1 && p <= meta.value.last_page) {
        page.value = p;
    }
}

</script>

<template>
    <div>
        <h1 class="text-xl font-semibold text-site-heading">Templates</h1>
        <p class="mt-1 text-sm text-site-body">
            Browse templates to create a new site. Use filters to narrow your search.
        </p>

        <!-- Filters -->
        <div class="mt-6 flex flex-wrap items-center gap-4 rounded-lg border border-slate-800 bg-slate-900/50 p-4">
            <select
                id="filter-category"
                v-model="filters.category_id"
                class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-heading"
            >
                <option value="">All categories</option>
                <option
                    v-for="cat in categories"
                    :key="cat.id"
                    :value="cat.id"
                >
                    {{ cat.name }}
                </option>
            </select>
            <button
                type="button"
                class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                :class="filters.has_store ? 'bg-cta text-white hover:bg-cta-hover' : 'border border-slate-600 text-site-body hover:border-slate-500 hover:text-site-heading'"
                @click="filters.has_store = filters.has_store ? '' : '1'"
            >
                Has store
            </button>
            <button
                type="button"
                class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                :class="filters.has_blog ? 'bg-cta text-white hover:bg-cta-hover' : 'border border-slate-600 text-site-body hover:border-slate-500 hover:text-site-heading'"
                @click="filters.has_blog = filters.has_blog ? '' : '1'"
            >
                Has blog
            </button>
            <button
                type="button"
                class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                :class="filters.has_booking ? 'bg-cta text-white hover:bg-cta-hover' : 'border border-slate-600 text-site-body hover:border-slate-500 hover:text-site-heading'"
                @click="filters.has_booking = filters.has_booking ? '' : '1'"
            >
                Has booking
            </button>
            <button
                type="button"
                class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                :class="filters.ai_enabled ? 'bg-cta text-white hover:bg-cta-hover' : 'border border-slate-600 text-site-body hover:border-slate-500 hover:text-site-heading'"
                @click="filters.ai_enabled = filters.ai_enabled ? '' : '1'"
            >
                AI enabled
            </button>
        </div>

        <!-- Template grid -->
        <div class="mt-6">
            <div v-if="loading" class="py-12 text-center text-site-body">Loading…</div>
            <div
                v-else
                class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
            >
                <article
                    v-for="tpl in templates"
                    :key="tpl.id"
                    class="group relative overflow-hidden rounded-lg border border-slate-800 bg-slate-900/50 transition-colors hover:border-slate-700"
                >
                    <div class="relative aspect-[470/310] overflow-hidden bg-slate-800">
                        <img
                            :src="tpl.thumbnail_url"
                            :alt="tpl.name"
                            class="h-full w-full object-cover"
                            loading="lazy"
                            @error="(e) => { e.target.src = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\' fill=\'%334155\'%3E%3Crect width=\'200\' height=\'200\' fill=\'%1e293b\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%64748b\' font-size=\'14\'%3ENo image%3C/text%3E%3C/svg%3E'; }"
                        />
                        <!-- Hover overlay with buttons -->
                        <div
                            class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-black/70 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                        >
                            <button
                                type="button"
                                class="w-44 rounded-lg border border-slate-500 bg-slate-800 px-4 py-2 text-sm font-medium text-site-heading transition-colors hover:bg-slate-700"
                                @click="openPreview(tpl)"
                            >
                                Preview
                            </button>
                            <button
                                type="button"
                                class="w-44 rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-cta-hover"
                                @click="openCreateSiteModal(tpl)"
                            >
                                Create site
                            </button>
                            <button
                                v-if="tpl.ai_enabled"
                                type="button"
                                class="w-44 rounded-lg bg-amber-400 px-4 py-2 text-sm font-medium text-black transition-colors hover:bg-amber-300"
                                @click="openCreateSiteAiModal(tpl)"
                            >
                                Create a site with AI
                            </button>
                        </div>
                    </div>
                    <div class="p-3">
                        <h3 class="font-medium text-site-heading">{{ tpl.name }}</h3>
                        <div class="mt-1 flex flex-wrap gap-1">
                            <span
                                v-if="tpl.has_store"
                                class="rounded px-1.5 py-0.5 text-xs text-slate-400"
                            >
                                Store
                            </span>
                            <span
                                v-if="tpl.has_blog"
                                class="rounded px-1.5 py-0.5 text-xs text-slate-400"
                            >
                                Blog
                            </span>
                            <span
                                v-if="tpl.has_booking"
                                class="rounded px-1.5 py-0.5 text-xs text-slate-400"
                            >
                                Booking
                            </span>
                            <span
                                v-if="tpl.ai_enabled"
                                class="rounded px-1.5 py-0.5 text-xs text-cta"
                            >
                                AI
                            </span>
                        </div>
                    </div>
                </article>
            </div>

            <p
                v-if="!loading && templates.length === 0"
                class="py-8 text-center text-site-body"
            >
                No templates found. Try adjusting your filters.
            </p>
        </div>

        <!-- Pagination -->
        <div
            v-if="meta.last_page > 1"
            class="mt-6 flex items-center justify-center gap-2"
        >
            <button
                type="button"
                class="rounded-lg border border-slate-600 px-3 py-2 text-sm text-site-body hover:bg-slate-800 disabled:opacity-50"
                :disabled="page === 1"
                @click="goToPage(page - 1)"
            >
                Previous
            </button>
            <span class="px-4 text-sm text-site-body">
                Page {{ meta.current_page }} of {{ meta.last_page }} ({{ meta.total }} total)
            </span>
            <button
                type="button"
                class="rounded-lg border border-slate-600 px-3 py-2 text-sm text-site-body hover:bg-slate-800 disabled:opacity-50"
                :disabled="page >= meta.last_page"
                @click="goToPage(page + 1)"
            >
                Next
            </button>
        </div>

        <!-- Create site modal -->
        <div
            v-show="createSiteModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="create-site-modal-title"
        >
            <div class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <h2 id="create-site-modal-title" class="text-lg font-semibold text-site-heading">
                        Create site from {{ selectedTemplate?.name ?? 'template' }}
                    </h2>
                    <button
                        type="button"
                        class="rounded p-1 text-site-body hover:bg-slate-700 hover:text-site-heading"
                        aria-label="Close"
                        @click="closeCreateSiteModal"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-6 space-y-4">
                    <div>
                        <label for="create-site-name" class="block text-sm font-medium text-site-body">Site name</label>
                        <input
                            id="create-site-name"
                            v-model="createForm.site_name"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading"
                            placeholder="My website"
                        />
                    </div>
                    <div>
                        <label for="create-site-description" class="block text-sm font-medium text-site-body">Site description</label>
                        <textarea
                            id="create-site-description"
                            v-model="createForm.site_description"
                            rows="3"
                            class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading"
                            placeholder="A brief description of your site"
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-site-body hover:bg-slate-800"
                        :disabled="createSubmitting"
                        @click="closeCreateSiteModal"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white hover:bg-cta-hover disabled:opacity-60"
                        :disabled="createSubmitting"
                        @click="finishCreateSite"
                    >
                        {{ createSubmitting ? 'Creating…' : 'Create site' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Create site with AI placeholder modal -->
        <div
            v-show="createSiteAiModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="create-site-ai-modal-title"
        >
            <div class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h2 id="create-site-ai-modal-title" class="text-lg font-semibold text-site-heading">
                    Create a site with AI
                </h2>
                <p class="mt-2 text-sm text-site-body">
                    This feature is coming soon. You selected: {{ selectedTemplate?.name ?? '—' }}
                </p>
                <div class="mt-4">
                    <button
                        type="button"
                        class="rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white hover:bg-cta-hover"
                        @click="closeCreateSiteAiModal"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Create site loading overlay -->
        <div
            v-show="createSiteOverlayVisible"
            class="fixed inset-0 z-[60] flex flex-col items-center justify-center bg-black/90 px-4"
            role="status"
            aria-live="polite"
            aria-label="Creating site"
        >
            <div
                v-if="!createSiteErrorMessage"
                class="flex flex-col items-center gap-6"
            >
                <div
                    class="h-14 w-14 animate-spin rounded-full border-4 border-slate-600 border-t-cta"
                    aria-hidden="true"
                />
                <p class="text-lg font-medium text-site-heading">
                    Creating your site…
                </p>
                <p class="text-sm text-site-body">
                    This may take up to 30 seconds. Please wait.
                </p>
            </div>
            <div
                v-else
                class="flex flex-col items-center gap-6 rounded-xl border border-red-800 bg-slate-900 p-8 shadow-xl max-w-md"
            >
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-900/50">
                    <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-site-heading">
                    Could not create site
                </h3>
                <p class="text-center text-site-body">
                    {{ createSiteErrorMessage }}
                </p>
                <RouterLink
                    :to="{ name: 'dashboard-templates' }"
                    class="rounded-lg bg-cta px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-cta-hover"
                    @click="dismissCreateSiteOverlay"
                >
                    Back to templates
                </RouterLink>
            </div>
        </div>
    </div>
</template>
