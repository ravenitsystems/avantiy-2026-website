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
const orderBy = ref('code');
const orderDir = ref('asc');

const modalOpen = ref(false);
const modalSaving = ref(false);
const form = ref({
    name: '',
    code: '',
    symbol: '',
    decimals: 2,
    exchange_rate: '0',
    enabled: true,
});
const editRowId = ref(null);

const flagDialogOpen = ref(false);
const flagDialogRow = ref(null);
const flagDialogSvgContent = ref('');
const flagDialogSaving = ref(false);
const refreshingRateId = ref(null);

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

        const { data } = await axios.get('/api/administration/currencyindex', { params });
        const payload = data?.data ?? data;
        entries.value = payload?.entries ?? [];
        meta.value = payload?.meta ?? { total: 0, per_page: 25, current_page: 1, last_page: 1 };
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Failed to load currencies.';
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
        const { data } = await axios.post(`/api/administration/currencyupdate/${row.id}`, {
            enabled: !row.enabled,
        });
        if (data?.status === 'FAIL') {
            toast.error(data?.data?.message ?? 'Update failed.');
            return;
        }
        row.enabled = !row.enabled;
        toast.success(data?.data?.message ?? 'Updated.');
        if (row.enabled) {
            await refreshRate(row);
        }
    } catch (err) {
        toast.error(err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Update failed.');
    }
}

