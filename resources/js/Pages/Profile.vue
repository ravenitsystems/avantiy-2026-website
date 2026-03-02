<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useSession } from '../composables/useSession';
import { useToast } from '../composables/useToast';

const { fetchSession } = useSession();
const toast = useToast();

const loading = ref(true);
const saving = ref(false);

const firstName = ref('');
const lastName = ref('');
const email = ref('');
const telephone = ref('');
const countryId = ref(null);
const addressLine1 = ref('');
const addressLine2 = ref('');
const city = ref('');
const stateRegion = ref('');
const postalCode = ref('');

const countries = ref([]);

const inputClass = 'mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta';
const labelClass = 'block text-sm font-medium text-site-body';

onMounted(async () => {
    try {
        const [profileRes, countriesRes] = await Promise.all([
            axios.get('/api/session/profile'),
            axios.get('/api/country/index'),
        ]);

        if (profileRes.data.status === 'PASS' && profileRes.data.data) {
            const p = profileRes.data.data;
            firstName.value = p.first_name ?? '';
            lastName.value = p.last_name ?? '';
            email.value = p.email ?? '';
            telephone.value = p.telephone ?? '';
            countryId.value = p.country_id ?? null;
            addressLine1.value = p.address_line_1 ?? '';
            addressLine2.value = p.address_line_2 ?? '';
            city.value = p.city ?? '';
            stateRegion.value = p.state_region ?? '';
            postalCode.value = p.postal_code ?? '';
        }

        if (countriesRes.data.status === 'PASS' && countriesRes.data.data?.countries) {
            countries.value = countriesRes.data.data.countries;
            if (countries.value.length > 0 && (countryId.value === null || countryId.value === 0)) {
                countryId.value = countries.value[0].id;
            }
        }
    } catch {
        toast.error('Failed to load profile.');
    } finally {
        loading.value = false;
    }
});

async function onSubmit() {
    saving.value = true;
    try {
        const { data } = await axios.post('/api/session/updateprofile', {
            first_name: firstName.value,
            last_name: lastName.value,
            email: email.value,
            telephone: telephone.value,
            country_id: countryId.value ?? 0,
            address_line_1: addressLine1.value,
            address_line_2: addressLine2.value,
            city: city.value,
            state_region: stateRegion.value,
            postal_code: postalCode.value,
        });
        if (data.status === 'FAIL') {
            toast.error(data.data?.message ?? 'Failed to update profile.');
            return;
        }
        toast.success(data.data?.message ?? 'Profile updated.');
        await fetchSession();
    } catch (err) {
        toast.error(err.response?.data?.data?.message ?? err.response?.data?.message ?? 'Something went wrong.');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div class="max-w-2xl space-y-8">
        <div>
            <h1 class="text-xl font-semibold text-site-heading">User profile</h1>
            <p class="mt-1 text-sm text-site-body">Edit your personal information and address.</p>
        </div>

        <div v-if="loading" class="rounded-xl border border-slate-700 bg-slate-900/50 p-8 text-center text-site-body">
            Loading…
        </div>

        <form v-else class="space-y-8" @submit.prevent="onSubmit">
            <!-- Personal information -->
            <section class="rounded-xl border border-slate-700 bg-slate-900/50 p-6">
                <h2 class="text-lg font-medium text-site-heading">Personal information</h2>
                <p class="mt-1 text-sm text-site-body">Your name and contact details.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="profile-first-name" :class="labelClass">First name</label>
                        <input
                            id="profile-first-name"
                            v-model="firstName"
                            type="text"
                            autocomplete="given-name"
                            required
                            :class="inputClass"
                            placeholder="First name"
                        />
                    </div>
                    <div>
                        <label for="profile-last-name" :class="labelClass">Last name</label>
                        <input
                            id="profile-last-name"
                            v-model="lastName"
                            type="text"
                            autocomplete="family-name"
                            required
                            :class="inputClass"
                            placeholder="Last name"
                        />
                    </div>
                </div>
                <div class="mt-4">
                    <label for="profile-email" :class="labelClass">Email address</label>
                    <input
                        id="profile-email"
                        v-model="email"
                        type="email"
                        autocomplete="email"
                        required
                        :class="inputClass"
                        placeholder="you@example.com"
                    />
                </div>
                <div class="mt-4">
                    <label for="profile-telephone" :class="labelClass">Phone number</label>
                    <input
                        id="profile-telephone"
                        v-model="telephone"
                        type="tel"
                        autocomplete="tel"
                        :class="inputClass"
                        placeholder="+1 555 123 4567"
                    />
                </div>
                <div class="mt-4">
                    <label for="profile-country" :class="labelClass">Country</label>
                    <select
                        id="profile-country"
                        v-model.number="countryId"
                        required
                        class="mt-1 block w-full rounded-lg border border-slate-600 bg-slate-800 pl-3 pr-8 py-2 text-site-heading focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                    >
                        <option v-if="!countries.length" value="">Loading…</option>
                        <option v-for="c in countries" :key="c.id" :value="c.id">
                            {{ c.name }}
                        </option>
                    </select>
                </div>
            </section>

            <!-- Physical address (optional) -->
            <section class="rounded-xl border border-slate-700 bg-slate-900/50 p-6">
                <h2 class="text-lg font-medium text-site-heading">Physical address</h2>
                <p class="mt-1 text-sm text-site-body">Optional. Not required on registration; you can add or update it here.</p>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="profile-address-line-1" :class="labelClass">Address line 1</label>
                        <input
                            id="profile-address-line-1"
                            v-model="addressLine1"
                            type="text"
                            autocomplete="address-line1"
                            :class="inputClass"
                            placeholder="Street address"
                        />
                    </div>
                    <div>
                        <label for="profile-address-line-2" :class="labelClass">Address line 2</label>
                        <input
                            id="profile-address-line-2"
                            v-model="addressLine2"
                            type="text"
                            autocomplete="address-line2"
                            :class="inputClass"
                            placeholder="Apartment, suite, etc. (optional)"
                        />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="profile-city" :class="labelClass">City</label>
                            <input
                                id="profile-city"
                                v-model="city"
                                type="text"
                                autocomplete="address-level2"
                                :class="inputClass"
                                placeholder="City"
                            />
                        </div>
                        <div>
                            <label for="profile-state-region" :class="labelClass">State / Region</label>
                            <input
                                id="profile-state-region"
                                v-model="stateRegion"
                                type="text"
                                autocomplete="address-level1"
                                :class="inputClass"
                                placeholder="State or region"
                            />
                        </div>
                    </div>
                    <div>
                        <label for="profile-postal-code" :class="labelClass">Postal code</label>
                        <input
                            id="profile-postal-code"
                            v-model="postalCode"
                            type="text"
                            autocomplete="postal-code"
                            :class="inputClass"
                            placeholder="Postal code"
                        />
                    </div>
                </div>
            </section>

            <button
                type="submit"
                :disabled="saving"
                class="rounded-lg bg-cta px-4 py-2.5 text-sm font-medium text-white hover:bg-cta-hover focus:outline-none focus:ring-2 focus:ring-cta focus:ring-offset-2 focus:ring-offset-black disabled:opacity-60"
            >
                {{ saving ? 'Saving…' : 'Save profile' }}
            </button>
        </form>
    </div>
</template>
