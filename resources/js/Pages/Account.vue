<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useSession } from '../composables/useSession';
import { useToast } from '../composables/useToast';

const { user, fetchSession } = useSession();
const toast = useToast();

const currentPassword = ref('');
const newPassword = ref('');
const newPasswordConfirmation = ref('');
const passwordFieldFocused = ref(false);
const passwordLoading = ref(false);

const twoFactorEnabled = computed(() => user.value?.twoFactorEnabled ?? false);
const twoFactorSetupQr = ref('');
const twoFactorSetupSecret = ref('');
const twoFactorCode = ref('');
const twoFactorLoading = ref(false);
const twoFactorDisablePassword = ref('');
const twoFactorDisableLoading = ref(false);

const marketingOptIn = ref(false);
const marketingLoading = ref(false);

const passwordsMatch = computed(() =>
    newPassword.value === newPasswordConfirmation.value || !newPasswordConfirmation.value
);

function computePasswordStrength(pw) {
    if (!pw) return { level: 0, label: '', criteria: [] };
    const criteria = {
        length: pw.length >= 8,
        lowercase: /[a-z]/.test(pw),
        uppercase: /[A-Z]/.test(pw),
        number: /\d/.test(pw),
        special: /[^a-zA-Z\d]/.test(pw),
    };
    const met = Object.values(criteria).filter(Boolean).length;
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    return { level: met, label: labels[met], criteria };
}

const passwordStrength = computed(() => computePasswordStrength(newPassword.value));
const passwordMeetsPolicy = computed(() => {
    const { criteria } = passwordStrength.value;
    return criteria && criteria.length && criteria.lowercase && criteria.uppercase && criteria.number && criteria.special;
});
const changePasswordValid = computed(() =>
    currentPassword.value && newPassword.value && passwordsMatch.value && passwordMeetsPolicy.value
);

onMounted(() => {
    marketingOptIn.value = user.value?.marketing ?? false;
});

