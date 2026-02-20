<script setup>
import { ref } from 'vue';
import { RouterLink } from 'vue-router';

const email = ref('');
const loading = ref(false);
const submitted = ref(false);

function onSubmit() {
    loading.value = true;
    // TODO: call password reset API
    setTimeout(() => {
        loading.value = false;
        submitted.value = true;
    }, 500);
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-black px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h1 class="text-xl font-semibold text-site-heading">Reset password</h1>
                <p class="mt-1 text-sm text-site-body">
                    Enter your email and we'll send you a link to reset your password.
                </p>

                <form v-if="!submitted" class="mt-6 space-y-4" @submit.prevent="onSubmit">
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
                        {{ loading ? 'Sending…' : 'Send reset link' }}
                    </button>
                </form>

                <div v-else class="mt-6 rounded-lg border border-slate-700 bg-slate-800/80 p-4 text-sm text-site-body">
                    If an account exists for that email, we've sent a password reset link. Check your inbox.
                </div>

                <p class="mt-6 text-center text-sm text-site-body">
                    <RouterLink to="/dashboard/login" class="font-medium text-cta hover:text-cta-hover hover:underline">
                        Back to sign in
                    </RouterLink>
                </p>
            </div>
        </div>
    </div>
</template>
