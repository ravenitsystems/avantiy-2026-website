<script setup>
import { RouterLink } from 'vue-router';

/** Runtime base path for landing assets */
const landingBase = '/images/landing';

const steps = [
    { num: 1, title: 'Follow the sparkles', description: 'Follow the sparkles that let you know the AI Assistant is there to help!' },
    { num: 2, title: 'Fill in your details', description: "Add your your business or your client's business profile and brand guidelines." },
    { num: 3, title: 'Watch it happen', description: 'Click to instantly generate copy, SEO metadata, and other page components.' },
];

const features = [
    { title: 'Content creation', description: "Effortlessly create engaging, top-notch content for your clients' websites, tailored to their specific business needs." },
    { title: 'Content editing', description: 'Efficiently modify the length of content, translate text, rectify grammar or spelling mistakes, and fine-tune the writing\'s tone.' },
    { title: 'Product descriptions', description: 'Improve your eCommerce product and category page descriptions, including translating the text into another language.' },
    { title: 'Automatic context', description: 'Once you provide the business context, our AI Assistant will seamlessly incorporate it into future content prompts without any manual intervention.' },
    { title: 'Automated SEO metadata', description: 'Generate SEO metadata, such as titles and descriptions, for every page on your website effortlessly using a single centralized dashboard.' },
    { title: 'Alt text for images', description: 'Effortlessly generate accurate and comprehensive alternative text with a single click, utilizing image context, attributes, and settings.' },
    { title: 'AI-powered site sections', description: 'Create fresh website sections within seconds by merely requesting assistance from the AI Assistant.' },
    { title: 'AI-driven content collection', description: 'Accelerate the process of launching client websites by utilizing artificial intelligence to automatically collect and customize content.' },
    { title: 'Instant FAQs', description: "Create customised frequently asked questions (FAQs) within seconds, specifically designed for your client's industry and business characteristics." },
];
</script>

