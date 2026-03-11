<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '../../composables/useToast';

const toast = useToast();
const entries = ref([]);
const meta = ref({ total: 0, per_page: 25, current_page: 1, last_page: 1 });
const loading = ref(true);

const filters = ref({
    method: '',
    date_from: '',
    date_to: '',
    uri: '',
    code: '',
    search: '',
});
const presetDropdownOpen = ref(false);
const currentPage = ref(1);
const perPage = ref(25);

const orderBy = ref('timestamp_request');
const orderDir = ref('desc');

const METHOD_OPTIONS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

const DATE_PRESETS = [
    { value: 'last_hour', label: 'In the last hour' },
];

const popupContent = ref('');
const popupTitle = ref('');
const popupOpen = ref(false);

function truncate(str, maxLen = 60) {
    if (str == null || str === '') return '—';
    const s = String(str);
    if (s.length <= maxLen) return s;
    return s.slice(0, maxLen) + '…';
}

function openPopup(title, content) {
    popupTitle.value = title;
    popupContent.value = content == null || content === '' ? '—' : String(content);
    popupOpen.value = true;
}

function closePopup() {
    popupOpen.value = false;
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
        if (filters.value.method) params.method = filters.value.method;
        if (filters.value.date_from) params.date_from = filters.value.date_from;
        if (filters.value.date_to) params.date_to = filters.value.date_to;
        if (filters.value.uri.trim()) params.uri = filters.value.uri.trim();
        if (filters.value.code !== '' && filters.value.code != null) params.code = filters.value.code;
        if (filters.value.search.trim()) params.search = filters.value.search.trim();

        const { data } = await axios.get('/api/administration/dudaapilogindex', { params });
        const payload = data?.data ?? data;
        entries.value = payload?.entries ?? [];
        meta.value = payload?.meta ?? { total: 0, per_page: 25, current_page: 1, last_page: 1 };
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Failed to load Duda API logs.';
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

function applyDatePreset(presetValue) {
    if (presetValue === 'last_hour') {
        const now = new Date();
        const from = new Date(now.getTime() - 60 * 60 * 1000);
        filters.value.date_from = formatDateTimeLocal(from);
        filters.value.date_to = formatDateTimeLocal(now);
    }
    presetDropdownOpen.value = false;
}

function formatDateTimeLocal(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const h = String(d.getHours()).padStart(2, '0');
    const min = String(d.getMinutes()).padStart(2, '0');
    return `${y}-${m}-${day}T${h}:${min}`;
}

function clearFilters() {
    filters.value = { method: '', date_from: '', date_to: '', uri: '', code: '', search: '' };
    presetDropdownOpen.value = false;
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
            <h1 class="text-xl font-semibold text-site-heading">Duda API Log</h1>
            <p class="mt-1 text-sm text-site-body">
                View and filter Duda API request/response logs. Requires admin code D or A.
            </p>
        </div>

        <!-- Filters -->
        <div class="mt-6 rounded-xl border border-slate-700 bg-slate-900/50 p-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400">Method</label>
                    <select
                        v-model="filters.method"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                    >
                        <option value="">All</option>
                        <option v-for="m in METHOD_OPTIONS" :key="m" :value="m">{{ m }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">URI (contains)</label>
                    <input
                        v-model="filters.uri"
                        type="text"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                        placeholder="Filter by URI…"
                        @keydown.enter="applyFilters"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Code</label>
                    <input
                        v-model="filters.code"
                        type="text"
                        inputmode="numeric"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                        placeholder="e.g. 200, 404"
                        @keydown.enter="applyFilters"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Search in payload / response</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                        placeholder="Text in payload or response…"
                        @keydown.enter="applyFilters"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">From date</label>
                    <input
                        v-model="filters.date_from"
                        type="datetime-local"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">To date</label>
                    <input
                        v-model="filters.date_to"
                        type="datetime-local"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                    />
                </div>
                <div class="relative flex items-end">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body hover:bg-slate-700"
                        :class="{ 'ring-1 ring-indigo-500': presetDropdownOpen }"
                        aria-haspopup="listbox"
                        :aria-expanded="presetDropdownOpen"
                        @click="presetDropdownOpen = !presetDropdownOpen"
                    >
                        Date presets
                    </button>
                    <div
                        v-show="presetDropdownOpen"
                        class="absolute left-0 top-full z-10 mt-1 min-w-[12rem] rounded-lg border border-slate-700 bg-slate-900 py-1 shadow-lg"
                        role="listbox"
                    >
                        <button
                            v-for="p in DATE_PRESETS"
                            :key="p.value"
                            type="button"
                            class="w-full px-3 py-2 text-left text-sm text-site-body hover:bg-slate-700"
                            role="option"
                            :aria-selected="false"
                            @click="applyDatePreset(p.value)"
                        >
                            {{ p.label }}
                        </button>
                    </div>
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

        <!-- Table -->
        <div class="mt-6 rounded-xl border border-slate-700 bg-slate-900/50 p-4">
            <div v-if="loading" class="py-8 text-center text-xs text-site-body">Loading…</div>
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-700">
                    <thead>
                        <tr>
                            <th class="p-1 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('id')">Id {{ sortIcon('id') }}</button>
                            </th>
                            <th class="p-1 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('timestamp_request')">Request {{ sortIcon('timestamp_request') }}</button>
                            </th>
                            <th class="p-1 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('method')">Method {{ sortIcon('method') }}</button>
                            </th>
                            <th class="p-1 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('uri')">URI {{ sortIcon('uri') }}</button>
                            </th>
                            <th class="p-1 text-left text-xs font-medium text-slate-400">Payload</th>
                            <th class="p-1 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('response_code')">Code {{ sortIcon('response_code') }}</button>
                            </th>
                            <th class="p-1 text-left text-xs font-medium text-slate-400">Response data</th>
                            <th class="p-1 text-left text-xs font-medium text-slate-400">
                                <button type="button" class="cursor-pointer hover:text-site-heading" @click="setSort('response_time')">Time (ms) {{ sortIcon('response_time') }}</button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="e in entries" :key="e.id">
                            <td class="p-1 text-xs text-site-body">{{ e.id }}</td>
                            <td class="p-1 text-xs text-site-body">{{ e.timestamp_request ?? '—' }}</td>
                            <td class="p-1 text-xs text-site-body">{{ e.method ?? '—' }}</td>
                            <td class="p-1 text-xs">
                                <button
                                    v-if="e.uri != null && e.uri !== ''"
                                    type="button"
                                    class="block w-full min-w-0 truncate text-left text-indigo-400 hover:text-indigo-300 hover:underline"
                                    :title="e.uri"
                                    @click="openPopup('URI', e.uri)"
                                >
                                    {{ e.uri }}
                                </button>
                                <span v-else class="text-site-body">—</span>
                            </td>
                            <td class="p-1 text-xs">
                                <button
                                    v-if="e.payload != null && e.payload !== ''"
                                    type="button"
                                    class="max-w-[120px] truncate text-left text-indigo-400 hover:text-indigo-300 hover:underline"
                                    title="Click to view payload"
                                    @click="openPopup('Payload', e.payload)"
                                >
                                    {{ truncate(e.payload, 30) }}
                                </button>
                                <span v-else class="text-site-body">—</span>
                            </td>
                            <td class="p-1 text-xs text-site-body">{{ e.response_code ?? '—' }}</td>
                            <td class="p-1 text-xs">
                                <button
                                    v-if="e.response_data != null && e.response_data !== ''"
                                    type="button"
                                    class="max-w-[120px] truncate text-left text-indigo-400 hover:text-indigo-300 hover:underline"
                                    title="Click to view response data"
                                    @click="openPopup('Response data', e.response_data)"
                                >
                                    {{ truncate(e.response_data, 30) }}
                                </button>
                                <span v-else class="text-site-body">—</span>
                            </td>
                            <td class="p-1 text-xs text-site-body">{{ e.response_time ?? '—' }}</td>
                        </tr>
                        <tr v-if="entries.length === 0">
                            <td colspan="8" class="p-1 py-8 text-center text-xs text-slate-500">No entries found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
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

        <!-- Popup modal for long text -->
        <div
            v-show="popupOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
            @click.self="closePopup"
        >
            <div
                class="flex max-h-[90vh] w-full max-w-3xl flex-col rounded-xl border border-slate-700 bg-slate-900 shadow-xl"
                role="dialog"
                aria-modal="true"
                :aria-label="popupTitle"
            >
                <div class="flex shrink-0 items-center justify-between border-b border-slate-700 px-4 py-3">
                    <h3 class="text-lg font-medium text-site-heading">{{ popupTitle }}</h3>
                    <button
                        type="button"
                        class="rounded p-2 text-site-body hover:bg-slate-700"
                        aria-label="Close"
                        @click="closePopup"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="min-h-0 flex-1 overflow-auto p-4">
                    <pre class="whitespace-pre-wrap break-words text-xs text-site-body font-mono">{{ popupContent }}</pre>
                </div>
            </div>
        </div>
    </div>
</template>