async function refreshRate(row) {
    if (refreshingRateId.value !== null) return;
    refreshingRateId.value = row.id;
    try {
        const { data } = await axios.post(`/api/administration/currencyrefreshrate/${row.id}`);
        if (data?.status === 'FAIL') {
            toast.error(data?.data?.message ?? 'Refresh failed.');
            return;
        }
        const rate = data?.data?.currency?.exchange_rate ?? data?.currency?.exchange_rate;
        if (rate !== undefined) {
            row.exchange_rate = rate;
        }
        toast.success(data?.data?.message ?? data?.message ?? 'Rate updated.');
    } catch (err) {
        toast.error(err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Refresh failed.');
    } finally {
        refreshingRateId.value = null;
    }
}

function openEdit(row) {
    editRowId.value = row.id;
    form.value = {
        name: row.name ?? '',
        code: row.code ?? '',
        symbol: row.symbol ?? '',
        decimals: row.decimals ?? 2,
        exchange_rate: String(row.exchange_rate ?? '0'),
        enabled: row.enabled ?? true,
    };
    modalOpen.value = true;
}

function openFlagDialog(row) {
    flagDialogRow.value = row;
    flagDialogSvgContent.value = (row.flag_svg && typeof row.flag_svg === 'string') ? row.flag_svg : '';
    flagDialogOpen.value = true;
}

function closeFlagDialog() {
    flagDialogOpen.value = false;
    flagDialogRow.value = null;
    flagDialogSvgContent.value = '';
}

async function saveFlagSvg() {
    const row = flagDialogRow.value;
    if (!row) return;
    flagDialogSaving.value = true;
    try {
        const { data } = await axios.post(`/api/administration/currencyupdateflagsvg/${row.id}`, {
            flag_svg: flagDialogSvgContent.value.trim(),
        });
        if (data?.status === 'FAIL') {
            toast.error(data?.data?.message ?? 'Update failed.');
            return;
        }
        row.flag_svg = data?.data?.flag_svg ?? null;
        toast.success(data?.data?.message ?? 'Flag updated.');
        closeFlagDialog();
    } catch (err) {
        toast.error(err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Update failed.');
    } finally {
        flagDialogSaving.value = false;
    }
}

function closeModal() {
    modalOpen.value = false;
}

async function submitModal() {
    if (!(form.value.name && form.value.name.trim())) {
        toast.error('Name is required.');
        return;
    }
    const code = String(form.value.code ?? '').trim().toUpperCase().slice(0, 3);
    if (!code) {
        toast.error('Code is required (up to 3 letters).');
        return;
    }
    modalSaving.value = true;
    try {
        const { data } = await axios.post(`/api/administration/currencyupdate/${editRowId.value}`, {
            name: form.value.name.trim(),
            code,
            symbol: (form.value.symbol ?? '').trim() || code,
            decimals: Math.min(4, Math.max(0, parseInt(form.value.decimals, 10) || 0)),
            exchange_rate: parseFloat(form.value.exchange_rate) || 0,
            enabled: form.value.enabled,
        });
        if (data?.status === 'FAIL') {
            toast.error(data?.data?.message ?? 'Update failed.');
            return;
        }
        toast.success(data?.data?.message ?? 'Currency updated.');
        const row = entries.value.find((e) => e.id === editRowId.value);
        if (row) {
            row.name = form.value.name.trim();
            row.code = code;
            row.symbol = (form.value.symbol ?? '').trim() || code;
            row.decimals = Math.min(4, Math.max(0, parseInt(form.value.decimals, 10) || 0));
            row.exchange_rate = String(parseFloat(form.value.exchange_rate) || 0);
            row.enabled = form.value.enabled;
        }
        closeModal();
    } catch (err) {
        toast.error(err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Request failed.');
    } finally {
        modalSaving.value = false;
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
            <h1 class="text-xl font-semibold text-site-heading">Currencies</h1>
            <p class="mt-1 text-sm text-site-body">
                Search, filter and manage currencies. SITE ADMIN.
            </p>
        </div>

        <div class="mt-6 rounded-xl border border-slate-700 bg-slate-900/50 p-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400">Search (name, code, symbol)</label>
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
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('code')">Code {{ sortIcon('code') }}</button>
                            </th>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('symbol')">Symbol {{ sortIcon('symbol') }}</button>
                            </th>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">Decimals</th>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">Exchange rate</th>
                            <th class="p-2 text-left text-xs font-medium text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="row in entries" :key="row.id">
                            <td class="p-2">
                                <button
                                    type="button"
                                    class="block rounded overflow-hidden focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    :title="'Edit flag: ' + (row.name || row.code)"
                                    @click="openFlagDialog(row)"
                                >
                                    <span
                                        v-if="hasFlagSvg(row)"
                                        class="currency-flag-svg inline-block h-6 w-10 rounded"
                                        v-html="row.flag_svg"
                                    />
                                    <span v-else class="inline-block h-6 w-10 rounded bg-slate-600" />
                                </button>
                            </td>
                            <td class="p-2 text-xs text-site-body">{{ row.name }}</td>
                            <td class="p-2 text-xs text-site-body">{{ row.code }}</td>
                            <td class="p-2 text-xs text-site-body">{{ row.symbol }}</td>
                            <td class="p-2 text-xs text-site-body">{{ row.decimals }}</td>
                            <td class="p-2 text-xs text-site-body">{{ row.exchange_rate }}</td>
                            <td class="p-2 flex gap-1">
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-xs text-indigo-400 hover:bg-slate-700"
                                    @click="openEdit(row)"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-xs text-sky-400 hover:bg-slate-700 disabled:opacity-50"
                                    :disabled="refreshingRateId !== null || !row.enabled"
                                    :title="row.enabled ? 'Get current exchange rate for ' + (row.code || '') : 'Enable the currency to refresh rate'"
                                    @click="refreshRate(row)"
                                >
                                    {{ refreshingRateId === row.id ? 'Refreshing…' : 'Refresh rate' }}
                                </button>
                                <button
                                    type="button"
                                    class="rounded px-2 py-1 text-xs hover:bg-slate-700"
                                    :class="row.enabled ? 'text-green-400' : 'text-red-400'"
                                    @click="toggleEnabled(row)"
                                >
                                    {{ row.enabled ? 'Enabled' : 'Disabled' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="entries.length === 0">
                            <td colspan="7" class="p-2 py-8 text-center text-xs text-slate-500">No currencies found.</td>
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

        <!-- Edit modal -->
        <div
            v-show="modalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
            @click.self="closeModal"
        >
            <div
                class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 shadow-xl"
                role="dialog"
                aria-modal="true"
                aria-label="Edit currency"
            >
                <div class="flex items-center justify-between border-b border-slate-700 px-4 py-3">
                    <h3 class="text-lg font-medium text-site-heading">Edit currency</h3>
                    <button type="button" class="rounded p-2 text-site-body hover:bg-slate-700" aria-label="Close" @click="closeModal">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form class="p-4 space-y-4" @submit.prevent="submitModal">
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            maxlength="64"
                            class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                            placeholder="e.g. US Dollar"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Code (3 letters)</label>
                        <input
                            v-model="form.code"
                            type="text"
                            maxlength="3"
                            class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body uppercase"
                            placeholder="USD"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Symbol</label>
                        <input
                            v-model="form.symbol"
                            type="text"
                            maxlength="16"
                            class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                            placeholder="$"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Decimals</label>
                        <input
                            v-model.number="form.decimals"
                            type="number"
                            min="0"
                            max="4"
                            class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400">Exchange rate</label>
                        <input
                            v-model="form.exchange_rate"
                            type="number"
                            min="0"
                            step="any"
                            class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            id="form-enabled"
                            v-model="form.enabled"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-600 bg-slate-800 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label for="form-enabled" class="text-sm text-site-body">Enabled</label>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-site-body hover:bg-slate-700" @click="closeModal">Cancel</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50" :disabled="modalSaving">
                            {{ modalSaving ? 'Saving…' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Flag SVG edit dialog -->
        <div
            v-show="flagDialogOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
            @click.self="closeFlagDialog"
        >
            <div
                class="flex max-h-[90vh] w-full max-w-2xl flex-col rounded-xl border border-slate-700 bg-slate-900 shadow-xl"
                role="dialog"
                aria-modal="true"
                aria-label="Edit flag SVG"
            >
                <div class="flex shrink-0 items-center justify-between border-b border-slate-700 px-4 py-3">
                    <h3 class="text-lg font-medium text-site-heading">
                        Edit flag SVG{{ flagDialogRow ? ` – ${flagDialogRow.name} (${flagDialogRow.symbol})` : '' }}
                    </h3>
                    <button type="button" class="rounded p-2 text-site-body hover:bg-slate-700" aria-label="Close" @click="closeFlagDialog">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="min-h-0 flex-1 overflow-hidden flex flex-col p-4">
                    <p class="mb-2 text-xs text-slate-400">
                        Paste SVG markup below. Leave empty to clear the flag.
                    </p>
                    <textarea
                        v-model="flagDialogSvgContent"
                        class="min-h-[200px] w-full flex-1 rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 font-mono text-xs text-site-body placeholder-slate-500 focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                        placeholder="<svg>...</svg>"
                        spellcheck="false"
                    />
                </div>
                <div class="flex shrink-0 justify-end gap-2 border-t border-slate-700 px-4 py-3">
                    <button type="button" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-site-body hover:bg-slate-700" @click="closeFlagDialog">Cancel</button>
                    <button type="button" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50" :disabled="flagDialogSaving" @click="saveFlagSvg">
                        {{ flagDialogSaving ? 'Saving…' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.currency-flag-svg :deep(svg) {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}
</style>
