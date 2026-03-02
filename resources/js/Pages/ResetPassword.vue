<script setup>
import { ref, computed } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from '../composables/useToast';

const router = useRouter();
const toast = useToast();

const step = ref('email');
const email = ref('');
const verificationCode = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const passwordFieldFocused = ref(false);
const loading = ref(false);

const passwordsMatch = computed(() =>
    password.value === passwordConfirmation.value || !passwordConfirmation.value
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
    return {
        level: met,
        label: labels[met],
        criteria,
    };
}

const passwordStrength = computed(() => computePasswordStrength(password.value));

const passwordMeetsPolicy = computed(() => {
    const { criteria } = passwordStrength.value;
    return criteria && criteria.length && criteria.lowercase && criteria.uppercase && criteria.number && criteria.special;
});

const formValid = computed(() =>
    passwordsMatch.value && passwordMeetsPolicy.value
);

async function onSubmit() {
    loading.value = true;
    try {
        if (step.value === 'email') {
            const { data } = await axios.post('/api/session/forgotpassword', {
                email: email.value,
            });
            if (data.status === 'FAIL') {
                toast.error(data.data?.message ?? 'Request failed.');
                return;
            }
            toast.success('Check your email for a verification code.');
            step.value = 'code';
        } else {
            const { data } = await axios.post('/api/session/resetpassword', {
                email: email.value,
                verification_code: verificationCode.value,
                password: password.value,
            });
            if (data.status === 'FAIL') {
                toast.error(data.data?.message ?? 'Reset failed.');
                return;
            }
            router.push({ name: 'dashboard-login', query: { reset: 'success' } });
        }
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Something went wrong.';
        toast.error(msg);
    } finally {
        loading.value = false;
    }
}

function backToEmail() {
    step.value = 'email';
    verificationCode.value = '';
    password.value = '';
    passwordConfirmation.value = '';
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-black px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h1 class="text-xl font-semibold text-site-heading">Reset password</h1>
                <p class="mt-1 text-sm text-site-body">
                    <template v-if="step === 'email'">
                        Enter your email and we'll send you a verification code.
                    </template>
                    <template v-else>
                        Enter the code from your email and choose a new password.
                    </template>
                </p>

                <form v-if="step === 'email'" class="mt-6 space-y-4" @submit.prevent="onSubmit">
                    <div>
                        <label for="reset-email" class="block text-sm font-medium text-site-body">Email</label>
                        <input
                            id="reset-email"
                            v-model="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                            placeholder="you@example.com"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full rounded-lg bg-cta px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover focus:outline-none focus:ring-2 focus:ring-cta focus:ring-offset-2 focus:ring-offset-black disabled:opacity-60"
                    >
                        {{ loading ? 'Sending…' : 'Send verification code' }}
                    </button>
                </form>

                <form v-else class="mt-6 space-y-4" @submit.prevent="onSubmit">
                    <div>
                        <label class="block text-sm font-medium text-site-body">Email</label>
                        <p class="mt-1 text-site-heading">{{ email }}</p>
                    </div>
                    <div>
                        <label for="reset-code" class="block text-sm font-medium text-site-body">Verification code</label>
                        <input
                            id="reset-code"
                            v-model="verificationCode"
                            type="text"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            required
                            maxlength="6"
                            class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                            placeholder="000000"
                        />
                    </div>
                    <div>
                        <label for="reset-password" class="block text-sm font-medium text-site-body">New password</label>
                        <input
                            id="reset-password"
                            v-model="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                            @focus="passwordFieldFocused = true"
                            @blur="passwordFieldFocused = false"
                        />
                        <div v-if="password && passwordFieldFocused" class="mt-1.5 space-y-1.5">
                            <div class="flex gap-1">
                                <span
                                    v-for="i in 4"
                                    :key="i"
                                    class="h-1 flex-1 rounded-full transition-colors"
                                    :class="i <= passwordStrength.level ? (passwordStrength.level <= 1 ? 'bg-red-500' : passwordStrength.level <= 2 ? 'bg-amber-500' : passwordStrength.level <= 3 ? 'bg-yellow-500' : 'bg-emerald-500') : 'bg-slate-600'"
                                />
                            </div>
                            <ul class="text-xs text-slate-400 space-y-0.5">
                                <li :class="passwordStrength.criteria.length ? 'text-emerald-400' : ''">
                                    {{ passwordStrength.criteria.length ? '✓' : '○' }} At least 8 characters
                                </li>
                                <li :class="passwordStrength.criteria.lowercase ? 'text-emerald-400' : ''">
                                    {{ passwordStrength.criteria.lowercase ? '✓' : '○' }} One lowercase letter
                                </li>
                                <li :class="passwordStrength.criteria.uppercase ? 'text-emerald-400' : ''">
                                    {{ passwordStrength.criteria.uppercase ? '✓' : '○' }} One uppercase letter
                                </li>
                                <li :class="passwordStrength.criteria.number ? 'text-emerald-400' : ''">
                                    {{ passwordStrength.criteria.number ? '✓' : '○' }} One number
                                </li>
                                <li :class="passwordStrength.criteria.special ? 'text-emerald-400' : ''">
                                    {{ passwordStrength.criteria.special ? '✓' : '○' }} One special character
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div>
                        <label for="reset-password-confirm" class="block text-sm font-medium text-site-body">Confirm password</label>
                        <input
                            id="reset-password-confirm"
                            v-model="passwordConfirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 block w-full rounded-lg border px-3 py-2 text-site-heading focus:outline-none focus:ring-1"
                            :class="passwordConfirmation && !passwordsMatch ? 'border-red-500 bg-slate-800 focus:border-red-500 focus:ring-red-500' : 'border-slate-600 bg-slate-800 focus:border-cta focus:ring-cta'"
                            placeholder="Repeat new password"
                        />
                        <p v-if="passwordConfirmation && !passwordsMatch" class="mt-1 text-xs text-red-400">Passwords do not match.</p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            :disabled="loading"
                            class="flex-1 rounded-lg border border-slate-600 bg-slate-800 px-4 py-2.5 text-sm font-medium text-site-heading transition-colors hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-cta focus:ring-offset-2 focus:ring-offset-black disabled:opacity-60"
                            @click="backToEmail"
                        >
                            Back
                        </button>
                        <button
                            type="submit"
                            :disabled="loading || !formValid"
                            class="flex-1 rounded-lg bg-cta px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover focus:outline-none focus:ring-2 focus:ring-cta focus:ring-offset-2 focus:ring-offset-black disabled:opacity-60"
                        >
                            {{ loading ? 'Resetting…' : 'Reset password' }}
                        </button>
                    </div>
                </form>

                <p class="mt-6 text-center text-sm text-site-body">
                    <RouterLink to="/dashboard/login" class="font-medium text-cta hover:text-cta-hover hover:underline">
                        Back to sign in
                    </RouterLink>
                </p>
            </div>
        </div>
    </div>
</template>
