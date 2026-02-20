<script setup>
import { ref, computed, onMounted } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import axios from 'axios';
import { useSession } from '../composables/useSession';

const router = useRouter();
const { fetchSession } = useSession();

const step = ref('details');
const countries = ref([]);
const firstName = ref('');
const lastName = ref('');
const email = ref('');
const countryId = ref(null);
const telephone = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const verificationCode = ref('');
const passwordFieldFocused = ref(false);
const invalidFields = ref({
    firstName: false,
    lastName: false,
    email: false,
    country: false,
    telephone: false,
    password: false,
    passwordConfirmation: false,
});
const loading = ref(false);
const errorMessage = ref('');

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

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/country/index');
        if (data.status === 'PASS' && data.data?.countries) {
            countries.value = data.data.countries;
            if (countries.value.length > 0) {
                countryId.value = countries.value[0].id;
            }
        }
    } catch {
        // fallback to empty, country_id 840 will be used
    }
});

async function onSubmit() {
    errorMessage.value = '';
    if (step.value === 'details') {
        if (!validateDetailsForm()) {
            return;
        }
    }
    loading.value = true;
    try {
        if (step.value === 'details') {
            const { data } = await axios.post('/api/session/register', {
                first_name: firstName.value,
                last_name: lastName.value,
                email: email.value,
                country_id: countryId.value,
                telephone: telephone.value,
                password: password.value,
                marketing: false,
            });
            if (data.status === 'FAIL') {
                errorMessage.value = data.data?.message ?? 'Registration failed.';
                return;
            }
            step.value = 'verify';
        } else {
            const { data } = await axios.post('/api/session/verifyemail', {
                email: email.value,
                verification_code: verificationCode.value,
            });
            if (data.status === 'FAIL') {
                errorMessage.value = data.data?.message ?? 'Verification failed.';
                return;
            }
            await fetchSession();
            router.push({ name: 'dashboard-home' });
        }
    } catch (err) {
        const msg = err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Something went wrong.';
        errorMessage.value = msg;
    } finally {
        loading.value = false;
    }
}

function clearInvalid(field) {
    invalidFields.value[field] = false;
}

function validateDetailsForm() {
    const pw = password.value;
    const criteria = computePasswordStrength(pw).criteria;
    const pwValid = criteria && criteria.length && criteria.lowercase && criteria.uppercase && criteria.number && criteria.special;
    const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim());

    invalidFields.value = {
        firstName: !firstName.value.trim(),
        lastName: !lastName.value.trim(),
        email: !email.value.trim() || !emailValid,
        country: !countryId.value,
        telephone: false,
        password: !pwValid,
        passwordConfirmation: !passwordsMatch.value,
    };
    return !Object.values(invalidFields.value).some(Boolean);
}

