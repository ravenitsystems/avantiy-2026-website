<script setup>
import { ref, computed } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import axios from 'axios';
import { useSession } from '../composables/useSession';
import { useToast } from '../composables/useToast';

const router = useRouter();
const toast = useToast();
const { user, fetchSession } = useSession();
const name = ref('');
const description = ref('');
const loading = ref(false);

const canCreateTeam = computed(() => user.value?.canCreateTeam ?? false);
const atLimit = computed(() => {
    const limit = user.value?.teamLimit;
    const count = user.value?.teamCount ?? 0;
    return limit != null && count >= limit;
});

async function submit() {
    const trimmedName = name.value?.trim() ?? '';
    if (!trimmedName) {
        toast.error('Team name is required.');
        return;
    }
    if (!canCreateTeam.value) {
        toast.error('Your account cannot create teams.');
        return;
    }
    if (atLimit.value) {
        toast.error('You have reached your team limit.');
        return;
    }
    loading.value = true;
    try {
        await axios.post('/api/team/create', {
            name: trimmedName,
            description: description.value?.trim() || undefined,
        });
        await fetchSession();
        toast.success('Team created.');
        router.push({ name: 'dashboard-teams' });
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Could not create team.';
        toast.error(msg);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div>
        <h1 class="text-xl font-semibold text-site-heading">Create team</h1>
        <p class="mt-1 text-sm text-site-body">
            Create a new team. Only agency and enterprise accounts can create teams.
        </p>

        <div v-if="!canCreateTeam" class="mt-6 rounded-lg border border-slate-700 bg-slate-900/50 p-4 text-site-body">
            Your account type cannot create teams. Only agency and enterprise accounts can create teams.
        </div>

        <div v-else-if="atLimit" class="mt-6 rounded-lg border border-slate-700 bg-slate-900/50 p-4 text-site-body">
            You have reached your team limit. Agency accounts are limited to 3 teams.
        </div>

        <template v-else>
            <form class="mt-6 max-w-md space-y-4" @submit.prevent="submit">
                <div>
                    <label for="team-name" class="block text-sm font-medium text-site-body">Team name</label>
                    <input
                        id="team-name"
                        v-model="name"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading"
                        placeholder="My team"
                    />
                </div>
                <div>
                    <label for="team-description" class="block text-sm font-medium text-site-body">Description (optional)</label>
                    <textarea
                        id="team-description"
                        v-model="description"
                        rows="3"
                        class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading"
                        placeholder="Brief description of the team"
                    />
                </div>
                <div class="flex gap-3 pt-2">
                    <RouterLink
                        :to="{ name: 'dashboard-teams' }"
                        class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-site-body hover:bg-slate-800"
                    >
                        Cancel
                    </RouterLink>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white hover:bg-cta-hover disabled:opacity-60"
                    >
                        {{ loading ? 'Creating…' : 'Create team' }}
                    </button>
                </div>
            </form>
        </template>
    </div>
</template>
