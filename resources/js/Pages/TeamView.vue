<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useSession } from '../composables/useSession';
import { useToast } from '../composables/useToast';
import EmailInput from '../components/EmailInput.vue';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const { user, fetchSession, switchTeam } = useSession();

const teamId = computed(() => user.value?.activeTeamId ?? null);
const acceptingInvite = ref(false);
const team = ref(null);
const members = ref([]);
const messages = ref([]);
const invitations = ref([]);
const roles = ref([]);
const allPermissions = ref([]);
const loading = ref(true);
const messagesLoading = ref(false);
const messageBody = ref('');
const sendingMessage = ref(false);
const messagesContainer = ref(null);

// Invite form
const inviteEmail = ref('');
const inviteRoleId = ref(null);
const inviting = ref(false);

// Edit team
const editing = ref(false);
const editName = ref('');
const editDescription = ref('');
const savingEdit = ref(false);

// Member role change
const changingRoleMemberId = ref(null);
const changingRoleValue = ref(null);
const savingRoleChange = ref(false);

// Role editor
const savingRole = ref(false);

// Delete role modal
const deleteRoleModalOpen = ref(false);
const deleteRoleId = ref(null);
const deleteRoleName = ref('');
const deletingRole = ref(false);

// Delete team modal
const deleteTeamModalOpen = ref(false);
const deleteTeamConfirmText = ref('');
const deletingTeam = ref(false);
const DELETE_TEAM_CONFIRM_PHRASE = 'I really want to do this';
const canConfirmDeleteTeam = computed(() => deleteTeamConfirmText.value.trim() === DELETE_TEAM_CONFIRM_PHRASE);

// Role wizard (modal) – used for both create and edit
const roleWizardOpen = ref(false);
const roleWizardStep = ref(0);
const wizardRoleName = ref('');
const wizardRoleDescription = ref('');
const wizardRolePermissions = ref([]);
const wizardIsEditMode = ref(false);
const wizardEditingRoleId = ref(null);

const isOwner = computed(() => team.value?.is_owner ?? false);
const myPermissions = computed(() => team.value?.permissions ?? []);
const canInvite = computed(() => isOwner.value || myPermissions.value.includes('team.invite_user'));
const canRemove = computed(() => isOwner.value || myPermissions.value.includes('team.remove_user'));
const canChangeRoles = computed(() => isOwner.value || myPermissions.value.includes('team.change_user_roles'));
const canEditRoles = computed(() => isOwner.value || myPermissions.value.includes('team.edit_roles'));
const canViewMessages = computed(() => isOwner.value || myPermissions.value.includes('messages.view'));
const canSendMessages = computed(() => isOwner.value || myPermissions.value.includes('messages.send'));

const assignableRoles = computed(() => roles.value.filter(r => !r.is_owner));

const activeTab = ref('conversation');

// Create role wizard
const wizardTotalSteps = computed(() => 1 + (allPermissions.value?.length ?? 0));
const wizardStep0 = computed(() => roleWizardStep.value === 0);
const wizardCurrentGroup = computed(() => {
    const idx = roleWizardStep.value - 1;
    return allPermissions.value?.[idx] ?? null;
});
const wizardIsLastStep = computed(() => roleWizardStep.value === wizardTotalSteps.value - 1);

async function fetchTeam() {
    try {
        const { data } = await axios.get(`/api/team/get/${teamId.value}`);
        const payload = data?.data ?? data;
        team.value = payload;
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? 'Failed to load team.';
        toast.error(msg);
        if (err.response?.status === 403 || err.response?.status === 404) {
            router.push({ name: 'dashboard-home' });
        }
    }
}

async function fetchMembers() {
    try {
        const { data } = await axios.get(`/api/team/members/${teamId.value}`);
        const payload = data?.data ?? data;
        members.value = payload?.members ?? [];
    } catch {
        members.value = [];
    }
}

async function fetchMessages() {
    messagesLoading.value = true;
    try {
        const { data } = await axios.get(`/api/team/messages/${teamId.value}`);
        const payload = data?.data ?? data;
        messages.value = (payload?.messages ?? []).reverse();
        await nextTick();
        scrollToBottom();
    } catch {
        messages.value = [];
    } finally {
        messagesLoading.value = false;
    }
}

