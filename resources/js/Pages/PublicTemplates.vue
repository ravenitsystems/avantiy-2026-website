<script setup>
import { RouterLink } from 'vue-router';

const landingBase = '/images/landing';

const templateCards = [
    { id: 'saas-demo', name: 'SaaS Demo Landing Page', image: 'templates/card-saas-demo.png' },
    { id: 'simple-editor', name: 'Simple Editor 2.0', image: 'templates/card-simple-editor.png' },
    { id: 'law-firm', name: 'Law Firm', image: 'templates/card-law-firm.png' },
    { id: 'mental-health', name: 'Mental Health Professional', image: 'templates/card-mental-health.png' },
    { id: 'early-years', name: 'Early Years School', image: 'templates/card-early-years.png' },
    { id: 'digital-marketing', name: 'Digital Marketing Agency', image: 'templates/card-digital-marketing.png' },
];
</script>

<template>
    <div>
        <section class="relative overflow-hidden bg-black py-16 sm:py-24 lg:py-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-bold tracking-tight text-site-heading sm:text-5xl">
                    Explore Our Professional Website Templates
                </h1>
                <p class="mt-6 max-w-2xl text-lg text-site-body">
                    Effortlessly bring your vision to life with our collection of modern, fully customisable templates. Whether you're building an online store, portfolio, or business site, our designs are crafted to help you launch faster and stand out online.
                </p>
                <RouterLink to="/dashboard/templates" class="mt-8 inline-flex rounded-lg bg-cta px-6 py-3 text-base font-medium text-white transition-colors hover:bg-cta-hover">
                    Get Started
                </RouterLink>
            </div>
        </section>

        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="card in templateCards"
                        :key="card.id"
                        class="overflow-hidden rounded-xl border border-slate-800/80 bg-slate-900/30 transition hover:border-cta/30 hover:shadow-lg hover:shadow-cta/10"
                    >
                        <img
                            :src="`${landingBase}/${card.image}`"
                            :alt="card.name"
                            class="w-full border-b border-slate-800/80 object-cover"
                        />
                        <div class="p-4">
                            <h3 class="font-semibold text-site-heading">{{ card.name }}</h3>
                            <RouterLink to="/dashboard/templates" class="mt-2 inline-block text-sm font-medium text-cta hover:underline">
                                Get Started
                            </RouterLink>
                        </div>
                    </div>
                </div>
                <div class="mt-10 text-center">
                    <RouterLink to="/dashboard/templates" class="inline-flex rounded-lg bg-cta px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover">
                        View all templates
                    </RouterLink>
                </div>
            </div>
        </section>
    </div>
</template>
