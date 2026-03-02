<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '../composables/useToast';

defineProps({
    embedded: { type: Boolean, default: false },
});

const toast = useToast();
const paymentMethods = ref([]);
const loading = ref(true);

const addCardOpen = ref(false);
const addCardLoading = ref(false);
const addCardName = ref('');
const cardElementRef = ref(null);
let stripe = null;
let cardElement = null;
let clientSecret = '';

const editingId = ref(null);
const editName = ref('');
const editLoading = ref(false);

async function loadPaymentMethods() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/session/paymentmethods');
        if (data.status === 'PASS' && data.data?.payment_methods) {
            paymentMethods.value = data.data.payment_methods;
        }
    } catch {
        toast.error('Failed to load payment methods.');
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadPaymentMethods();
});

function showAddCard() {
    addCardOpen.value = true;
    addCardName.value = '';
    clientSecret = '';
    stripe = null;
    cardElement = null;
    // Wait for DOM then init Stripe
    setTimeout(initAddCardForm, 100);
}

function cancelAddCard() {
    addCardOpen.value = false;
    if (cardElement && cardElement.destroy) cardElement.destroy();
    cardElement = null;
    stripe = null;
}

async function initAddCardForm() {
    if (!cardElementRef.value) return;
    try {
        const [keyRes, secretRes] = await Promise.all([
            axios.get('/api/session/stripepublishablekey'),
            axios.post('/api/session/paymentmethodsetupintent'),
        ]);
        const pk = keyRes.data.status === 'PASS' ? keyRes.data.data?.publishable_key : '';
        clientSecret = secretRes.data.status === 'PASS' ? secretRes.data.data?.client_secret : '';
        if (!pk || !clientSecret) {
            toast.error('Payment setup is not available.');
            return;
        }
        if (window.Stripe) {
            stripe = window.Stripe(pk, { locale: 'en-GB' });
        } else {
            await loadScript('https://js.stripe.com/v3/');
            stripe = window.Stripe(pk, { locale: 'en-GB' });
        }
        const elements = stripe.elements();
        cardElementRef.value.innerHTML = '';
        cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#e2e8f0',
                    '::placeholder': { color: '#94a3b8' },
                },
                invalid: {
                    color: '#f87171',
                },
            },
        });
        cardElement.mount(cardElementRef.value);
    } catch (e) {
        toast.error(e?.message || 'Failed to load card form.');
    }
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) {
            resolve();
            return;
        }
        const s = document.createElement('script');
        s.src = src;
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
    });
}

async function confirmAddCard() {
    if (!stripe || !cardElement || !clientSecret) {
        toast.error('Please wait for the form to load.');
        return;
    }
    addCardLoading.value = true;
    try {
        const { setupIntent, error } = await stripe.confirmCardSetup(clientSecret, {
            payment_method: { card: cardElement },
        });
        if (error) {
            toast.error(error.message || 'Card could not be verified.');
            addCardLoading.value = false;
            return;
        }
        const pmId = setupIntent.payment_method;
        const { data } = await axios.post('/api/session/paymentmethodadd', {
            payment_method_id: pmId,
            name: addCardName.value.trim() || undefined,
        });
        if (data.status === 'FAIL') {
            toast.error(data.data?.message ?? 'Failed to save card.');
            addCardLoading.value = false;
            return;
        }
        if (data.data?.payment_method) {
            paymentMethods.value.push(data.data.payment_method);
        }
        addCardOpen.value = false;
        if (cardElement && cardElement.destroy) cardElement.destroy();
        cardElement = null;
        stripe = null;
        toast.success('Card added.');
    } catch (e) {
        toast.error(e?.message || e?.response?.data?.data?.message || 'Something went wrong.');
    } finally {
        addCardLoading.value = false;
    }
}

async function moveUp(index) {
    if (index <= 0) return;
    const list = [...paymentMethods.value];
    [list[index - 1], list[index]] = [list[index], list[index - 1]];
    await saveOrder(list);
}

async function moveDown(index) {
    if (index >= paymentMethods.value.length - 1) return;
    const list = [...paymentMethods.value];
    [list[index], list[index + 1]] = [list[index + 1], list[index]];
    await saveOrder(list);
}

async function saveOrder(list) {
    const orderedIds = list.map((pm) => pm.id);
    try {
        const { data } = await axios.post('/api/session/paymentmethodreorder', {
            ordered_ids: orderedIds,
        });
        if (data.status === 'PASS') {
            paymentMethods.value = list;
            toast.success('Order updated.');
        }
    } catch {
        toast.error('Failed to update order.');
    }
}

