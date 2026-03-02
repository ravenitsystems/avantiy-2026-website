<script setup>
import { ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PaymentMethods from './PaymentMethods.vue';

const route = useRoute();
const router = useRouter();

const TABS = [
    { id: 'account-status', label: 'Account Status' },
    { id: 'invoices', label: 'Invoices' },
    { id: 'payment-methods', label: 'Payment Methods' },
];

const currentTab = ref(route.query.tab && TABS.some((t) => t.id === route.query.tab) ? route.query.tab : 'account-status');

watch(
    () => route.query.tab,
    (tab) => {
        if (tab && TABS.some((t) => t.id === tab)) {
            currentTab.value = tab;
        }
    }
);

function setTab(id) {
    currentTab.value = id;
    router.replace({ query: { ...route.query, tab: id } });
}

const isActive = (id) => currentTab.value === id;
</script>

<template>
    <div class="max-w-3xl">
        <h1 class="text-xl font-semibold text-site-heading">Billing</h1>
        <p class="mt-1 text-sm text-site-body">
            Manage your account status, invoices, and payment methods.
        </p>

        <!-- Tabs (style matches Websites page) -->
        <div class="mt-6 border-b border-slate-800">
            <nav class="-mb-px flex gap-6" aria-label="Tabs" role="tablist">
                <button
                    v-for="tab in TABS"
                    :key="tab.id"
                    type="button"
                    role="tab"
                    :aria-selected="isActive(tab.id)"
                    class="border-b-2 py-3 text-sm font-medium transition-colors"
                    :class="isActive(tab.id)
                        ? 'border-site-accent text-site-heading'
                        : 'border-transparent text-site-body hover:border-slate-600 hover:text-site-heading'"
                    @click="setTab(tab.id)"
                >
                    {{ tab.label }}
                </button>
            </nav>
        </div>

        <!-- Tab panels -->
        <div class="mt-6 min-h-[200px]">
            <div v-show="currentTab === 'account-status'" role="tabpanel" class="space-y-4">
                <section class="rounded-xl border border-slate-700 bg-slate-900/50 p-6">
                    <h2 class="text-lg font-medium text-site-heading">Account status</h2>
                    <p class="mt-1 text-sm text-site-body">
                        Your account is in good standing. You can manage your payment methods and view invoices from the tabs above.
                    </p>
                </section>
            </div>

            <div v-show="currentTab === 'invoices'" role="tabpanel" class="space-y-4">
                <section class="rounded-xl border border-slate-700 bg-slate-900/50 p-6">
                    <h2 class="text-lg font-medium text-site-heading">Invoices</h2>
                    <p class="mt-1 text-sm text-site-body">
                        Your billing invoices will appear here. There are no invoices yet.
                    </p>
                </section>
            </div>

            <div v-show="currentTab === 'payment-methods'" role="tabpanel">
                <PaymentMethods :embedded="true" />
            </div>
        </div>
    </div>
</template>