<template>
    <div>
        <!-- Hero with image -->
        <section class="relative overflow-hidden bg-black py-16 sm:py-24 lg:py-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                    <div>
                        <h1 class="text-4xl font-bold tracking-tight text-site-heading sm:text-5xl">
                            AI Assistant — The future of web building is here
                        </h1>
                        <p class="mt-6 max-w-xl text-lg text-site-body">
                            Revolutionise your workflows with the inclusion of a robust AI Assistant that effortlessly generates site content, optimizes pages for SEO, and accomplishes much more in a matter of seconds.
                        </p>
                        <RouterLink to="/dashboard/templates" class="mt-8 inline-flex rounded-lg bg-cta px-6 py-3 text-base font-medium text-white transition-colors hover:bg-cta-hover">
                            Get Started
                        </RouterLink>
                        <ul class="mt-12 flex flex-wrap gap-8 text-sm text-site-body">
                            <li>No credit card required</li>
                            <li>Free until you publish</li>
                            <li>Trusted by 20,000+ organizations worldwide</li>
                        </ul>
                    </div>
                    <div class="relative flex justify-center lg:justify-end">
                        <img
                            :src="`${landingBase}/ai-assistant/enhanced-ai.jpeg`"
                            alt="AI Assistant in action"
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

        <!-- Features intro + visual -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-cta">features</h2>
                <h3 class="mt-2 text-3xl font-bold text-site-heading sm:text-4xl">
                    Leverage the capabilities of artificial intelligence to create websites on a large scale.
                </h3>
                <p class="mt-4 max-w-3xl text-site-body">
                    By utilising our integrated AI Assistant, you have the ability to leave behind your most time-consuming responsibilities and concentrate on providing distinctive value to your final consumers. Transform your customer websites into dynamic entities within a matter of minutes!
                </p>
                <div class="mt-12 flex justify-center">
                    <img
                        :src="`${landingBase}/ai-assistant/build-sites-fast.webp`"
                        alt="Build full-feature sites fast"
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

        <!-- Compose / Personalize / Automate - three visuals -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h3 class="text-3xl font-bold text-site-heading sm:text-4xl">
                    Write, personalise, and automate — all in one place
                </h3>
                <p class="mt-4 max-w-3xl text-site-body">
                    Compose and edit effortlessly, customise content at scale, and streamline SEO execution with a click.
                </p>
                <div class="mt-12 grid gap-8 sm:grid-cols-3">
                    <div class="overflow-hidden rounded-xl border border-slate-800/80 bg-slate-900/30">
                        <img :src="`${landingBase}/ai-assistant/write-revise.webp`" alt="Write and revise with a click" class="h-48 w-full object-cover" />
                        <div class="p-4">
                            <h4 class="font-semibold text-site-heading">Compose and edit effortlessly</h4>
                            <p class="mt-1 text-sm text-site-body">Generate engaging content, enhance current text, and modify the wording to align with the design or capture the desired tone.</p>
                        </div>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-slate-800/80 bg-slate-900/30">
                        <img :src="`${landingBase}/ai-assistant/personalize-content.webp`" alt="Personalise content at scale" class="h-48 w-full object-cover" />
                        <div class="p-4">
                            <h4 class="font-semibold text-site-heading">Customise content at scale</h4>
                            <p class="mt-1 text-sm text-site-body">Effortlessly gather and personalise content for your client websites. Say goodbye to placeholder text and long waits for client approval.</p>
                        </div>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-slate-800/80 bg-slate-900/30">
                        <img :src="`${landingBase}/ai-assistant/automate-seo.webp`" alt="Automate SEO implementation" class="h-48 w-full object-cover" />
                        <div class="p-4">
                            <h4 class="font-semibold text-site-heading">Streamline SEO execution</h4>
                            <p class="mt-1 text-sm text-site-body">Generate keyword-rich SEO metadata in seconds — meta titles, descriptions, image alt text, and more for all site pages.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works - 3 steps -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h3 class="text-3xl font-bold text-site-heading sm:text-4xl">
                    How the AI Assistant works in simple 3 steps
                </h3>
                <p class="mt-4 text-site-body">
                    That's it! You enter the business context once, and that information is automatically incorporated to generate high-quality, hyper-relevant results. Not loving the output? Simply click again to refine!
                </p>
                <div class="mt-12 grid gap-8 sm:grid-cols-3">
                    <div v-for="step in steps" :key="step.num" class="rounded-xl border border-slate-800/80 bg-slate-900/30 p-6">
                        <span class="text-2xl font-bold text-cta">{{ step.num }}</span>
                        <h4 class="mt-2 text-lg font-semibold text-site-heading">{{ step.title }}</h4>
                        <p class="mt-2 text-sm text-site-body">{{ step.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h3 class="text-3xl font-bold text-site-heading sm:text-4xl">
                    AI Assistant frequently asked questions
                </h3>
                <p class="mt-4 text-site-body">
                    Can't find any answer for your question? Ask our
                    <a href="https://blog.avantyplc.com/" target="_blank" rel="noopener noreferrer" class="text-cta hover:underline">customer support</a>.
                </p>
                <div class="mt-12 space-y-8">
                    <div class="rounded-xl border border-slate-800/80 bg-slate-900/30 p-6">
                        <h4 class="font-semibold text-site-heading">What can you tell me about GDPR, data privacy and our integration with OpenAI?</h4>
                        <p class="mt-2 text-sm text-site-body">
                            Currently, Duda is integrating with OpenAI to provide generative AI capabilities. Any use of AI is governed by Avantiy's terms of service. Third party partner terms, such as the OpenAI terms, may also apply. Any data that is submitted to OpenAI is not used to train or improve their models. We recommend not to share any Personal Identifiable Information (PII) while using AI in the platform.
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-800/80 bg-slate-900/30 p-6">
                        <h4 class="font-semibold text-site-heading">What languages does the AI Assistant support?</h4>
                        <p class="mt-2 text-sm text-site-body">
                            Since we are integrating with OpenAI, we are able to support all languages that OpenAI supports. However, the content quality may vary in other languages. We are currently working on improving our prompts for additional languages.
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-800/80 bg-slate-900/30 p-6">
                        <h4 class="font-semibold text-site-heading">Will AI generated content impact search engine rankings?</h4>
                        <p class="mt-2 text-sm text-site-body">
                            Google have stated that they reward high quality content however it is produced. Avantiy always recommends that a human will review and edit all AI generated content, as the human touch still plays a critical role in generating high quality content.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="border-t border-slate-800/80 bg-black py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <h3 class="text-3xl font-bold text-site-heading sm:text-4xl">
                    Try Avantiy's AI Assistant and save yourself hours
                </h3>
                <p class="mt-4 text-site-body">
                    Log in to your Avantiy account or start a free trial to get started.
                </p>
                <RouterLink to="/dashboard/templates" class="mt-8 inline-flex rounded-lg bg-cta px-6 py-3 text-base font-medium text-white transition-colors hover:bg-cta-hover">
                    Get Started Today
                </RouterLink>
            </div>
        </section>
    </div>
</template>