async function fetchInvitations() {
    try {
        const { data } = await axios.get(`/api/team/invitations/${teamId.value}`);
        const payload = data?.data ?? data;
        invitations.value = payload?.invitations ?? [];
    } catch {
        invitations.value = [];
    }
}

async function fetchRoles() {
    try {
        const { data } = await axios.get(`/api/team/roles/${teamId.value}`);
        const payload = data?.data ?? data;
        roles.value = payload?.roles ?? [];
    } catch {
        roles.value = [];
    }
}

async function fetchPermissionsList() {
    try {
        const { data } = await axios.get('/api/team/permissions');
        const payload = data?.data ?? data;
        allPermissions.value = payload?.permissions ?? [];
    } catch {
        allPermissions.value = [];
    }
}

function scrollToBottom() {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
}

async function sendMessage() {
    const body = messageBody.value.trim();
    if (!body) return;
    sendingMessage.value = true;
    try {
        const { data } = await axios.post(`/api/team/sendmessage/${teamId.value}`, { body });
        const payload = data?.data ?? data;
        messages.value.push(payload);
        messageBody.value = '';
        await nextTick();
        scrollToBottom();
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? 'Failed to send message.';
        toast.error(msg);
    } finally {
        sendingMessage.value = false;
    }
}

function handleMessageKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

// Edit team
function startEdit() {
    editName.value = team.value?.name ?? '';
    editDescription.value = team.value?.description ?? '';
    editing.value = true;
}

function cancelEdit() {
    editing.value = false;
}

async function saveEdit() {
    const name = editName.value.trim();
    if (!name) {
        toast.error('Team name is required.');
        return;
    }
    savingEdit.value = true;
    try {
        await axios.post(`/api/team/update/${teamId.value}`, {
            name,
            description: editDescription.value.trim() || undefined,
        });
        toast.success('Team updated.');
        editing.value = false;
        await fetchTeam();
        await fetchSession();
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? 'Failed to update team.';
        toast.error(msg);
    } finally {
        savingEdit.value = false;
    }
}

function openDeleteTeamModal() {
    deleteTeamConfirmText.value = '';
    deleteTeamModalOpen.value = true;
}

function closeDeleteTeamModal() {
    deleteTeamModalOpen.value = false;
    deleteTeamConfirmText.value = '';
}

async function confirmDeleteTeam() {
    if (!canConfirmDeleteTeam.value) return;
    deletingTeam.value = true;
    try {
        await axios.post(`/api/team/delete/${teamId.value}`);
        toast.success('Team deleted.');
        closeDeleteTeamModal();
        await fetchSession();
        router.push({ name: 'dashboard-home' });
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? 'Failed to delete team.';
        toast.error(msg);
    } finally {
        deletingTeam.value = false;
    }
}

// Members
async function removeMember(memberId) {
    if (!confirm('Remove this member from the team?')) return;
    try {
        await axios.post(`/api/team/removemember/${teamId.value}`, { team_member_id: memberId });
        toast.success('Member removed.');
        await Promise.all([fetchMembers(), fetchRoles()]);
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? 'Failed to remove member.';
        toast.error(msg);
    }
}

function startChangeRole(member) {
    changingRoleMemberId.value = member.id;
    changingRoleValue.value = member.role_id;
}

function cancelChangeRole() {
    changingRoleMemberId.value = null;
    changingRoleValue.value = null;
}

async function saveChangeRole() {
    savingRoleChange.value = true;
    try {
        await axios.post(`/api/team/changememberrole/${teamId.value}`, {
            team_member_id: changingRoleMemberId.value,
            team_role_id: changingRoleValue.value,
        });
        toast.success('Role updated.');
        changingRoleMemberId.value = null;
        await fetchMembers();
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? 'Failed to change role.';
        toast.error(msg);
    } finally {
        savingRoleChange.value = false;
    }
}

// Roles CRUD
function startCreateRole() {
    wizardIsEditMode.value = false;
    wizardEditingRoleId.value = null;
    roleWizardOpen.value = true;
    roleWizardStep.value = 0;
    wizardRoleName.value = '';
    wizardRoleDescription.value = '';
    wizardRolePermissions.value = [];
}

function startEditRole(role) {
    wizardIsEditMode.value = true;
    wizardEditingRoleId.value = role.id;
    roleWizardOpen.value = true;
    roleWizardStep.value = 0;
    wizardRoleName.value = role.name ?? '';
    wizardRoleDescription.value = role.description ?? '';
    wizardRolePermissions.value = [...(role.permissions ?? [])];
}

