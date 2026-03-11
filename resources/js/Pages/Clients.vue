<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { useSession } from '../composables/useSession';
import { useToast } from '../composables/useToast';
import EmailInput from '../components/EmailInput.vue';

const toast = useToast();
const { user, fetchSession } = useSession();
const clients = ref([]);
const loading = ref(false);
const createFormOpen = ref(false);
const createLoading = ref(false);
const createName = ref('');
const createEmail = ref('');
const createPermissions = ref([]);

const ALLOWED_PERMISSIONS = [
    { key: 'asset.client_dummy.view', label: 'View (dummy)' },
    { key: 'asset.client_dummy.edit', label: 'Edit (dummy)' },
];

const canAdd = computed(() => user.value?.canAddClientAssociation ?? false);
const clientLimit = computed(() => user.value?.clientLimit ?? null);
const clientCount = computed(() => user.value?.clientCountPersonal ?? 0);
const limitLabel = computed(() => {
    if (clientLimit.value == null) return `${clientCount.value} clients`;
    return `${clientCount.value} of ${clientLimit.value} clients`;
});
const atLimit = computed(() => clientLimit.value != null && clientCount.value >= clientLimit.value);

const activeTeamId = computed(() => user.value?.activeTeamId ?? null);
const scopeType = computed(() => activeTeamId.value ? 'team' : 'user');

async function fetchClients() {
    loading.value = true;
    try {
        const params = { scope_type: scopeType.value };
        if (scopeType.value === 'team') {
            params.scope_team_id = activeTeamId.value;
        }
        const { data } = await axios.get('/api/client/index', { params });
        const payload = data?.data ?? data;
        clients.value = payload?.clients ?? [];
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Failed to load clients.';
        toast.error(msg);
        clients.value = [];
    } finally {
        loading.value = false;
    }
}

watch(activeTeamId, () => {
    if (canAdd.value) fetchClients();
});

function openCreateForm() {
    createFormOpen.value = true;
    createName.value = '';
    createEmail.value = '';
    createPermissions.value = [];
}

function closeCreateForm() {
    createFormOpen.value = false;
}

function togglePermission(key) {
    const i = createPermissions.value.indexOf(key);
    if (i === -1) createPermissions.value = [...createPermissions.value, key];
    else createPermissions.value = createPermissions.value.filter((p) => p !== key);
}

async function submitCreate() {
    const name = createName.value?.trim();
    const email = createEmail.value?.trim();
    if (!name || !email) {
        toast.error('Name and email are required.');
        return;
    }
    createLoading.value = true;
    try {
        const payload = {
            name,
            email,
            scope_type: scopeType.value,
            permissions: createPermissions.value,
        };
        if (scopeType.value === 'team') {
            payload.scope_team_id = activeTeamId.value;
        }
        await axios.post('/api/client/create', payload);
        closeCreateForm();
        await fetchSession();
        await fetchClients();
        toast.success('Client added.');
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Could not add client.';
        toast.error(msg);
    } finally {
        createLoading.value = false;
    }
}

async function removeClient(associationId) {
    if (!confirm('Remove this client from your list?')) return;
    try {
        await axios.delete(`/api/client/delete/${associationId}`);
        await fetchSession();
        await fetchClients();
        toast.success('Client removed.');
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Could not remove client.';
        toast.error(msg);
    }
}

onMounted(() => {
    if (canAdd.value) fetchClients();
});
</script>

<template>
    <div>
        <h1 class="text-xl font-semibold text-site-heading">Clients</h1>
        <p class="mt-1 text-sm text-site-body">
            Manage client access to your assets. You can grant limited permissions per client.
        </p>

        <div v-if="!canAdd" class="mt-6 rounded-lg border border-slate-700 bg-slate-900/50 p-4 text-site-body">
            Your account type does not allow adding clients. Only contractor, agency, and enterprise accounts can manage clients.
        </div>

        <template v-else>
            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                <span class="text-sm text-site-body">{{ limitLabel }}</span>
                <button
                    type="button"
                    class="rounded-lg bg-cta px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-cta-hover disabled:opacity-60"
                    :disabled="atLimit"
                    @click="openCreateForm"
                >
                    Add client
                </button>
            </div>

            <div v-if="atLimit" class="mt-2 text-sm text-amber-400">
                You have reached your client limit. Remove a client to add another.
            </div>

            <div v-if="loading" class="py-8 text-center text-site-body">Loading…</div>
            <div v-else class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="c in clients"
                    :key="c.association_id"
                    class="rounded-lg border border-slate-800 bg-slate-900/50 p-4 transition-colors hover:border-slate-700"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="font-medium text-site-heading">{{ c.name }}</h3>
                            <p class="mt-0.5 text-sm text-site-body">{{ c.email }}</p>
                            <p v-if="c.permissions && c.permissions.length" class="mt-2 text-xs text-site-body">
                                Permissions: {{ c.permissions.join(', ') }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="shrink-0 rounded p-1.5 text-site-body hover:bg-slate-800 hover:text-red-400"
                            aria-label="Remove client"
                            @click="removeClient(c.association_id)"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </article>
            </div>
            <p v-if="!loading && clients.length === 0" class="mt-6 text-site-body">
                No clients yet. Add a client to grant them limited access to your assets.
            </p>
        </template>

        <!-- Add client modal -->
        <div
            v-show="createFormOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="create-client-title"
        >
            <div class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h2 id="create-client-title" class="text-lg font-semibold text-site-heading">Add client</h2>
                <p class="mt-1 text-sm text-site-body">The client will have limited access to your assets based on the permissions you set.</p>
                <form class="mt-4 space-y-4" @submit.prevent="submitCreate">
                    <div>
                        <label for="create-client-name" class="block text-sm font-medium text-site-body">Name</label>
                        <input
                            id="create-client-name"
                            v-model="createName"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading"
                            placeholder="Client name"
                        />
                    </div>
                    <div>
                        <EmailInput
                            id="create-client-email"
                            label="Email"
                            v-model="createEmail"
                            placeholder="client@example.com"
                            required
                        />
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-site-body">Permissions</span>
                        <div class="mt-2 space-y-2">
                            <label
                                v-for="perm in ALLOWED_PERMISSIONS"
                                :key="perm.key"
                                class="flex items-center gap-2"
                            >
                                <input
                                    type="checkbox"
                                    :checked="createPermissions.includes(perm.key)"
                                    class="rounded border-slate-600"
                                    @change="togglePermission(perm.key)"
                                />
                                <span class="text-sm text-site-body">{{ perm.label }}</span>
                            </label>
                        </div>
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
                            {{ createLoading ? 'Adding…' : 'Add client' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
