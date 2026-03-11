<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '../../composables/useToast';

const toast = useToast();
const entries = ref([]);
const meta = ref({ total: 0, per_page: 25, current_page: 1, last_page: 1 });
const loading = ref(true);

const filters = ref({ search: '', enabled: '1' });
const currentPage = ref(1);
const perPage = ref(25);
const orderBy = ref('name');
const orderDir = ref('asc');

function hasFlagSvg(row) {
    return row && typeof row.flag_svg === 'string' && row.flag_svg.trim() !== '';
}

async function fetchEntries() {
    loading.value = true;
    try {
        const params = {
            page: currentPage.value,
            per_page: perPage.value,
            order_by: orderBy.value,
            order_dir: orderDir.value,
        };
        if (filters.value.search.trim()) params.search = filters.value.search.trim();
        if (filters.value.enabled !== '') params.enabled = filters.value.enabled;

        const { data } = await axios.get('/api/administration/countryindex', { params });
        const payload = data?.data ?? data;
        entries.value = payload?.entries ?? [];
        meta.value = payload?.meta ?? { total: 0, per_page: 25, current_page: 1, last_page: 1 };
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Failed to load countries.';
        toast.error(msg);
        entries.value = [];
    } finally {
        loading.value = false;
    }
}

function applyFilters() {
    currentPage.value = 1;
    fetchEntries();
}

function clearFilters() {
    filters.value = { search: '', enabled: '1' };
    applyFilters();
}

function setSort(field) {
    if (orderBy.value === field) {
        orderDir.value = orderDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        orderBy.value = field;
        orderDir.value = 'asc';
    }
    currentPage.value = 1;
    fetchEntries();
}

function sortIcon(field) {
    if (orderBy.value !== field) return '↕';
    return orderDir.value === 'asc' ? '↑' : '↓';
}

function goToPage(p) {
    if (p >= 1 && p <= meta.value.last_page) {
        currentPage.value = p;
        fetchEntries();
    }
}

async function toggleEnabled(row) {
    try {
        const { data } = await axios.post(`/api/administration/countryupdate/${row.id}`, {
            enabled: !row.enabled,
        });
        if (data?.status === 'FAIL') {
            toast.error(data?.data?.message ?? 'Update failed.');
            return;
        }
        row.enabled = !row.enabled;
        toast.success(data?.data?.message ?? 'Updated.');
    } catch (err) {
        toast.error(err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Update failed.');
    }
}

watch([currentPage, perPage], () => {
    if (loading.value) return;
    fetchEntries();
});

onMounted(() => {
    fetchEntries();
});
</script>

<template>
    <div class="max-w-full">
        <div>
            <h1 class="text-xl font-semibold text-site-heading">Countries</h1>
            <p class="mt-1 text-sm text-site-body">
                Search, filter and manage countries. SITE ADMIN.
            </p>
        </div>

        <div class="mt-6 rounded-xl border border-slate-700 bg-slate-900/50 p-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400">Search (name, code, dial)</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                        placeholder="Search…"
                        @keydown.enter="applyFilters"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Enabled</label>
                    <select
                        v-model="filters.enabled"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                    >
                        <option value="">All</option>
                        <option value="1">Enabled</option>
                        <option value="0">Disabled</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button
                    type="button"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                    @click="applyFilters"
                >
                    Apply filters
                </button>
                <button
                    type="button"
                    class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-site-body hover:bg-slate-700"
                    @click="clearFilters"
                >
                    Clear
                </button>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-slate-700 bg-slate-900/50 p-4">
            <div v-if="loading" class="py-8 text-center text-xs text-site-body">Loading…</div>
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-700">
                    <thead>
                        <tr>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">Flag</th>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('name')">Name {{ sortIcon('name') }}</button>
                            </th>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('alpha_2')">Alpha-2 {{ sortIcon('alpha_2') }}</button>
                            </th>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('alpha_3')">Alpha-3 {{ sortIcon('alpha_3') }}</button>
                            </th>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('dial_code')">Dial code {{ sortIcon('dial_code') }}</button>
                            </th>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('order_index')">Order {{ sortIcon('order_index') }}</button>
                            </th>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">Currency</th>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">Override</th>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('enabled')">Enabled {{ sortIcon('enabled') }}</button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="row in entries" :key="row.id">
                            <td class="p-2">
                                <span
                                    v-if="hasFlagSvg(row)"
                                    class="country-flag-svg inline-block h-6 w-10 rounded overflow-hidden"
                                    v-html="row.flag_svg"
                                />
                                <span v-else class="inline-block h-6 w-10 rounded bg-slate-600" />
                            </td>
                            <td class="p-2 text-xs text-site-body">{{ row.name }}</td>
                            <td class="p-2 text-xs text-site-body">{{ row.alpha_2 }}</td>
                            <td class="p-2 text-xs text-site-body">{{ row.alpha_3 }}</td>
                            <td class="p-2 text-xs text-site-body">{{ row.dial_code }}</td>
                            <td class="p-2 text-xs text-site-body">{{ row.order_index }}</td>
                            <td class="p-2 text-xs text-site-body">{{ row.currency ?? '—' }}</td>
                            <td class="p-2 text-xs text-site-body">{{ row.currency_override != null && row.currency_override !== '' ? 'Yes' : '—' }}</td>
                            <td class="p-2">
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-xs font-medium"
                                    :class="row.enabled ? 'bg-emerald-900/50 text-emerald-300' : 'bg-slate-700 text-slate-400'"
                                    @click="toggleEnabled(row)"
                                >
                                    {{ row.enabled ? 'Yes' : 'No' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="entries.length === 0">
                            <td colspan="9" class="p-2 py-8 text-center text-xs text-slate-500">No countries found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="meta.total > 0" class="mt-4 flex items-center justify-between border-t border-slate-700 pt-4">
                <div class="text-xs text-slate-400">
                    Showing {{ (meta.current_page - 1) * meta.per_page + 1 }}–{{ Math.min(meta.current_page * meta.per_page, meta.total) }} of {{ meta.total }}
                </div>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="rounded px-2 py-1 text-xs text-site-body hover:bg-slate-700 disabled:opacity-50"
                        :disabled="meta.current_page <= 1"
                        @click="goToPage(meta.current_page - 1)"
                    >
                        Previous
                    </button>
                    <span class="px-2 py-1 text-xs text-slate-400">
                        Page {{ meta.current_page }} of {{ meta.last_page }}
                    </span>
                    <button
                        type="button"
                        class="rounded px-2 py-1 text-xs text-site-body hover:bg-slate-700 disabled:opacity-50"
                        :disabled="meta.current_page >= meta.last_page"
                        @click="goToPage(meta.current_page + 1)"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.country-flag-svg :deep(svg) {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}
</style>
