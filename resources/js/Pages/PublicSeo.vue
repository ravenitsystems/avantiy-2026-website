<script setup>
import { RouterLink } from 'vue-router';

/** Runtime base path for landing assets */
const landingBase = '/images/landing';

const features = [
    { title: 'Free SSL', description: 'Avantiy automatically provides a TLS (SSL) Certificate for every website, keeping data secure and giving your site an SEO advantage.' },
    { title: 'Rock-Solid Hosting, Always Up', description: "With Avantiy's 99.9% uptime, your sites stay online, get indexed, and you can sleep easy knowing we've got it covered." },
    { title: 'Responsive design', description: 'Every Avantiy site is mobile-ready from the start, giving you full design flexibility on desktop, tablet, and smartphone.' },
    { title: 'JavaScript-Free Content', description: 'Avantiy delivers fully server-side rendered (SSR) content, making it easy for search engines to crawl and index your site.' },
    { title: 'Automatic Sitemaps for Better Indexing', description: 'Avantiy instantly generates sitemaps and Robots.txt files for every site, covering all your website pages for seamless search engine indexing.' },
    { title: 'Speak to Every Customer', description: 'Avantiy takes care of hreflang tags for you, so search engines can correctly identify all multi-language pages on your website.' },
    { title: 'Automatic Sitemap Submission', description: 'Sitemaps are instantly submitted and updated with every change you make to your site, keeping search engines in the loop.' },
    { title: 'Automatic Canonical Tags', description: 'Avantiy creates canonical tags for you, following SEO best practices and handling everything behind the scenes.' },
    { title: 'Built-In Schema Support', description: 'Easily add schema to business info, FAQs, videos, product pages, blogs, and more — all in just a few clicks.' },
    { title: 'Custom Code Support', description: 'Avantiy gives you full flexibility to add your own custom code anywhere on your site — including custom schema markup.' },
    { title: 'Full URL Control', description: "Take full control of every page's URL — no restrictions, no fixed structures. Create clean, custom URLs with complete freedom." },
    { title: 'Full Redirect Control', description: 'Avantiy lets you set 301 or 302 redirects for any page — whenever you need. You can even upload redirects in bulk with a simple CSV file.' },
];

const integrationLogoCount = 15;
</script>

