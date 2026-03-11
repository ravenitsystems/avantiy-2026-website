<script setup>
import { RouterLink } from 'vue-router';

/** Runtime base path for landing assets so Vite does not resolve as module imports */
const landingBase = '/images/landing';

const features = [
    { title: 'All the tools to kickstart your sales', description: 'Explore our collection of robust tools and functionalities crafted to assist you in swiftly initiating your online store. This includes an AI Assistant that promptly generates product and category descriptions, as well as SEO metadata.' },
    { title: 'Scalable eCommerce', description: 'Configure personalised payment gateways, synchronise with external catalogs, or utilise any of our API-driven store customisations to provide the precise shopping experience for your clients.' },
    { title: 'High-performing stores', description: 'Achieve high-converting stores using a platform that is optimised for SEO, equipped with automatic renewal of 1-click SSL certificates, and hosted on AWS with a 99.99% uptime guarantee.' },
    { title: 'Reduce design-to-publish time', description: 'Boost your workflow efficiency by utilising reusable Custom Templates, Site Theme, Client Management, and various other tools that save you time.' },
    { title: 'Complete freedom in store design', description: 'Create high-converting online stores, product pages, and checkout screens that offer maximum flexibility, ensuring fully responsive and flawlessly designed websites.' },
    { title: '24/7 assistance and training', description: 'Gain entry to our acclaimed live assistance, delve into comprehensive onboarding and training sessions, and provide customers with a support portal that can be customized with your own branding.' },
];

const stats = [
    { value: '80%', label: 'of online stores launch in under a day' },
    { value: '40+', label: 'Payment gateways' },
    { value: '0%', label: 'commission on all online sales' },
    { value: '4.9', label: 'Clutch Rating' },
];

const highPerformingCards = [
    { title: 'Enterprise-grade site security', description: 'Stores with unparalleled security and stability — benefit from automatic SSL and AWS hosting with a 99.99% uptime guarantee.', image: 'feature-1.jpg' },
    { title: 'Store pages born SEO-ready', description: "The store's product catalog offers SEO optimization features that can be customised at the product level.", image: 'feature-2.jpg' },
    { title: 'Industry-leading Core Web Vitals', description: 'Build stores that are at the forefront of the industry in terms of Core Web Vitals and page speed.', image: 'feature-3.jpg' },
];

const syncChannels = ['TikToK Shopping', 'eBay Product Sync', 'Amazon Product Sync', 'Google Story Sync', 'Instagram Shopping', 'Facebook Shopping'];
</script>

