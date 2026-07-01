<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import HeroSection from '@/Components/Sections/HeroSection.vue';
import FeaturesSection from '@/Components/Sections/FeaturesSection.vue';
import StatsSection from '@/Components/Sections/StatsSection.vue';
import CtaSection from '@/Components/Sections/CtaSection.vue';

const page = usePage();

const availableComponents: Record<string, any> = {
    hero: HeroSection,
    features: FeaturesSection,
    stats: StatsSection,
    cta: CtaSection,
};

const sections = computed(() => {
    const settings = page.props.branding?.business_settings?.home_page_sections;
    if (settings) {
        try {
            return JSON.parse(settings);
        } catch (e) {
            console.error('Failed to parse home page sections', e);
            return [];
        }
    }
    // Fallback default sections if nothing is saved
    return [
        { 
            type: 'hero', 
            id: 'default-hero', 
            data: { 
                title_main: 'Manage Your Sales', 
                title_gradient: 'With Zero Gravity.', 
                subtitle: 'Nexus CRM is the first predictive sales platform that automates your follow-ups and identifies million-dollar opportunities before they disappear.', 
                cta_text: 'Start Your Free Trial', 
                cta_link: '/register'
            } 
        },
        { 
            type: 'stats', 
            id: 'default-stats', 
            data: { 
                items: [
                    { label: 'Active Users', value: '25,000', suffix: '+' },
                    { label: 'Deals Closed', value: '$1.2B', suffix: '+' },
                    { label: 'Global Uptime', value: '99.99', suffix: '%' },
                ] 
            }
        },
        { 
            type: 'features', 
            id: 'default-features', 
            data: { 
                title: 'Everything you need to scale.', 
                description: 'Stop juggling tabs and spreadsheet hacks. Nexus brings your entire revenue operations into a single, beautiful dashboard.', 
                items: [
                    { title: 'Smart Lead Capture', description: 'Automatically capture and qualify leads from any source with AI-powered scoring.', icon: 'Zap' },
                    { title: 'Interactive Deal Pipeline', description: 'Visualize your sales process and move deals through custom stages with drag-and-drop ease.', icon: 'Layout' },
                    { title: 'Instant Reminders', description: 'Never miss a follow-up with intelligent task scheduling and automated notifications.', icon: 'Clock' },
                ] 
            }
        },
        { 
            type: 'cta', 
            id: 'default-cta', 
            data: { 
                title: 'Ready to accelerate your revenue pipeline?', 
                subtitle: 'Join 2,000+ high-growth companies closing deals faster with Nexus CRM. No credit card required.', 
                button_text: 'Start for Free', 
                button_link: '/register',
                secondary_button_text: 'Contact Sales'
            } 
        }
    ];
});

onMounted(() => {
    // Initial theme check
    const isDark = document.documentElement.classList.contains('dark') || 
                   localStorage.getItem('theme') === 'dark' ||
                   (localStorage.getItem('theme') === null && window.matchMedia('(prefers-color-scheme: dark)').matches);
    
    if (isDark) {
        document.documentElement.classList.add('dark');
    }
});
</script>

<template>
    <Head :title="page.props.branding.business_settings?.landing_title || 'Nexus CRM - The Modern Sales Pipeline'" />

    <PublicLayout :full-width="true">
        <div class="min-h-screen bg-[#FDFDFC] dark:bg-slate-950 font-inter selection:bg-brand-500/30 transition-colors duration-500">
            <template v-for="section in sections" :key="section.id">
                <component 
                    :is="availableComponents[section.type]" 
                    v-if="availableComponents[section.type]"
                    :data="section.data" 
                />
            </template>
        </div>
    </PublicLayout>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');

.font-inter {
    font-family: 'Inter', sans-serif;
}
</style>
