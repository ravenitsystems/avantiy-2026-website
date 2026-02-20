<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import axios from 'axios';
import { useSession } from '../composables/useSession';

const router = useRouter();
const { fetchSession } = useSession();

const email = ref('');
const password = ref('');
const loading = ref(false);
const errorMessage = ref('');
const step = ref('credentials');
const twoFactorCode = ref('');
const rememberDevice = ref(false);

async function onSubmit() {
    errorMessage.value = '';
    loading.value = true;
    try {
        const body =
            step.value === 'credentials'
                ? { email: email.value, password: password.value, remember_device: rememberDevice.value }
                : {
                      email: email.value,
                      password: password.value,
                      '2fa': twoFactorCode.value,
                      remember_device: rememberDevice.value,
                  };
        const { data } = await axios.post('/api/session/login', body);

        if (data.status === 'FAIL') {
            errorMessage.value = data.data?.message ?? 'Invalid email or password.';
            return;
        }

        if (data.data?.two_factor_required === true) {
            step.value = '2fa';
            return;
        }

        await fetchSession();
        router.push({ name: 'dashboard-home' });
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Something went wrong.';
        errorMessage.value = msg;
    } finally {
        loading.value = false;
    }
}

function backToCredentials() {
    step.value = 'credentials';
    twoFactorCode.value = '';
    errorMessage.value = '';
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-black px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h1 class="text-xl font-semibold text-site-heading">Sign in</h1>
                <p class="mt-1 text-sm text-site-body">
                    {{ step === 'credentials' ? 'Enter your credentials to access the dashboard.' : 'Enter the code from your authenticator app.' }}
                </p>

                <form class="mt-6 space-y-4" @submit.prevent="onSubmit">
                    <div v-if="errorMessage" class="rounded-lg bg-red-900/40 px-3 py-2 text-sm text-red-300">
                        {{ errorMessage }}
                    </div>

                    <template v-if="step === 'credentials'">
                        <div>
                            <label for="login-email" class="block text-sm font-medium text-site-body">Email</label>
                            <input
                                id="login-email"
                                v-model="email"
                                type="email"
                                autocomplete="email"
                                required
                                class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                                placeholder="you@example.com"
                            />
                        </div>
                        <div>
                            <label for="login-password" class="block text-sm font-medium text-site-body">Password</label>
                            <input
                                id="login-password"
                                v-model="password"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                            />
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2 text-site-body">
                                <input
                                    v-model="rememberDevice"
                                    type="checkbox"
                                    class="rounded border-slate-600 bg-slate-800 text-cta focus:ring-cta"
                                />
                                Remember this device
                            </label>
                            <RouterLink
                                to="/dashboard/reset-password"
                                class="font-medium text-site-body hover:text-site-heading"
                            >
                                Forgot password?
                            </RouterLink>
                        </div>
                    </template>

                    <template v-else>
                        <div>
                            <label for="login-2fa" class="block text-sm font-medium text-site-body">Authentication code</label>
                            <input
                                id="login-2fa"
                                v-model="twoFactorCode"
                                type="text"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                maxlength="8"
                                placeholder="000000"
                                class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                            />
                        </div>
                        <button
                            type="button"
                            class="text-sm text-site-body hover:text-site-heading"
                            @click="backToCredentials"
                        >
                            Back to email and password
                        </button>
                    </template>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full rounded-lg bg-cta px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover focus:outline-none focus:ring-2 focus:ring-cta focus:ring-offset-2 focus:ring-offset-black disabled:opacity-60"
                    >
                        {{ loading ? 'Signing in…' : step === '2fa' ? 'Verify' : 'Sign in' }}
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-site-body">
                    Don't have an account?
                    <RouterLink to="/dashboard/register" class="font-medium text-cta hover:text-cta-hover hover:underline">
                        Register
                    </RouterLink>
                </p>
            </div>
        </div>
    </div>
</template>