function displayLabel(pm) {
    return (pm.name && pm.name.trim()) ? pm.name.trim() : pm.description;
}

function startEditName(pm) {
    editingId.value = pm.id;
    editName.value = pm.name || '';
}

function cancelEditName() {
    editingId.value = null;
    editName.value = '';
}

async function saveCardName() {
    if (editingId.value === null) return;
    const id = editingId.value;
    const name = editName.value.trim();
    editLoading.value = true;
    try {
        const { data } = await axios.post('/api/session/paymentmethodupdate/' + id, { name });
        if (data.status === 'PASS') {
            const pm = paymentMethods.value.find((p) => p.id === id);
            if (pm) pm.name = name;
            toast.success('Card name updated.');
            editingId.value = null;
            editName.value = '';
        } else {
            toast.error(data.data?.message ?? 'Failed to update name.');
        }
    } catch (e) {
        toast.error(e?.response?.data?.data?.message ?? 'Failed to update name.');
    } finally {
        editLoading.value = false;
    }
}

async function removeCard(pm) {
    const label = displayLabel(pm);
    if (!confirm(`Remove ${label}?`)) return;
    try {
        const { data } = await axios.post('/api/session/paymentmethoddelete/' + pm.id);
        if (data.status === 'PASS') {
            paymentMethods.value = paymentMethods.value.filter((p) => p.id !== pm.id);
            toast.success('Card removed.');
        } else {
            toast.error(data.data?.message ?? 'Failed to remove card.');
        }
    } catch (e) {
        toast.error(e?.response?.data?.data?.message ?? 'Failed to remove card.');
    }
}
</script>