function closeRoleWizard() {
    roleWizardOpen.value = false;
    roleWizardStep.value = 0;
    wizardIsEditMode.value = false;
    wizardEditingRoleId.value = null;
}

function wizardNext() {
    if (wizardStep0.value) {
        if (!wizardRoleName.value.trim()) {
            toast.error('Role name is required.');
            return;
        }
    }
    if (roleWizardStep.value < wizardTotalSteps.value - 1) {
        roleWizardStep.value++;
    }
}

function wizardBack() {
    if (roleWizardStep.value > 0) {
        roleWizardStep.value--;
    }
}

function wizardTogglePermission(key) {
    const idx = wizardRolePermissions.value.indexOf(key);
    if (idx >= 0) {
        wizardRolePermissions.value.splice(idx, 1);
    } else {
        wizardRolePermissions.value.push(key);
    }
}

async function saveRoleFromWizard() {
    const name = wizardRoleName.value.trim();
    if (!name) {
        toast.error('Role name is required.');
        return;
    }
    savingRole.value = true;
    try {
        if (wizardIsEditMode.value) {
            await axios.post(`/api/team/updaterole/${teamId.value}`, {
                role_id: wizardEditingRoleId.value,
                name,
                description: wizardRoleDescription.value.trim() || undefined,
                permissions: wizardRolePermissions.value,
            });
            toast.success('Role updated.');
        } else {
            await axios.post(`/api/team/createrole/${teamId.value}`, {
                name,
                description: wizardRoleDescription.value.trim() || undefined,
                permissions: wizardRolePermissions.value,
            });
            toast.success('Role created.');
        }
        closeRoleWizard();
        await fetchRoles();
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? 'Failed to save role.';
        toast.error(msg);
    } finally {
        savingRole.value = false;
    }
}


function openDeleteRoleModal(role) {
    deleteRoleId.value = role.id;
    deleteRoleName.value = role.name ?? 'this role';
    deleteRoleModalOpen.value = true;
}

function closeDeleteRoleModal() {
    deleteRoleModalOpen.value = false;
    deleteRoleId.value = null;
    deleteRoleName.value = '';
}

async function confirmDeleteRole() {
    if (!deleteRoleId.value) return;
    deletingRole.value = true;
    try {
        await axios.post(`/api/team/deleterole/${teamId.value}`, { role_id: deleteRoleId.value });
        toast.success('Role deleted.');
        closeDeleteRoleModal();
        await fetchRoles();
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? 'Failed to delete role.';
        toast.error(msg);
    } finally {
        deletingRole.value = false;
    }
}

// Invite
async function sendInvite() {
    const email = inviteEmail.value.trim();
    if (!email) {
        toast.error('Email is required.');
        return;
    }
    if (!inviteRoleId.value) {
        toast.error('Please select a role.');
        return;
    }
    inviting.value = true;
    try {
        await axios.post(`/api/team/invite/${teamId.value}`, {
            email,
            team_role_id: inviteRoleId.value,
        });
        toast.success('Invitation sent.');
        inviteEmail.value = '';
        inviteRoleId.value = null;
        await fetchInvitations();
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? 'Failed to send invitation.';
        toast.error(msg);
    } finally {
        inviting.value = false;
    }
}