<template>
    <div>
        <!-- Hero with image -->
        <section class="relative overflow-hidden bg-black py-16 sm:py-24 lg:py-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-cta">Game changing SEO out of the box</p>
                        <h1 class="mt-2 text-4xl font-bold tracking-tight text-site-heading sm:text-5xl">
                            Build Beautiful Websites. We'll Handle the SEO.
                        </h1>
                        <p class="mt-6 max-w-xl text-lg text-site-body">
                            Avantiy's AI-powered SEO takes care of the hard stuff — so you can focus on growing your brand.
                        </p>
                        <RouterLink to="/dashboard/templates" class="mt-8 inline-flex rounded-lg bg-cta px-6 py-3 text-base font-medium text-white transition-colors hover:bg-cta-hover">
                            Get Started Free
                        </RouterLink>
                        <ul class="mt-12 flex flex-wrap gap-8 text-sm text-site-body">
                            <li>No credit card required</li>
                            <li>Free until you publish</li>
                            <li>Trusted by 20,000+ designers worldwide</li>
                        </ul>
                    </div>
                    <div class="relative flex justify-center lg:justify-end">
                        <img
                            :src="`${landingBase}/seo/seo-information.webp`"
                            alt="SEO dashboard"
                            class="w-full max-w-lg rounded-xl border border-slate-800 shadow-2xl object-cover"
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

        <!-- Speed - Blazing fast -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-cta">Speed</h2>
                        <h3 class="mt-2 text-3xl font-bold text-site-heading sm:text-4xl">
                            Blazing-Fast Performance, Built In
                        </h3>
                        <p class="mt-4 max-w-xl text-site-body">
                            Deliver ultra-fast websites that excel in Core Web Vitals and Page Speed — right out of the box. Backed by enterprise-grade security, robust infrastructure, automatic sitemaps, SSL encryption, and structured data, Avantiy gives you everything you need to build top-ranking sites quickly and confidently.
                        </p>
                        <RouterLink to="/dashboard/templates" class="mt-6 inline-flex rounded-lg bg-cta px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover">
                            Get Started
                        </RouterLink>
                    </div>
                    <div class="relative flex justify-center lg:justify-end">
                        <img
                            :src="`${landingBase}/seo/blazing-fast.webp`"
                            alt="Blazing fast performance"
                            class="w-full max-w-lg rounded-xl border border-slate-800 shadow-xl object-cover"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- SEO Audits -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                    <div class="order-2 lg:order-1">
                        <img
                            :src="`${landingBase}/seo/seo-audit.webp`"
                            alt="SEO audit dashboard"
                            class="w-full max-w-lg rounded-xl border border-slate-800 shadow-xl object-cover mx-auto lg:mx-0"
                        />
                    </div>
                    <div class="order-1 lg:order-2">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-cta">Audit like a boss</h2>
                        <h3 class="mt-2 text-3xl font-bold text-site-heading sm:text-4xl">
                            SEO Audits Made Simple.
                        </h3>
                        <p class="mt-4 max-w-xl text-site-body">
                            With Avantiy's SEO Overview dashboard, you can quickly spot and fix missing alt text, broken links, metadata issues, and more — all from a single, easy-to-use panel that's hidden from your clients. Optimise faster, or unlock upsell opportunities, in just a few clicks.
                        </p>
                        <RouterLink to="/dashboard/templates" class="mt-6 inline-flex rounded-lg border border-slate-600 px-5 py-2.5 text-sm font-medium text-site-heading transition-colors hover:bg-white/10">
                            Learn More
                        </RouterLink>
                    </div>
                </div>
            </div>
        </section>

        <!-- AI SEO -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-cta">AI SEO</h2>
                        <h3 class="mt-2 text-3xl font-bold text-site-heading sm:text-4xl">
                            AI-Powered SEO, Done for You
                        </h3>
                        <p class="mt-4 max-w-xl text-site-body">
                            Avantiy's unique AI SEO Assistant helps you instantly create localised, high-performing metadata across your entire website from one simple dashboard. Generate SEO-friendly titles and meta descriptions in seconds — all aligned with best practices to help you rank higher, faster.
                        </p>
                        <RouterLink to="/dashboard/templates" class="mt-6 inline-flex rounded-lg bg-cta px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover">
                            Get Started
                        </RouterLink>
                    </div>
                    <div class="relative flex justify-center lg:justify-end">
                        <img
                            :src="`${landingBase}/seo/ai-powered-seo.png`"
                            alt="AI-powered SEO"
                            class="w-full max-w-lg rounded-xl border border-slate-800 shadow-xl object-cover"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Fix broken links + Bulk alt text - two cards with images -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 sm:grid-cols-2">
                    <div class="overflow-hidden rounded-xl border border-slate-800/80 bg-slate-900/30">
                        <img
                            :src="`${landingBase}/seo/fix-broken-links.webp`"
                            alt="Fix broken links"
                            class="h-52 w-full object-cover"
                        />
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-site-heading">Fix Broken Links in One Click</h3>
                            <p class="mt-2 text-site-body">
                                Easily check the health of every link on your website — both internal and external — from one simple dashboard. Spot issues fast and fix them in a single click, all within Avantiy's SEO Overview.
                            </p>
                            <RouterLink to="/dashboard/templates" class="mt-4 inline-flex rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-cta-hover">
                                Get Started
                            </RouterLink>
                        </div>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-slate-800/80 bg-slate-900/30">
                        <img
                            :src="`${landingBase}/seo/bulk-alt-text.webp`"
                            alt="Bulk AI-generated alt text"
                            class="h-52 w-full object-cover"
                        />
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-site-heading">Bulk AI-Generated Alt Text in One Click</h3>
                            <p class="mt-2 text-site-body">
                                Generate detailed, relevant alt text for every image on your site with a single click. Avantiy's AI scans each image's content and context to create descriptions that boost organic traffic and improve accessibility — all in seconds.
                            </p>
                            <RouterLink to="/dashboard/templates" class="mt-4 inline-flex rounded-lg bg-cta px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-cta-hover">
                                Get Started
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features grid -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h3 class="text-3xl font-bold text-site-heading sm:text-4xl">
                    Features that put you ahead of your competitors
                </h3>
                <p class="mt-4 max-w-3xl text-site-body">
                    SEO tools that give you a real advantage. Take control of meta tags, URLs, image optimisation, and schema markup—everything you need to improve visibility, drive organic traffic, and outperform your competitors in search results.
                </p>
                <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(feat, i) in features" :key="i" class="rounded-xl border border-slate-800/80 bg-slate-900/30 p-6">
                        <h4 class="font-semibold text-site-heading">{{ feat.title }}</h4>
                        <p class="mt-2 text-sm text-site-body">{{ feat.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Integrations -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h3 class="text-3xl font-bold text-site-heading sm:text-4xl">
                    Integrate apps with just a few clicks
                </h3>
                <p class="mt-4 max-w-2xl text-site-body">
                    Connect your existing apps and workflows with pre-made integrations available for 400+ popular apps, plus custom integrations for more advanced users.
                </p>
                <div class="mt-12 flex flex-wrap items-center justify-center gap-8">
                    <img
                        v-for="i in integrationLogoCount"
                        :key="i"
                        :src="`${landingBase}/integrations/integrate-logo-${i}.svg`"
                        :alt="`Integration ${i}`"
                        class="h-10 w-auto opacity-70 hover:opacity-100 transition"
                    />
                </div>
                <RouterLink to="/dashboard/templates" class="mt-10 inline-flex rounded-lg bg-cta px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover">
                    Get Started
                </RouterLink>
            </div>
        </section>
    </div>
</template>
