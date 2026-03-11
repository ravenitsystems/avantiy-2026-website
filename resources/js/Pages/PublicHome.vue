<script setup>
import { RouterLink } from 'vue-router';

/** Base URL for landing assets (public folder). Kept as a runtime value so Vite does not resolve these paths as module imports. */
const landingBase = '/images/landing';

const trustBadges = [
    { text: 'No credit card required' },
    { text: 'Free until you publish' },
    { text: 'Trusted by 20,000+ organisations worldwide' },
];

const planFeatures = [
    {
        title: 'Templates & Sections',
        description: 'Choose from our selection of categorised templates and sections on our website builder, or customise designs that you can save, share with your team, and reuse for multiple projects.',
    },
    {
        title: 'AI Content Assistant',
        description: 'Generate new content, check grammar and spelling, and edit or translate existing copy instantly, all with the convenience of an AI assistant integrated directly into the text editor.',
    },
    {
        title: 'Connected content library',
        description: 'Witness the seamless flow of changes from your datasets as they are automatically pushed to any connected widget on your site.',
    },
];

const performanceFeatures = [
    {
        title: 'Performance',
        description: 'Experience unparalleled performance with the ultimate website builder for achieving high Core Web Vital scores in the industry.',
    },
    {
        title: 'SEO',
        description: 'Build SEO-ready websites effortlessly with automatic site maps, free SSL, local business schema, and AI-assisted metadata.',
    },
    {
        title: 'Reliability',
        description: "Enjoy reliable hosting on AWS's secure, trusted, cloud-based solution with 99.9% uptime and no storage or bandwidth limits.",
    },
];

const templateCards = [
    { id: 'saas-demo', name: 'SaaS Demo Landing Page', image: 'templates/card-saas-demo.png' },
    { id: 'simple-editor', name: 'Simple Editor 2.0', image: 'templates/card-simple-editor.png' },
    { id: 'law-firm', name: 'Law Firm', image: 'templates/card-law-firm.png' },
    { id: 'mental-health', name: 'Mental Health Professional', image: 'templates/card-mental-health.png' },
    { id: 'early-years', name: 'Early Years School', image: 'templates/card-early-years.png' },
    { id: 'digital-marketing', name: 'Digital Marketing Agency', image: 'templates/card-digital-marketing.png' },
];

const dynamicPageFeatures = [
    {
        title: 'Internal and external collections',
        description: 'Store your content in Internal Collections within the Avantiy editor or in external collections such as Google Sheet, Airtable, or other external databases. This will allow you to use the content in Dynamic Pages.',
    },
    {
        title: 'Built-in CMS',
        description: "Easily build and manage Dynamic Pages using Avantiy's own built-in CMS, without the need for any design customization.",
    },
    {
        title: 'Ready-to-use dynamic pages',
        description: "To enhance your website, simply add a pre-designed Dynamic Page that is already linked to an Internal Collection. Once added, you can easily update it with your own content.",
    },
];

const uxFeatures = [
    { title: 'eCommerce', description: 'Boost sales by utilising highly-converting online stores that offer multiple payment providers, effective marketing tools, and seamless omnichannel integrations.' },
    { title: "Membership's", description: 'Build websites that not only build communities but also generate leads. We achieve this by offering gated content exclusively for members right inside the platform.' },
    { title: 'Widgets', description: 'Select from a diverse range of over 50 widgets and enhance conversions with powerful widgets such as popups, maps, booking tools, payment solutions, and customer reviews.' },
    { title: 'Custom Widgets', description: 'Create your own custom widgets that seamlessly integrate into the Avantiy platform, providing users with captivating experiences that take your website further.' },
    { title: 'Apps', description: 'Boost your recurring revenue by integrating powerful apps into your website offering additional services like, bookings, events, CRM chatbots, marketing and much more.' },
    { title: 'Dynamic Content', description: 'Build powerful websites that are driven by databases, offering a built-in content management system (CMS), utilise third-party datasets like Google spreadsheets, Airtable, and more.' },
];

const integrationLogoCount = 15;
</script>