async function cancelInvitation(invitationId) {
    try {
        await axios.post(`/api/team/cancelinvite/${teamId.value}`, { invitation_id: invitationId });
        toast.success('Invitation cancelled.');
        await fetchInvitations();
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? 'Failed to cancel invitation.';
        toast.error(msg);
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const now = new Date();
    const sameDay = d.toDateString() === now.toDateString();
    if (sameDay) return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    return d.toLocaleDateString(undefined, { dateStyle: 'short' }) + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

async function loadTeamData() {
    if (!teamId.value) return;
    loading.value = true;
    team.value = null;
    members.value = [];
    messages.value = [];
    invitations.value = [];
    roles.value = [];
    editing.value = false;
    changingRoleMemberId.value = null;
    closeRoleWizard();
    closeDeleteRoleModal();
    closeDeleteTeamModal();
    activeTab.value = 'conversation';

    await Promise.all([fetchTeam(), fetchPermissionsList(), fetchRoles()]);
    if (team.value) {
        const fetches = [fetchMembers()];
        if (canViewMessages.value) fetches.push(fetchMessages());
        if (canInvite.value) fetches.push(fetchInvitations());
        await Promise.all(fetches);
    }
    loading.value = false;
}

async function handleAcceptToken() {
    const token = route.query.accept_token;
    if (!token) return false;

    acceptingInvite.value = true;
    try {
        const { data } = await axios.post('/api/team/acceptinvite', { token });
        const payload = data?.data ?? data;
        toast.success('Invitation accepted.');
        if (payload?.team_id) {
            await switchTeam(payload.team_id);
        } else {
            await fetchSession();
        }
        router.replace({ name: 'dashboard-teams' });
        return true;
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? 'Failed to accept invitation.';
        toast.error(msg);
        if (!teamId.value) {
            router.push({ name: 'dashboard-home' });
        }
        return false;
    } finally {
        acceptingInvite.value = false;
    }
}

onMounted(async () => {
    const handled = await handleAcceptToken();
    if (!handled) {
        if (!teamId.value) {
            router.push({ name: 'dashboard-home' });
            return;
        }
        await loadTeamData();
    }
});

watch(teamId, (newId, oldId) => {
    if (newId && newId !== oldId) {
        loadTeamData();
    }
    if (!newId) {
        router.push({ name: 'dashboard-home' });
    }
});
</script>

<template>
    <div v-if="acceptingInvite" class="py-8 text-center text-site-body">Accepting invitation...</div>

    <div v-else-if="loading" class="py-8 text-center text-site-body">Loading...</div>

    <div v-else-if="!team" class="py-8 text-center text-site-body">Team not found.</div>

    <div v-else>
        <!-- Header -->
        <div class="mb-6">
            <template v-if="!editing">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-semibold text-site-heading">{{ team.name }}</h1>
                        <p v-if="team.description" class="mt-1 text-sm text-site-body">{{ team.description }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ team.member_count }} member{{ team.member_count !== 1 ? 's' : '' }}
                            <span class="ml-2 rounded bg-cta/20 px-1.5 py-0.5 text-cta">{{ team.role_name }}</span>
                        </p>
                    </div>
                    <div v-if="isOwner" class="flex gap-2">
                        <button type="button" class="rounded-lg border border-slate-600 px-3 py-1.5 text-sm text-site-body hover:bg-slate-800" @click="startEdit">Edit</button>
                        <button type="button" class="rounded-lg border border-red-800 px-3 py-1.5 text-sm text-red-400 hover:bg-red-900/30" @click="openDeleteTeamModal">Delete</button>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="max-w-md space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-site-body">Team name</label>
                        <input v-model="editName" type="text" class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-site-body">Description</label>
                        <textarea v-model="editDescription" rows="2" class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading" />
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="rounded-lg border border-slate-600 px-3 py-1.5 text-sm text-site-body hover:bg-slate-800" @click="cancelEdit">Cancel</button>
                        <button type="button" :disabled="savingEdit" class="rounded-lg bg-cta px-3 py-1.5 text-sm font-medium text-white hover:bg-cta-hover disabled:opacity-60" @click="saveEdit">
                            {{ savingEdit ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Tabs -->
        <div class="mb-4 flex gap-1 border-b border-slate-800">
            <button type="button" class="px-4 py-2 text-sm font-medium transition-colors" :class="activeTab === 'conversation' ? 'border-b-2 border-cta text-cta' : 'text-site-body hover:text-site-heading'" @click="activeTab = 'conversation'">Conversation</button>
            <button type="button" class="px-4 py-2 text-sm font-medium transition-colors" :class="activeTab === 'members' ? 'border-b-2 border-cta text-cta' : 'text-site-body hover:text-site-heading'" @click="activeTab = 'members'">Members</button>
            <button v-if="canEditRoles" type="button" class="px-4 py-2 text-sm font-medium transition-colors" :class="activeTab === 'roles' ? 'border-b-2 border-cta text-cta' : 'text-site-body hover:text-site-heading'" @click="activeTab = 'roles'">Roles</button>
            <button v-if="canInvite" type="button" class="px-4 py-2 text-sm font-medium transition-colors" :class="activeTab === 'invite' ? 'border-b-2 border-cta text-cta' : 'text-site-body hover:text-site-heading'" @click="activeTab = 'invite'">Invite</button>
        </div>

        <!-- Conversation Tab -->
        <div v-show="activeTab === 'conversation'">
            <div v-if="!canViewMessages" class="rounded-lg border border-slate-700 bg-slate-900/50 p-4 text-sm text-site-body">
                You do not have permission to view the team conversation.
            </div>
            <template v-else>
                <div ref="messagesContainer" class="mb-3 h-[400px] overflow-y-auto rounded-lg border border-slate-800 bg-slate-950 p-4">
                    <div v-if="messagesLoading" class="py-8 text-center text-sm text-site-body">Loading messages...</div>
                    <div v-else-if="messages.length === 0" class="py-8 text-center text-sm text-site-body">No messages yet. Start the conversation.</div>
                    <div v-else class="space-y-3">
                        <div v-for="msg in messages" :key="msg.id" class="group">
                            <div class="flex items-baseline gap-2">
                                <span class="text-sm font-medium text-cta">{{ msg.first_name || 'User' }} {{ msg.last_name || '' }}</span>
                                <span class="text-[0.65rem] text-slate-500">{{ formatDate(msg.created_at) }}</span>
                            </div>
                            <p class="mt-0.5 whitespace-pre-wrap text-sm text-site-body">{{ msg.body }}</p>
                        </div>
                    </div>
                </div>
                <div v-if="canSendMessages" class="flex gap-2">
                    <textarea v-model="messageBody" rows="2" class="flex-1 rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-heading placeholder-slate-500 resize-none" placeholder="Type a message... (Enter to send)" @keydown="handleMessageKeydown" />
                    <button type="button" :disabled="sendingMessage || !messageBody.trim()" class="self-end rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white hover:bg-cta-hover disabled:opacity-60" @click="sendMessage">Send</button>
                </div>
            </template>
        </div>

        <!-- Members Tab -->
        <div v-show="activeTab === 'members'">
            <div class="space-y-3">
                <div v-for="m in members" :key="m.id" class="rounded-lg border border-slate-800 bg-slate-900/50 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-medium text-site-heading">{{ m.first_name || m.email }} {{ m.last_name || '' }}</p>
                            <p class="text-xs text-site-body">{{ m.email }}</p>
                            <p class="mt-1 text-xs text-slate-400">
                                <span class="rounded bg-slate-800 px-1.5 py-0.5">{{ m.role_name }}</span>
                            </p>
                        </div>
                        <div v-if="!m.is_owner" class="flex gap-2">
                            <button v-if="canChangeRoles" type="button" class="rounded border border-slate-600 px-2 py-1 text-xs text-site-body hover:bg-slate-800" @click="startChangeRole(m)">Change role</button>
                            <button v-if="canRemove" type="button" class="rounded border border-red-800 px-2 py-1 text-xs text-red-400 hover:bg-red-900/30" @click="removeMember(m.id)">Remove</button>
                        </div>
                    </div>
                    <div v-if="changingRoleMemberId === m.id" class="mt-3 flex items-center gap-2">
                        <select v-model="changingRoleValue" class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-1.5 text-sm text-site-heading">
                            <option v-for="r in assignableRoles" :key="r.id" :value="r.id">{{ r.name }}</option>
                        </select>
                        <button type="button" class="rounded border border-slate-600 px-2 py-1 text-xs text-site-body hover:bg-slate-800" @click="cancelChangeRole">Cancel</button>
                        <button type="button" :disabled="savingRoleChange" class="rounded bg-cta px-2 py-1 text-xs font-medium text-white hover:bg-cta-hover disabled:opacity-60" @click="saveChangeRole">
                            {{ savingRoleChange ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
            <p v-if="members.length === 0" class="mt-4 text-sm text-site-body">No members.</p>
        </div>

        <!-- Roles Tab -->
        <div v-show="activeTab === 'roles'">
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-site-body">Define roles and their permissions for this team.</p>
                <button v-if="canEditRoles" type="button" class="rounded-lg bg-cta px-3 py-1.5 text-sm font-medium text-white hover:bg-cta-hover" @click="startCreateRole">Create role</button>
            </div>

            <!-- Role list -->
            <div class="space-y-3">
                <div v-for="r in roles" :key="r.id" class="rounded-lg border border-slate-800 bg-slate-900/50 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <h4 class="text-sm font-medium text-site-heading">
                                {{ r.name }}
                                <span v-if="r.is_owner" class="ml-1 text-xs text-cta">(all permissions)</span>
                                <span v-if="r.is_preset" class="ml-1 text-xs text-slate-500">preset</span>
                            </h4>
                            <p v-if="r.description" class="mt-0.5 text-xs text-site-body">{{ r.description }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ r.member_count }} member{{ r.member_count !== 1 ? 's' : '' }}
                                <template v-if="!r.is_owner"> &middot; {{ r.permissions.length }} permission{{ r.permissions.length !== 1 ? 's' : '' }}</template>
                            </p>
                        </div>
                        <div v-if="canEditRoles && !r.is_owner && !r.is_global" class="flex gap-2">
                            <button type="button" class="rounded border border-slate-600 px-2 py-1 text-xs text-site-body hover:bg-slate-800" @click="startEditRole(r)">Edit</button>
                            <button v-if="r.member_count === 0" type="button" class="rounded border border-red-800 px-2 py-1 text-xs text-red-400 hover:bg-red-900/30" @click="openDeleteRoleModal(r)">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
            <p v-if="roles.length === 0" class="mt-4 text-sm text-site-body">No roles defined.</p>
        </div>

        <!-- Invite Tab -->
        <div v-show="activeTab === 'invite'">
            <div class="max-w-lg space-y-4">
                <EmailInput
                    id="invite-email"
                    label="Email address"
                    v-model="inviteEmail"
                    placeholder="user@example.com"
                />
                <div>
                    <label class="block text-sm font-medium text-site-body">Role</label>
                    <select v-model="inviteRoleId" class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading">
                        <option :value="null" disabled>Select a role...</option>
                        <option v-for="r in assignableRoles" :key="r.id" :value="r.id">{{ r.name }}</option>
                    </select>
                </div>
                <button type="button" :disabled="inviting" class="rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white hover:bg-cta-hover disabled:opacity-60" @click="sendInvite">
                    {{ inviting ? 'Sending...' : 'Send invitation' }}
                </button>
            </div>

            <div v-if="invitations.length" class="mt-6">
                <h3 class="mb-3 text-sm font-semibold text-site-heading">Pending invitations</h3>
                <div class="space-y-2">
                    <div v-for="inv in invitations" :key="inv.id" class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-900/50 px-4 py-3">
                        <div>
                            <p class="text-sm text-site-heading">{{ inv.email }}</p>
                            <p class="text-xs text-slate-400">
                                Role: {{ inv.role_name || 'Unknown' }} &middot; Expires {{ formatDate(inv.expires_at) }}
                            </p>
                        </div>
                        <button type="button" class="rounded border border-red-800 px-2 py-1 text-xs text-red-400 hover:bg-red-900/30" @click="cancelInvitation(inv.id)">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create role wizard modal -->
        <div
            v-show="roleWizardOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="role-wizard-title"
        >
            <div class="relative w-full max-w-lg rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <!-- Yellow warning in corner when group has high-impact -->
                <div
                    v-if="!wizardStep0 && wizardCurrentGroup?.group_is_high_impact"
                    class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/90 text-xl font-bold text-amber-950"
                    title="This group contains high-impact permissions"
                >
                    !
                </div>

                <h2 id="role-wizard-title" class="pr-10 text-lg font-semibold text-site-heading">
                    {{ wizardIsEditMode ? 'Edit role' : 'Create role' }} – Step {{ roleWizardStep + 1 }} of {{ wizardTotalSteps }}
                </h2>

                <!-- Step 0: Name and description -->
                <div v-if="wizardStep0" class="mt-4 space-y-4">
                    <div>
                        <label for="wizard-role-name" class="block text-sm font-medium text-site-body">Role name</label>
                        <input
                            id="wizard-role-name"
                            v-model="wizardRoleName"
                            type="text"
                            class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading"
                            placeholder="e.g. Designer"
                        />
                    </div>
                    <div>
                        <label for="wizard-role-desc" class="block text-sm font-medium text-site-body">Description</label>
                        <input
                            id="wizard-role-desc"
                            v-model="wizardRoleDescription"
                            type="text"
                            class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading"
                            placeholder="Brief description"
                        />
                    </div>
                </div>

                <!-- Step 1+: Permission group -->
                <div v-else-if="wizardCurrentGroup" class="mt-4">
                    <h3 class="mb-2 text-sm font-medium text-site-heading">{{ wizardCurrentGroup.group }}</h3>
                    <p v-if="wizardCurrentGroup.group_description" class="mb-4 text-sm text-slate-400">
                        {{ wizardCurrentGroup.group_description }}
                    </p>
                    <div class="space-y-4">
                        <label
                            v-for="p in wizardCurrentGroup.permissions"
                            :key="p.key"
                            class="flex min-h-[4rem] cursor-pointer items-center gap-3 rounded-lg border border-slate-700 bg-slate-800/50 p-3 transition-colors hover:border-slate-600 hover:bg-slate-800"
                        >
                            <input
                                type="checkbox"
                                :checked="wizardRolePermissions.includes(p.key)"
                                class="size-4 shrink-0 rounded border-slate-600 bg-slate-800 accent-cta focus:ring-2 focus:ring-cta/30 focus:ring-offset-0 focus:ring-offset-slate-900"
                                @change="wizardTogglePermission(p.key)"
                            />
                            <div class="min-w-0 flex-1">
                                <span
                                    class="text-sm font-medium"
                                    :class="p.is_high_impact ? 'text-red-400' : 'text-site-heading'"
                                >{{ p.label }}</span>
                                <p v-if="p.description" class="mt-1 text-xs text-slate-400">
                                    {{ p.description }}
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-between">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-site-body hover:bg-slate-800"
                        @click="roleWizardStep === 0 ? closeRoleWizard() : wizardBack()"
                    >
                        {{ roleWizardStep === 0 ? 'Cancel' : 'Back' }}
                    </button>
                    <button
                        v-if="wizardIsLastStep"
                        type="button"
                        :disabled="savingRole"
                        class="rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white hover:bg-cta-hover disabled:opacity-60"
                        @click="saveRoleFromWizard"
                    >
                        {{ savingRole ? (wizardIsEditMode ? 'Saving…' : 'Creating…') : (wizardIsEditMode ? 'Save changes' : 'Create role') }}
                    </button>
                    <button
                        v-else
                        type="button"
                        class="rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white hover:bg-cta-hover"
                        @click="wizardNext"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete team confirmation modal -->
        <div
            v-show="deleteTeamModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-team-title"
        >
            <div class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h2 id="delete-team-title" class="text-lg font-semibold text-site-heading">Delete team</h2>
                <p class="mt-2 text-sm text-site-body">
                    Deleting <strong class="text-site-heading">{{ team?.name }}</strong> is permanent and cannot be undone. All members, roles, and data will be removed.
                </p>
                <p class="mt-4 text-sm text-site-body">
                    Type <strong class="text-amber-400">{{ DELETE_TEAM_CONFIRM_PHRASE }}</strong> below to confirm:
                </p>
                <input
                    v-model="deleteTeamConfirmText"
                    type="text"
                    :placeholder="DELETE_TEAM_CONFIRM_PHRASE"
                    class="mt-2 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500"
                    autocomplete="off"
                />
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-site-body hover:bg-slate-800"
                        @click="closeDeleteTeamModal"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        :disabled="!canConfirmDeleteTeam || deletingTeam"
                        class="rounded-lg border border-red-800 bg-red-900/30 px-4 py-2 text-sm font-medium text-red-400 hover:bg-red-900/50 disabled:opacity-60 disabled:cursor-not-allowed"
                        @click="confirmDeleteTeam"
                    >
                        {{ deletingTeam ? 'Deleting…' : 'Delete team' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete role confirmation modal -->
        <div
            v-show="deleteRoleModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-role-title"
        >
            <div class="w-full max-w-md rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h2 id="delete-role-title" class="text-lg font-semibold text-site-heading">Delete role</h2>
                <p class="mt-2 text-sm text-site-body">
                    Are you sure you want to delete <strong class="text-site-heading">{{ deleteRoleName }}</strong>? This cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-site-body hover:bg-slate-800"
                        @click="closeDeleteRoleModal"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        :disabled="deletingRole"
                        class="rounded-lg border border-red-800 bg-red-900/30 px-4 py-2 text-sm font-medium text-red-400 hover:bg-red-900/50 disabled:opacity-60"
                        @click="confirmDeleteRole"
                    >
                        {{ deletingRole ? 'Deleting…' : 'Delete role' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
