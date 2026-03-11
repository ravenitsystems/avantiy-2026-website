<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '../../composables/useToast';

const toast = useToast();
const entries = ref([]);
const meta = ref({ total: 0, per_page: 25, current_page: 1, last_page: 1 });
const loading = ref(true);
const filterTeams = ref([]);
const filterActionClasses = ref([]);
const filterActions = ref([]);

const filters = ref({
    user_email: '',
    team_id: '',
    action_class: '',
    action_name: '',
    ip_address: '',
    date_from: '',
    date_to: '',
});
const currentPage = ref(1);
const perPage = ref(25);

async function fetchFilters() {
    try {
        const params = filters.value.action_class ? { action_class: filters.value.action_class } : {};
        const { data } = await axios.get('/api/administration/activitylogfilters', { params });
        const payload = data?.data ?? data;
        filterTeams.value = payload?.teams ?? [];
        filterActionClasses.value = payload?.action_classes ?? [];
        filterActions.value = payload?.actions ?? [];
    } catch {
        filterTeams.value = [];
        filterActionClasses.value = [];
        filterActions.value = [];
    }
}

async function fetchEntries() {
    loading.value = true;
    try {
        const params = {
            page: currentPage.value,
            per_page: perPage.value,
        };
        if (filters.value.user_email) params.user_email = filters.value.user_email;
        if (filters.value.team_id) params.team_id = filters.value.team_id;
        if (filters.value.action_class) params.action_class = filters.value.action_class;
        if (filters.value.action_name) params.action_name = filters.value.action_name;
        if (filters.value.ip_address) params.ip_address = filters.value.ip_address;
        if (filters.value.date_from) params.date_from = filters.value.date_from;
        if (filters.value.date_to) params.date_to = filters.value.date_to;

        const { data } = await axios.get('/api/administration/activitylogindex', { params });
        const payload = data?.data ?? data;
        entries.value = payload?.entries ?? [];
        meta.value = payload?.meta ?? { total: 0, per_page: 25, current_page: 1, last_page: 1 };
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Failed to load activity log.';
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

watch(() => filters.value.action_class, () => {
    filters.value.action_name = '';
    fetchFilters();
});

onMounted(() => {
    fetchFilters();
    fetchEntries();
});
</script>

<template>
    <div class="max-w-6xl">
        <div>
            <h1 class="text-xl font-semibold text-site-heading">Activity Log</h1>
            <p class="mt-1 text-sm text-site-body">View and filter activity log entries. Requires admin code A or C (Customer Admin).</p>
        </div>

        <!-- Filters -->
        <div class="mt-6 rounded-xl border border-slate-700 bg-slate-900/50 p-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <div>
                    <label class="block text-xs font-medium text-slate-400">User email</label>
                    <input
                        v-model="filters.user_email"
                        type="text"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                        placeholder="Search by email…"
                        autocomplete="off"
                        @keydown.enter="applyFilters"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Team</label>
                    <select
                        v-model="filters.team_id"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                    >
                        <option value="">All teams</option>
                        <option v-for="t in filterTeams" :key="t.id" :value="t.id">{{ t.label }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Class</label>
                    <select
                        v-model="filters.action_class"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                    >
                        <option value="">All classes</option>
                        <option v-for="c in filterActionClasses" :key="c" :value="c">{{ c }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">Action</label>
                    <select
                        v-model="filters.action_name"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!filters.action_class"
                    >
                        <option value="">All actions</option>
                        <option v-for="a in filterActions" :key="a" :value="a">{{ a }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">IP address</label>
                    <input
                        v-model="filters.ip_address"
                        type="text"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                        placeholder="Partial match"
                        @keydown.enter="applyFilters"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">From date</label>
                    <input
                        v-model="filters.date_from"
                        type="date"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400">To date</label>
                    <input
                        v-model="filters.date_to"
                        type="date"
                        class="mt-1 w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-body"
                    />
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
                    @click="filters = { user_email: '', team_id: '', action_class: '', action_name: '', ip_address: '', date_from: '', date_to: '' }; applyFilters()"
                >
                    Clear
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="mt-6 rounded-xl border border-slate-700 bg-slate-900/50 p-4">
            <div v-if="loading" class="py-8 text-center text-site-body">Loading…</div>
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-700">
                    <thead>
                        <tr>
                            <th class="py-2 text-left text-xs font-medium text-slate-400">Date</th>
                            <th class="py-2 text-left text-xs font-medium text-slate-400">Email</th>
                            <th class="py-2 text-left text-xs font-medium text-slate-400">Team</th>
                            <th class="py-2 text-left text-xs font-medium text-slate-400">Action</th>
                            <th class="py-2 text-left text-xs font-medium text-slate-400">IP</th>
                            <th class="py-2 text-left text-xs font-medium text-slate-400">Context</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <tr v-for="e in entries" :key="e.id">
                            <td class="py-2 text-sm text-site-body">{{ e.created_at }}</td>
                            <td class="py-2 text-sm text-site-body">{{ e.user_display }}</td>
                            <td class="py-2 text-sm text-site-body">{{ e.team_name }}</td>
                            <td class="py-2 text-sm text-site-body">{{ e.action || '—' }}</td>
                            <td class="py-2 text-sm text-site-body">{{ e.ip_address }}</td>
                            <td class="py-2 text-sm text-slate-400 max-w-xs truncate" :title="JSON.stringify(e.context)">
                                {{ e.context && Object.keys(e.context).length ? JSON.stringify(e.context) : '—' }}
                            </td>
                        </tr>
                        <tr v-if="entries.length === 0">
                            <td colspan="6" class="py-8 text-center text-sm text-slate-500">No entries found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="meta.total > 0" class="mt-4 flex items-center justify-between border-t border-slate-700 pt-4">
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
    </div>
</template>