<template>
    <div>
        <!-- Hero -->
        <section class="relative overflow-hidden bg-black">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-32">
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-site-heading sm:text-5xl md:text-6xl">
                        Build your next website even faster.
                    </h1>
                    <p class="mx-auto mt-6 max-w-2xl text-lg text-site-body">
                        Discover time-saving tools that improve productivity and collaboration. Use AI tools, pre-designed elements, and content import to quickly create and share designs, and build websites in minutes.
                    </p>
                    <RouterLink
                        to="/dashboard/templates"
                        class="mt-8 inline-flex rounded-lg bg-cta px-6 py-3 text-base font-medium text-white transition-colors hover:bg-cta-hover"
                    >
                        Get Started
                    </RouterLink>
                    <ul class="mt-12 flex flex-wrap items-center justify-center gap-8 text-sm text-site-body">
                        <li v-for="(badge, i) in trustBadges" :key="i">{{ badge.text }}</li>
                    </ul>
                </div>
                <div class="mt-12 flex justify-center">
                    <img
                        :src="`${landingBase}/hero/img_header_desktop.webp`"
                        alt="Website builder interface"
                        class="w-full max-w-5xl rounded-xl border border-slate-800 shadow-2xl"
                    />
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
                    :alt="`Client logo ${i}`"
                    class="h-8 w-auto opacity-70 grayscale hover:opacity-100 hover:grayscale-0"
                />
            </div>
        </section>

        <!-- Visualise & plan -->
        <section id="products" class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-cta">Visualise & plan</h2>
                    <h3 class="mt-2 text-3xl font-bold text-site-heading sm:text-4xl">
                        Revolutionise your approach to website development and design
                    </h3>
                    <p class="mx-auto mt-4 max-w-3xl text-lg text-site-body">
                        Discover productivity-enhancing tools that can reduce build times by 50% and empower your team to collaborate more effectively. Take advantage of native AI tools and pre-designed elements, create designs that can be easily shared, collect and import content seamlessly, and build websites that are ready to launch within minutes.
                    </p>
                    <RouterLink
                        to="/dashboard/templates"
                        class="mt-6 inline-flex rounded-lg bg-cta px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover"
                    >
                        Get Started
                    </RouterLink>
                </div>
                <div class="mt-16 grid gap-8 sm:grid-cols-3">
                    <div
                        v-for="(feat, i) in planFeatures"
                        :key="i"
                        class="rounded-xl border border-slate-800/80 bg-slate-900/30 p-6"
                    >
                        <h4 class="text-lg font-semibold text-site-heading">{{ feat.title }}</h4>
                        <p class="mt-2 text-sm text-site-body">{{ feat.description }}</p>
                    </div>
                </div>
                <div class="mt-12 flex justify-center">
                    <img
                        :src="`${landingBase}/sections/section2.webp`"
                        alt="Design and planning tools"
                        class="w-full max-w-4xl rounded-xl border border-slate-800 shadow-xl"
                    />
                </div>
            </div>
        </section>

        <!-- Performance -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-cta">Performance</h2>
                    <h3 class="mt-2 text-3xl font-bold text-site-heading sm:text-4xl">
                        Unlock exceptional results with our high-performing website builder.
                    </h3>
                    <p class="mx-auto mt-4 max-w-3xl text-lg text-site-body">
                        Create stable and secure websites effortlessly, setting the industry standard for speed, performance, and SEO. But don't just take our word for it. Since Google introduced its Core Web Vitals user experience metrics in 2021, Avantiy has consistently boasted the highest percentage of high-scoring websites in the industry. And even with the introduction of new metrics like INP, Avantiy remains at the forefront.
                    </p>
                    <RouterLink
                        to="/dashboard/templates"
                        class="mt-6 inline-flex rounded-lg bg-cta px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover"
                    >
                        Get Started
                    </RouterLink>
                </div>
                <div class="mt-16 grid gap-8 sm:grid-cols-3">
                    <div
                        v-for="(feat, i) in performanceFeatures"
                        :key="i"
                        class="rounded-xl border border-slate-800/80 bg-slate-900/30 p-6"
                    >
                        <h4 class="text-lg font-semibold text-site-heading">{{ feat.title }}</h4>
                        <p class="mt-2 text-sm text-site-body">{{ feat.description }}</p>
                    </div>
                </div>
                <div class="mt-12 flex justify-center">
                    <img
                        :src="`${landingBase}/sections/build-websites-conversions.webp`"
                        alt="Performance and conversions"
                        class="w-full max-w-4xl rounded-xl border border-slate-800 shadow-xl"
                    />
                </div>
            </div>
        </section>

        <!-- Stunning Landing Pages -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h3 class="text-3xl font-bold text-site-heading sm:text-4xl">
                        Stunning Landing Pages
                    </h3>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-site-body">
                        Avantiy's templates comes with everything you need to get your website off the ground.
                    </p>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
                            <h4 class="font-semibold text-site-heading">{{ card.name }}</h4>
                        </div>
                    </div>
                </div>
                <div class="mt-10 text-center">
                    <RouterLink
                        to="/dashboard/templates"
                        class="inline-flex rounded-lg bg-cta px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover"
                    >
                        Get Started
                    </RouterLink>
                </div>
            </div>
        </section>

        <!-- Dynamic Pages -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h3 class="text-3xl font-bold text-site-heading sm:text-4xl">
                        Build multiple-pages with Dynamic Pages and Collections.
                    </h3>
                    <p class="mx-auto mt-4 max-w-3xl text-lg text-site-body">
                        Generate multiple pages of content quickly and efficiently using a single design. This is useful for various purposes such as real estate listings, recipe pages, portfolios, catalogs, and directories.
                    </p>
                </div>
                <div class="mt-16 grid gap-8 sm:grid-cols-3">
                    <RouterLink
                        v-for="(feat, i) in dynamicPageFeatures"
                        :key="i"
                        to="/dashboard/templates"
                        class="block rounded-xl border border-slate-800/80 bg-slate-900/30 p-6 transition hover:border-cta/30 hover:bg-slate-900/50"
                    >
                        <h4 class="text-lg font-semibold text-site-heading">{{ feat.title }}</h4>
                        <p class="mt-2 text-sm text-site-body">{{ feat.description }}</p>
                    </RouterLink>
                </div>
                <div class="mt-12 flex justify-center">
                    <img
                        :src="`${landingBase}/sections/cms-collections.webp`"
                        alt="CMS and collections"
                        class="w-full max-w-4xl rounded-xl border border-slate-800 shadow-xl"
                    />
                </div>
            </div>
        </section>

        <!-- User Experience -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-cta">User Experience</h2>
                    <h3 class="mt-2 text-3xl font-bold text-site-heading sm:text-4xl">
                        Deliver exceptional user experiences with websites that convert at a high rate.
                    </h3>
                    <p class="mx-auto mt-4 max-w-3xl text-lg text-site-body">
                        Turn websites into valuable digital assets that enhance brand awareness, boost conversions, and drive sales. With our professional website builder. Create websites that provide visitors with exactly what they're looking for.
                    </p>
                    <RouterLink
                        to="/dashboard/templates"
                        class="mt-6 inline-flex rounded-lg bg-cta px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover"
                    >
                        Get Started
                    </RouterLink>
                </div>
                <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(feat, i) in uxFeatures"
                        :key="i"
                        class="rounded-xl border border-slate-800/80 bg-slate-900/30 p-6"
                    >
                        <h4 class="text-lg font-semibold text-site-heading">{{ feat.title }}</h4>
                        <p class="mt-2 text-sm text-site-body">{{ feat.description }}</p>
                    </div>
                </div>
                <div class="mt-12 flex justify-center">
                    <img
                        :src="`${landingBase}/sections/website-builder-guidelines.webp`"
                        alt="User experience"
                        class="w-full max-w-4xl rounded-xl border border-slate-800 shadow-xl"
                    />
                </div>
            </div>
        </section>

        <!-- Integrations -->
        <section id="contact" class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h3 class="text-3xl font-bold text-site-heading sm:text-4xl">
                        Integrate apps with just a few clicks
                    </h3>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-site-body">
                        Connect your existing apps and workflows with pre-made integrations available for 400+ popular apps, plus custom integrations for more advanced users.
                    </p>
                </div>
                <div class="mt-12 flex flex-wrap items-center justify-center gap-8">
                    <img
                        v-for="i in integrationLogoCount"
                        :key="i"
                        :src="`${landingBase}/integrations/integrate-logo-${i}.svg`"
                        :alt="`Integration ${i}`"
                        class="h-10 w-auto opacity-70 hover:opacity-100"
                    />
                </div>
                <div class="mt-10 text-center">
                    <RouterLink
                        to="/dashboard/templates"
                        class="inline-flex rounded-lg bg-cta px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-cta-hover"
                    >
                        Get Started
                    </RouterLink>
                </div>
            </div>
        </section>
    </div>
</template>