<template>
    <div class="max-w-2xl space-y-8">
        <div v-if="!embedded">
            <h1 class="text-xl font-semibold text-site-heading">Payment methods</h1>
            <p class="mt-1 text-sm text-site-body">
                Manage your saved cards. The first card in the list is used as the default for billing when we charge your account.
            </p>
        </div>

        <div v-if="loading" class="rounded-xl border border-slate-700 bg-slate-900/50 p-8 text-center text-site-body">
            Loading…
        </div>

        <template v-else>
            <section class="rounded-xl border border-slate-700 bg-slate-900/50 p-6">
                <h2 class="text-lg font-medium text-site-heading">Your cards</h2>
                <p class="mt-1 text-sm text-site-body">Reorder to set billing priority (top = default).</p>

                <ul v-if="paymentMethods.length" class="mt-4 space-y-2">
                    <li
                        v-for="(pm, index) in paymentMethods"
                        :key="pm.id"
                        class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-600 bg-slate-800/50 px-4 py-3"
                    >
                        <!-- Card name (or edit form): takes all available space -->
                        <div class="min-w-0 flex-1">
                            <template v-if="editingId === pm.id">
                                <div class="flex flex-wrap items-center gap-2">
                                    <input
                                        v-model="editName"
                                        type="text"
                                        maxlength="64"
                                        placeholder="Card name"
                                        class="max-w-[200px] rounded border border-slate-600 bg-slate-800 px-2 py-1 text-site-heading focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                                        @keydown.enter="saveCardName"
                                        @keydown.escape="cancelEditName"
                                    />
                                    <button
                                        type="button"
                                        :disabled="editLoading"
                                        class="rounded px-2 py-1 text-sm text-cta hover:bg-slate-700 disabled:opacity-60"
                                        @click="saveCardName"
                                    >
                                        {{ editLoading ? 'Saving…' : 'Save' }}
                                    </button>
                                    <button
                                        type="button"
                                        :disabled="editLoading"
                                        class="rounded px-2 py-1 text-sm text-site-body hover:bg-slate-700 disabled:opacity-60"
                                        @click="cancelEditName"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </template>
                            <template v-else>
                                <button
                                    type="button"
                                    class="text-left font-medium text-site-heading hover:text-cta focus:outline-none focus:ring-2 focus:ring-cta focus:ring-offset-2 focus:ring-offset-slate-800 rounded"
                                    @click="startEditName(pm)"
                                >
                                    {{ displayLabel(pm) }}
                                </button>
                            </template>
                        </div>
                        <!-- Card icon + last 4: right-aligned, flexbox vertical align -->
                        <div
                            v-if="editingId !== pm.id"
                            class="flex shrink-0 items-center gap-1.5 text-slate-400"
                            :title="(pm.brand || 'Card') + ' •••• ' + pm.last_four"
                        >
                            <span class="flex h-6 shrink-0 items-center overflow-hidden">
                                <template v-if="(pm.brand || '').toLowerCase() === 'visa'">
                                    <svg class="h-6 w-auto min-w-[24px]" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><text x="2" y="11.5" font-family="system-ui, sans-serif" font-size="10" font-weight="700" fill="currentColor">VISA</text></svg>
                                </template>
                                <template v-else-if="(pm.brand || '').toLowerCase() === 'mastercard'">
                                    <svg class="h-6 w-auto" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="9" cy="8" r="7" fill="currentColor" opacity="0.8"/><circle cx="15" cy="8" r="7" fill="currentColor" opacity="0.6"/></svg>
                                </template>
                                <template v-else-if="(pm.brand || '').toLowerCase() === 'amex' || (pm.brand || '').toLowerCase() === 'american_express'">
                                    <svg class="h-6 w-auto" viewBox="0 0 36 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><text x="2" y="11.5" font-family="system-ui, sans-serif" font-size="9" font-weight="700" fill="currentColor">AMEX</text></svg>
                                </template>
                                <template v-else-if="(pm.brand || '').toLowerCase() === 'discover'">
                                    <svg class="h-6 w-auto" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="1" y="2" width="22" height="12" rx="1.5" stroke="currentColor" stroke-width="1.2" fill="none"/><text x="12" y="10.5" font-family="system-ui, sans-serif" font-size="6" font-weight="700" fill="currentColor" text-anchor="middle">D</text></svg>
                                </template>
                                <template v-else>
                                    <svg class="h-6 w-auto" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="1" y="3" width="22" height="14" rx="2" stroke="currentColor" stroke-width="1.2" fill="none"/><path d="M1 7h22" stroke="currentColor" stroke-width="1.2"/></svg>
                                </template>
                            </span>
                            <span class="tabular-nums text-[22px] leading-none text-site-body">{{ pm.last_four }}</span>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <button
                                type="button"
                                :disabled="index === 0"
                                class="rounded p-2 text-site-body hover:bg-slate-700 disabled:opacity-40"
                                aria-label="Move up"
                                @click="moveUp(index)"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                :disabled="index === paymentMethods.length - 1"
                                class="rounded p-2 text-site-body hover:bg-slate-700 disabled:opacity-40"
                                aria-label="Move down"
                                @click="moveDown(index)"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                class="rounded p-2 text-red-400 hover:bg-slate-700"
                                aria-label="Remove"
                                @click="removeCard(pm)"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </li>
                </ul>
                <p v-if="paymentMethods.length" class="mt-2 text-xs text-slate-400">Click a card name to edit it (e.g. &quot;Personal&quot;, &quot;Work&quot;) so you can tell them apart.</p>
                <p v-if="!paymentMethods.length" class="mt-4 text-sm text-slate-400">No cards saved yet. Add one below.</p>

                <div v-if="!addCardOpen" class="mt-4">
                    <button
                        type="button"
                        class="rounded-lg bg-cta px-4 py-2.5 text-sm font-medium text-white hover:bg-cta-hover focus:outline-none focus:ring-2 focus:ring-cta"
                        @click="showAddCard"
                    >
                        Add card
                    </button>
                </div>

                <div v-else class="mt-4 rounded-lg border border-slate-600 bg-slate-800/50 p-4">
                    <h3 class="text-sm font-medium text-site-heading">New card</h3>
                    <div class="mt-3">
                        <label for="new-card-name" class="block text-xs font-medium text-site-body">Card name (optional)</label>
                        <input
                            id="new-card-name"
                            v-model="addCardName"
                            type="text"
                            maxlength="64"
                            placeholder="e.g. Personal, Work"
                            class="mt-1 block w-full max-w-xs rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:border-cta focus:outline-none focus:ring-1 focus:ring-cta"
                        />
                    </div>
                    <div ref="cardElementRef" class="mt-3 min-h-[40px] rounded border border-slate-600 bg-slate-800 px-3 py-2"></div>
                    <div class="mt-3 flex gap-2">
                        <button
                            type="button"
                            :disabled="addCardLoading"
                            class="rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white hover:bg-cta-hover disabled:opacity-60"
                            @click="confirmAddCard"
                        >
                            {{ addCardLoading ? 'Adding…' : 'Add card' }}
                        </button>
                        <button
                            type="button"
                            :disabled="addCardLoading"
                            class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-site-body hover:bg-slate-700 disabled:opacity-60"
                            @click="cancelAddCard"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </section>
        </template>
    </div>
</template>