<template>
    <div>
        <!-- Hero with image -->
        <section class="relative overflow-hidden bg-black py-16 sm:py-24 lg:py-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-cta">New Introducing AI Editor</p>
                        <h1 class="mt-2 text-4xl font-bold tracking-tight text-site-heading sm:text-5xl">
                            Create and oversee large-scale eCommerce websites.
                        </h1>
                        <p class="mt-6 max-w-xl text-lg text-site-body">
                            Develop tailor-made eCommerce websites for clients, reducing production time significantly, all while maintaining your brand identity.
                        </p>
                        <RouterLink to="/dashboard/templates" class="mt-8 inline-flex rounded-lg bg-cta px-6 py-3 text-base font-medium text-white transition-colors hover:bg-cta-hover">
                            Get Started
                        </RouterLink>
                    </div>
                    <div class="relative flex justify-center lg:justify-end">
                        <img
                            :src="`${landingBase}/ecommerce/hero-ecommerce.png`"
                            alt="Avantiy eCommerce platform"
                            class="w-full max-w-lg rounded-xl border border-slate-800 shadow-2xl object-contain"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Client logos -->
        <section class="border-t border-slate-800/80 bg-black py-12">
            <div class="mx-auto flex max-w-4xl flex-wrap items-center justify-center gap-10 px-4 sm:px-6 lg:px-8">
                <img
                    v-for="i in 5"
                    :key="i"
                    :src="`${landingBase}/clients/clients-logo-${i}.svg`"
                    :alt="`Client ${i}`"
                    class="h-8 w-auto opacity-70 grayscale hover:opacity-100 hover:grayscale-0 transition"
                />
            </div>
        </section>

        <!-- Features with promo visual -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-cta">features</h2>
                <h3 class="mt-2 text-3xl font-bold text-site-heading sm:text-4xl">
                    Supercharge your growth with a powerful eCommerce solution ready to go right out of the box.
                </h3>
                <div class="mt-12 flex justify-center">
                    <img
                        :src="`${landingBase}/ecommerce/promo.webp`"
                        alt="eCommerce features"
                        class="w-full max-w-4xl rounded-xl border border-slate-800 shadow-xl object-cover"
                    />
                </div>
                <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(feat, i) in features" :key="i" class="rounded-xl border border-slate-800/80 bg-slate-900/30 p-6">
                        <h4 class="text-lg font-semibold text-site-heading">{{ feat.title }}</h4>
                        <p class="mt-2 text-sm text-site-body">{{ feat.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="(stat, i) in stats" :key="i" class="rounded-xl border border-slate-800/80 bg-slate-900/30 px-6 py-8 text-center">
                        <p class="text-3xl font-bold text-cta">{{ stat.value }}</p>
                        <p class="mt-1 text-sm text-site-body">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Get ahead - three cards with images -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h3 class="text-3xl font-bold text-site-heading sm:text-4xl">
                    Get ahead by starting with a high-performing store
                </h3>
                <p class="mt-4 max-w-3xl text-site-body">
                    Let Avantiy take care of all the demanding tasks while you concentrate on constructing and marketing stores on a large scale. Benefit from the cutting-edge performance, privacy, and security features integrated into your store through the platform.
                </p>
                <div class="mt-12 grid gap-8 sm:grid-cols-3">
                    <RouterLink
                        v-for="(card, i) in highPerformingCards"
                        :key="i"
                        to="/dashboard/templates"
                        class="block overflow-hidden rounded-xl border border-slate-800/80 bg-slate-900/30 transition hover:border-cta/30 hover:shadow-lg hover:shadow-cta/10"
                    >
                        <img
                            :src="`${landingBase}/ecommerce/${card.image}`"
                            :alt="card.title"
                            class="h-44 w-full object-cover"
                        />
                        <div class="p-6">
                            <h4 class="font-semibold text-site-heading">{{ card.title }}</h4>
                            <p class="mt-2 text-sm text-site-body">{{ card.description }}</p>
                        </div>
                    </RouterLink>
                </div>
            </div>
        </section>

        <!-- Your products, in your clients favourite places - with multichannel image -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-cta">Grow</h2>
                        <h3 class="mt-2 text-3xl font-bold text-site-heading sm:text-4xl">
                            Your products, in your clients favourite places
                        </h3>
                        <p class="mt-4 max-w-xl text-site-body">
                            Drive sales to your store by easily syncing your products on all your favourite social channels and market places. Keep track of everything in your Avantiy dashboard with automatic product sync — saving you time but giving you endless possibilities.
                        </p>
                        <ul class="mt-6 flex flex-wrap gap-3 text-sm text-site-body">
                            <li v-for="ch in syncChannels" :key="ch" class="rounded-lg bg-slate-800/50 px-3 py-1.5">{{ ch }}</li>
                        </ul>
                        <RouterLink to="/dashboard/templates" class="mt-8 inline-flex rounded-lg bg-cta px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover">
                            Get Started Today
                        </RouterLink>
                    </div>
                    <div class="relative">
                        <img
                            :src="`${landingBase}/ecommerce/multichannel.jpg`"
                            alt="Multichannel eCommerce"
                            class="w-full rounded-xl border border-slate-800 shadow-xl object-cover"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Inventory / Never run out - with quantity image -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                    <div class="order-2 lg:order-1">
                        <img
                            :src="`${landingBase}/ecommerce/quantity-limits.jpg`"
                            alt="Inventory management"
                            class="w-full rounded-xl border border-slate-800 shadow-xl object-cover"
                        />
                    </div>
                    <div class="order-1 lg:order-2">
                        <h3 class="text-3xl font-bold text-site-heading sm:text-4xl">
                            Never run out of your clients' favourite products
                        </h3>
                        <p class="mt-4 max-w-xl text-site-body">
                            Manage your inventory like a pro. Keep on top of your products with full inventory management and low stock reminders e-mailed to you so you never forget to re-order a single item.
                        </p>
                        <ul class="mt-6 space-y-2 text-site-body">
                            <li>• Never forget to reorder a single product</li>
                            <li>• Inventory management</li>
                            <li>• Low stock reminders</li>
                        </ul>
                        <RouterLink to="/dashboard/templates" class="mt-8 inline-flex rounded-lg bg-cta px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover">
                            Get Started Today
                        </RouterLink>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <h3 class="text-3xl font-bold text-site-heading sm:text-4xl">
                    Try our powerful eCommerce tools today
                </h3>
                <p class="mt-4 text-site-body">
                    Sign up for a free trial of Avantiy today — no credit card required.
                </p>
                <RouterLink to="/dashboard/register" class="mt-8 inline-flex rounded-lg bg-cta px-6 py-3 text-base font-medium text-white transition-colors hover:bg-cta-hover">
                    Get Started for free
                </RouterLink>
                <ul class="mt-8 flex flex-wrap justify-center gap-6 text-sm text-site-body">
                    <li>Unlimited free trial</li>
                    <li>No credit card required</li>
                </ul>
            </div>
        </section>
    </div>
</template>
