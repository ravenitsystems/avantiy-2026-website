<script setup>
import { ref } from 'vue';
import { RouterLink } from 'vue-router';

const billingPeriod = ref('yearly');

const plans = [
    {
        name: 'Personal',
        tagline: 'Turn your dreams into reality',
        monthly: 24.95,
        yearly: 22,
        yearlyLabel: 'billed annually',
        features: [
            'Unlimited storage & bandwidth',
            'SSL Encryption',
            'AI Building Assistant',
            'AI SEO Assistant',
            'AI Text Assistant',
            'Dynamic Pages',
            'AWS Hosting 99.9% uptime',
            'Priority supports',
        ],
    },
    {
        name: 'Business',
        tagline: '100 Product Store & Bookings',
        monthly: 39.95,
        yearly: 35,
        yearlyLabel: 'billed annually',
        features: [
            'Sell services',
            'Product search',
            'Product import & export',
            'Inventory management',
            'Accept Bookings',
            '2 product customisations',
            '8 Variation per product',
            'Everything from Personal',
        ],
    },
    {
        name: 'Business Plus',
        tagline: '1000 Product Store',
        monthly: 54.95,
        yearly: 50,
        yearlyLabel: 'billed annually',
        features: [
            'Sell subscriptions',
            'Automatic shipping',
            'Product sorting & filtering',
            'Automatic taxes',
            'Sell digital products',
            '10 product customisations',
            '300 variations per product',
            'Everything Business',
        ],
    },
];

const faqs = [
    { q: 'How long will my free trial last?', a: 'Our free trial comes with no time limits, giving you the flexibility to build as many sites as you need for as long as it takes. Each site can be published whenever you\'re ready, ensuring you have all the time you need to perfect your projects.' },
    { q: 'How are plans billed?', a: 'Each site you build is billed monthly or yearly, depending on the preference you select when you publish your website. This gives you the flexibility to choose a payment schedule that best fits your needs.' },
    { q: 'Can I connect my own domain name?', a: 'Yes, you can connect your own domain name. When you publish your site, we will provide you with the required DNS details to easily connect your domain name to your new website.' },
    { q: 'Can I change a domain name?', a: 'Yes, you can change your domain name at any time. This can be conveniently done right from within the editor, allowing you to update your site\'s domain as needed without any hassle.' },
    { q: 'Do my websites have to be hosted with Avantiy?', a: 'Yes, all websites built on the Avantiy platform are hosted on our servers, which utilize Amazon Web Services (AWS) to ensure a 99.999% uptime. While you cannot import code from external sources, you have the flexibility to export code from Avantiy at any time.' },
    { q: 'What CDN does Avantiy use?', a: "All Avantiy websites are served via Amazon's CloudFront CDN, ensuring lightning-fast loading times for your sites." },
];
</script>

<template>
    <div>
        <section class="relative overflow-hidden bg-black py-16 sm:py-24 lg:py-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl font-bold tracking-tight text-site-heading sm:text-5xl">
                    Transparent Pricing
                </h1>
                <p class="mt-4 text-lg text-site-body">Pick Your Perfect Plan</p>
                <div class="mt-8 flex justify-center gap-4">
                    <button
                        type="button"
                        class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                        :class="billingPeriod === 'monthly' ? 'bg-cta text-white' : 'text-site-body hover:bg-white/10'"
                        @click="billingPeriod = 'monthly'"
                    >
                        Monthly
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                        :class="billingPeriod === 'yearly' ? 'bg-cta text-white' : 'text-site-body hover:bg-white/10'"
                        @click="billingPeriod = 'yearly'"
                    >
                        Yearly
                    </button>
                </div>
            </div>
        </section>

        <section class="border-t border-slate-800/80 bg-black py-12 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 lg:grid-cols-3">
                    <div
                        v-for="plan in plans"
                        :key="plan.name"
                        class="rounded-xl border border-slate-800/80 bg-slate-900/30 p-8"
                    >
                        <div class="flex flex-col items-baseline justify-between gap-4">
                            <div>
                                <p class="text-2xl font-bold text-site-heading">
                                    £ {{ billingPeriod === 'yearly' ? plan.yearly : plan.monthly }}
                                    <span class="text-base font-normal text-site-body">/month</span>
                                </p>
                                <p v-if="billingPeriod === 'yearly'" class="text-sm text-site-body">{{ plan.yearlyLabel }}</p>
                            </div>
                            <h3 class="text-xl font-bold text-site-heading">{{ plan.name }}</h3>
                            <p class="text-sm text-site-body">{{ plan.tagline }}</p>
                        </div>
                        <ul class="mt-6 space-y-3 text-sm text-site-body">
                            <li v-for="(feat, i) in plan.features" :key="i">• {{ feat }}</li>
                        </ul>
                        <RouterLink to="/dashboard/templates" class="mt-8 block w-full rounded-lg bg-cta py-3 text-center text-sm font-medium text-white transition-colors hover:bg-cta-hover">
                            Get Started
                        </RouterLink>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-site-heading">Compare plan details</h2>
                <p class="mt-4 text-site-body">Start building your site for free. Upgrade to unlock more features.</p>
            </div>
        </section>

        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-2xl font-bold text-site-heading">Trusted by 30,000+ businesses.</h2>
                <p class="mt-4 text-site-body">
                    Thousands of startups and organizations use Block to improve the productivity of their sales team and create more opportunities.
                </p>
            </div>
        </section>

        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-site-heading">Frequently asked questions</h2>
                <p class="mt-2 text-site-body">Can't find any answer for your question? Ask our customer support</p>
                <div class="mt-12 space-y-8">
                    <div v-for="(faq, i) in faqs" :key="i" class="border-b border-slate-800/80 pb-8">
                        <h3 class="font-semibold text-site-heading">{{ faq.q }}</h3>
                        <p class="mt-2 text-sm text-site-body">A: {{ faq.a }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