function backToDetails() {
    step.value = 'details';
    verificationCode.value = '';
    errorMessage.value = '';
    invalidFields.value = { firstName: false, lastName: false, email: false, country: false, telephone: false, password: false, passwordConfirmation: false };
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-black px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-xl">
                <h1 class="text-xl font-semibold text-site-heading">Create an account</h1>
                <p class="mt-1 text-sm text-site-body">Register to access the dashboard.</p>

                <form v-if="step === 'details'" class="mt-6 space-y-4" @submit.prevent="onSubmit">
                    <div>
                        <label for="register-first-name" class="block text-sm font-medium text-site-body">First name</label>
                        <input
                            id="register-first-name"
                            v-model="firstName"
                            type="text"
                            autocomplete="given-name"
                            required
                            class="mt-1 block w-full rounded-lg border bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:outline-none focus:ring-1"
                            :class="invalidFields.firstName ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-600 focus:border-cta focus:ring-cta'"
                            placeholder="First name"
                            @input="clearInvalid('firstName')"
                        />
                    </div>
                    <div>
                        <label for="register-last-name" class="block text-sm font-medium text-site-body">Last name</label>
                        <input
                            id="register-last-name"
                            v-model="lastName"
                            type="text"
                            autocomplete="family-name"
                            required
                            class="mt-1 block w-full rounded-lg border bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:outline-none focus:ring-1"
                            :class="invalidFields.lastName ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-600 focus:border-cta focus:ring-cta'"
                            placeholder="Last name"
                            @input="clearInvalid('lastName')"
                        />
                    </div>
                    <div>
                        <label for="register-email" class="block text-sm font-medium text-site-body">Email</label>
                        <input
                            id="register-email"
                            v-model="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="mt-1 block w-full rounded-lg border bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:outline-none focus:ring-1"
                            :class="invalidFields.email ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-600 focus:border-cta focus:ring-cta'"
                            placeholder="you@example.com"
                            @input="clearInvalid('email')"
                        />
                    </div>
                    <div>
                        <label for="register-country" class="block text-sm font-medium text-site-body">Country</label>
                        <select
                            id="register-country"
                            v-model.number="countryId"
                            required
                            class="mt-1 block w-full rounded-lg border bg-slate-800 pl-3 pr-8 py-2 text-site-heading focus:outline-none focus:ring-1"
                            :class="invalidFields.country ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-600 focus:border-cta focus:ring-cta'"
                            @change="clearInvalid('country')"
                        >
                            <option v-if="!countries.length" value="">Loading countries…</option>
                            <option v-for="c in countries" :key="c.id" :value="c.id">
                                {{ c.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="register-telephone" class="block text-sm font-medium text-site-body">Phone number</label>
                        <input
                            id="register-telephone"
                            v-model="telephone"
                            type="tel"
                            autocomplete="tel"
                            class="mt-1 block w-full rounded-lg border bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:outline-none focus:ring-1"
                            :class="invalidFields.telephone ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-600 focus:border-cta focus:ring-cta'"
                            placeholder="+1 555 123 4567"
                            @input="clearInvalid('telephone')"
                        />
                    </div>
                    <div>
                        <label for="register-password" class="block text-sm font-medium text-site-body">Password</label>
                        <input
                            id="register-password"
                            v-model="password"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 block w-full rounded-lg border bg-slate-800 px-3 py-2 text-site-heading focus:outline-none focus:ring-1"
                            :class="invalidFields.password ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-600 focus:border-cta focus:ring-cta'"
                            @focus="passwordFieldFocused = true"
                            @blur="passwordFieldFocused = false"
                            @input="clearInvalid('password')"
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
                            <p
                                class="text-xs"
                                :class="passwordStrength.level <= 1 ? 'text-red-400' : passwordStrength.level <= 2 ? 'text-amber-400' : passwordStrength.level <= 3 ? 'text-yellow-400' : 'text-emerald-400'"
                            >
                                {{ passwordStrength.label }}
                            </p>
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
                        <label for="register-password-confirm" class="block text-sm font-medium text-site-body">Confirm password</label>
                        <input
                            id="register-password-confirm"
                            v-model="passwordConfirmation"
                            type="password"
                            autocomplete="new-password"
                            required
                            class="mt-1 block w-full rounded-lg border bg-slate-800 px-3 py-2 text-site-heading focus:outline-none focus:ring-1"
                            :class="(invalidFields.passwordConfirmation || (passwordConfirmation && !passwordsMatch)) ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-600 focus:border-cta focus:ring-cta'"
                            @input="clearInvalid('passwordConfirmation')"
                        />
                        <p v-if="passwordConfirmation && !passwordsMatch" class="mt-1 text-xs text-red-400">Passwords do not match.</p>
                    </div>
                    <p v-if="errorMessage" class="text-sm text-red-400">{{ errorMessage }}</p>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full rounded-lg bg-cta px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover focus:outline-none focus:ring-2 focus:ring-cta focus:ring-offset-2 focus:ring-offset-black disabled:opacity-60"
                    >
                        {{ loading ? 'Sending code…' : 'Continue' }}
                    </button>
                </form>

                <form v-else class="mt-6 space-y-4" @submit.prevent="onSubmit">
                    <p class="text-sm text-site-body">
                        We sent a 6-digit verification code to <strong class="text-site-heading">{{ email }}</strong>
                    </p>
                    <div>
                        <label for="register-code" class="block text-sm font-medium text-site-body">Verification code</label>
                        <input
                            id="register-code"
                            v-model="verificationCode"
                            type="text"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            required
                            pattern="[0-9]{6}"
                            class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                            placeholder="000000"
                        />
                        <p class="mt-1 text-xs text-slate-400">Enter the code from your email</p>
                    </div>
                    <p v-if="errorMessage" class="text-sm text-red-400">{{ errorMessage }}</p>
                    <button
                        type="submit"
                        :disabled="loading || verificationCode.length !== 6"
                        class="w-full rounded-lg bg-cta px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover focus:outline-none focus:ring-2 focus:ring-cta focus:ring-offset-2 focus:ring-offset-black disabled:opacity-60"
                    >
                        {{ loading ? 'Verifying…' : 'Complete registration' }}
                    </button>
                    <button
                        type="button"
                        class="w-full text-sm text-slate-400 hover:text-site-body"
                        @click="backToDetails"
                    >
                        Use different email
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-site-body">
                    Already have an account?
                    <RouterLink to="/dashboard/login" class="font-medium text-cta hover:text-cta-hover hover:underline">
                        Sign in
                    </RouterLink>
                </p>
            </div>
        </div>
    </div>
</template>