async function onChangePassword() {
    passwordLoading.value = true;
    try {
        const { data } = await axios.post('/api/session/changepassword', {
            current_password: currentPassword.value,
            new_password: newPassword.value,
        });
        if (data.status === 'FAIL') {
            toast.error(data.data?.message ?? 'Failed to update password.');
            return;
        }
        toast.success(data.data?.message ?? 'Password updated.');
        currentPassword.value = '';
        newPassword.value = '';
        newPasswordConfirmation.value = '';
    } catch (err) {
        toast.error(err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Something went wrong.');
    } finally {
        passwordLoading.value = false;
    }
}

async function startTwoFactorSetup() {
    twoFactorSetupQr.value = '';
    twoFactorSetupSecret.value = '';
    twoFactorCode.value = '';
    twoFactorLoading.value = true;
    try {
        const { data } = await axios.get('/api/session/twofasetup');
        if (data.status === 'FAIL') {
            toast.error(data.data?.message ?? 'Failed to start setup.');
            return;
        }
        twoFactorSetupQr.value = data.data.qr_code_url ?? '';
        twoFactorSetupSecret.value = data.data.secret ?? '';
    } catch (err) {
        toast.error(err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Something went wrong.');
    } finally {
        twoFactorLoading.value = false;
    }
}

function cancelTwoFactorSetup() {
    twoFactorSetupQr.value = '';
    twoFactorSetupSecret.value = '';
    twoFactorCode.value = '';
}

async function confirmTwoFactor() {
    twoFactorLoading.value = true;
    try {
        const { data } = await axios.post('/api/session/twofaconfirm', {
            verification_code: twoFactorCode.value,
        });
        if (data.status === 'FAIL') {
            toast.error(data.data?.message ?? 'Invalid code.');
            return;
        }
        toast.success(data.data?.message ?? 'Two-factor authentication is now enabled.');
        twoFactorSetupQr.value = '';
        twoFactorSetupSecret.value = '';
        twoFactorCode.value = '';
        await fetchSession();
    } catch (err) {
        toast.error(err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Something went wrong.');
    } finally {
        twoFactorLoading.value = false;
    }
}

async function disableTwoFactor() {
    twoFactorDisableLoading.value = true;
    try {
        const { data } = await axios.post('/api/session/twofadisable', {
            password: twoFactorDisablePassword.value,
        });
        if (data.status === 'FAIL') {
            toast.error(data.data?.message ?? 'Failed to disable.');
            return;
        }
        toast.success('Two-factor authentication disabled.');
        twoFactorDisablePassword.value = '';
        await fetchSession();
    } catch (err) {
        toast.error(err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Something went wrong.');
    } finally {
        twoFactorDisableLoading.value = false;
    }
}

async function onMarketingChange() {
    marketingLoading.value = true;
    try {
        const { data } = await axios.post('/api/session/updatemarketing', {
            marketing: marketingOptIn.value,
        });
        if (data.status === 'FAIL') {
            marketingOptIn.value = !marketingOptIn.value;
            toast.error(data.data?.message ?? 'Failed to update preference.');
            return;
        }
        toast.success(data.data?.message ?? 'Preference saved.');
        await fetchSession();
        marketingOptIn.value = user.value?.marketing ?? false;
    } catch {
        marketingOptIn.value = !marketingOptIn.value;
        toast.error('Something went wrong.');
    } finally {
        marketingLoading.value = false;
    }
}
</script>

<template>
    <div class="max-w-2xl space-y-8">
        <div>
            <h1 class="text-xl font-semibold text-site-heading">Account settings</h1>
            <p class="mt-1 text-sm text-site-body">Manage your password, two-factor authentication, and email preferences.</p>
        </div>

        <!-- Change password -->
        <section class="rounded-xl border border-slate-700 bg-slate-900/50 p-6">
            <h2 class="text-lg font-medium text-site-heading">Change password</h2>
            <p class="mt-1 text-sm text-site-body">Update your password. Use at least 8 characters with uppercase, lowercase, a number, and a special character.</p>
            <form class="mt-4 space-y-4" @submit.prevent="onChangePassword">
                <div>
                    <label for="account-current-password" class="block text-sm font-medium text-site-body">Current password</label>
                    <input
                        id="account-current-password"
                        v-model="currentPassword"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                    />
                </div>
                <div>
                    <label for="account-new-password" class="block text-sm font-medium text-site-body">New password</label>
                    <input
                        id="account-new-password"
                        v-model="newPassword"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                        @focus="passwordFieldFocused = true"
                        @blur="passwordFieldFocused = false"
                    />
                    <div v-if="newPassword && passwordFieldFocused" class="mt-1.5 space-y-1.5">
                        <div class="flex gap-1">
                            <span
                                v-for="i in 4"
                                :key="i"
                                class="h-1 flex-1 rounded-full transition-colors"
                                :class="i <= passwordStrength.level ? (passwordStrength.level <= 1 ? 'bg-red-500' : passwordStrength.level <= 2 ? 'bg-amber-500' : passwordStrength.level <= 3 ? 'bg-yellow-500' : 'bg-emerald-500') : 'bg-slate-600'"
                            />
                        </div>
                        <ul class="text-xs text-slate-400 space-y-0.5">
                            <li :class="passwordStrength.criteria.length ? 'text-emerald-400' : ''">{{ passwordStrength.criteria.length ? '✓' : '○' }} At least 8 characters</li>
                            <li :class="passwordStrength.criteria.lowercase ? 'text-emerald-400' : ''">{{ passwordStrength.criteria.lowercase ? '✓' : '○' }} One lowercase</li>
                            <li :class="passwordStrength.criteria.uppercase ? 'text-emerald-400' : ''">{{ passwordStrength.criteria.uppercase ? '✓' : '○' }} One uppercase</li>
                            <li :class="passwordStrength.criteria.number ? 'text-emerald-400' : ''">{{ passwordStrength.criteria.number ? '✓' : '○' }} One number</li>
                            <li :class="passwordStrength.criteria.special ? 'text-emerald-400' : ''">{{ passwordStrength.criteria.special ? '✓' : '○' }} One special character</li>
                        </ul>
                    </div>
                </div>
                <div>
                    <label for="account-confirm-password" class="block text-sm font-medium text-site-body">Confirm new password</label>
                    <input
                        id="account-confirm-password"
                        v-model="newPasswordConfirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="mt-1 block w-full rounded-lg border px-3 py-2 text-site-heading focus:outline-none focus:ring-1"
                        :class="newPasswordConfirmation && !passwordsMatch ? 'border-red-500 bg-slate-800 focus:border-red-500 focus:ring-red-500' : 'border-slate-600 bg-slate-800 focus:border-cta focus:ring-cta'"
                    />
                    <p v-if="newPasswordConfirmation && !passwordsMatch" class="mt-1 text-xs text-red-400">Passwords do not match.</p>
                </div>
                <button
                    type="submit"
                    :disabled="passwordLoading || !changePasswordValid"
                    class="rounded-lg bg-cta px-4 py-2.5 text-sm font-medium text-white hover:bg-cta-hover focus:outline-none focus:ring-2 focus:ring-cta focus:ring-offset-2 focus:ring-offset-black disabled:opacity-60"
                >
                    {{ passwordLoading ? 'Updating…' : 'Update password' }}
                </button>
            </form>
        </section>

        <!-- Two-factor authentication -->
        <section class="rounded-xl border border-slate-700 bg-slate-900/50 p-6">
            <h2 class="text-lg font-medium text-site-heading">Two-factor authentication</h2>
            <p class="mt-1 text-sm text-site-body">
                {{ twoFactorEnabled ? 'Two-factor authentication is enabled. You will be asked for a code when signing in.' : 'Add an extra layer of security by requiring a code from your authenticator app.' }}
            </p>

            <template v-if="twoFactorEnabled">
                <div class="mt-4 flex flex-wrap items-center gap-4">
                    <span class="inline-flex items-center rounded-full bg-emerald-900/50 px-3 py-1 text-sm text-emerald-300">Enabled</span>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <input
                            v-model="twoFactorDisablePassword"
                            type="password"
                            placeholder="Your password to disable"
                            class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-site-heading placeholder-slate-500 focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                        />
                        <button
                            type="button"
                            :disabled="twoFactorDisableLoading || !twoFactorDisablePassword"
                            class="rounded-lg border border-red-600/80 bg-red-900/30 px-4 py-2 text-sm font-medium text-red-300 hover:bg-red-900/50 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-60"
                            @click="disableTwoFactor"
                        >
                            {{ twoFactorDisableLoading ? 'Disabling…' : 'Disable 2FA' }}
                        </button>
                    </div>
                </div>
            </template>

            <template v-else>
                <template v-if="!twoFactorSetupQr">
                    <button
                        type="button"
                        :disabled="twoFactorLoading"
                        class="mt-4 rounded-lg bg-cta px-4 py-2.5 text-sm font-medium text-white hover:bg-cta-hover focus:outline-none focus:ring-2 focus:ring-cta disabled:opacity-60"
                        @click="startTwoFactorSetup"
                    >
                        {{ twoFactorLoading ? 'Starting…' : 'Enable two-factor authentication' }}
                    </button>
                </template>
                <template v-else>
                    <div class="mt-4 space-y-4">
                        <p class="text-sm text-site-body">Scan this QR code with your authenticator app (e.g. Google Authenticator, Authy), then enter the 6-digit code below.</p>
                        <div class="flex flex-wrap items-start gap-6">
                            <div class="rounded-lg border border-slate-600 bg-white p-2">
                                <img
                                    :src="'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(twoFactorSetupQr)"
                                    alt="QR code for authenticator"
                                    width="200"
                                    height="200"
                                />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-site-body">Or enter this secret manually:</p>
                                <code class="mt-1 block break-all rounded bg-slate-800 px-2 py-1 text-xs text-site-heading">{{ twoFactorSetupSecret }}</code>
                                <div class="mt-4">
                                    <label for="account-2fa-code" class="block text-sm font-medium text-site-body">Verification code</label>
                                    <input
                                        id="account-2fa-code"
                                        v-model="twoFactorCode"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="6"
                                        placeholder="000000"
                                        class="mt-1 block w-full max-w-xs rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                                    />
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        :disabled="twoFactorLoading || twoFactorCode.length !== 6"
                                        class="rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white hover:bg-cta-hover focus:outline-none focus:ring-2 focus:ring-cta disabled:opacity-60"
                                        @click="confirmTwoFactor"
                                    >
                                        {{ twoFactorLoading ? 'Verifying…' : 'Confirm and enable' }}
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="twoFactorLoading"
                                        class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-medium text-site-body hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cta"
                                        @click="cancelTwoFactorSetup"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </template>
        </section>

        <!-- Marketing emails -->
        <section class="rounded-xl border border-slate-700 bg-slate-900/50 p-6">
            <h2 class="text-lg font-medium text-site-heading">Marketing emails</h2>
            <p class="mt-1 text-sm text-site-body">Choose whether to receive marketing and product updates by email.</p>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <label class="flex cursor-pointer items-center gap-2">
                    <input
                        v-model="marketingOptIn"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-600 bg-slate-800 text-cta focus:ring-cta"
                        :disabled="marketingLoading"
                        @change="onMarketingChange"
                    />
                    <span class="text-sm text-site-body">I'd like to receive marketing emails</span>
                </label>
                <span v-if="marketingLoading" class="text-xs text-slate-400">Saving…</span>
            </div>
        </section>
    </div>
</template>
