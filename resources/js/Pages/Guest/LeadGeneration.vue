<script setup lang="ts">
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import LeadFormCard from '@/Components/Public/LeadFormCard.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import Toast from '@/Components/Common/Toast.vue';

const props = defineProps({
    leadForm: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const branding = computed(() => (page.props.branding as any) || {});
const businessSettings = computed(() => branding.value.business_settings || {});
const landing = computed(() => ({
    ...(branding.value.landing || {}),
    ...(branding.value.business_settings || {})
}));

const logoUrl = computed(() => {
    const logo = businessSettings.value.logo_url || businessSettings.value.business_logo;
    if (!logo) return null;
    return logo.startsWith('http') || logo.startsWith('/storage') ? logo : '/storage/' + logo;
});

const logoDarkUrl = computed(() => {
    const logo = businessSettings.value.logo_dark_url;
    if (!logo) return null;
    return logo.startsWith('http') || logo.startsWith('/storage') ? logo : '/storage/' + logo;
});

const iconUrl = computed(() => {
    // Favicon serves as the icon
    const icon = businessSettings.value.favicon_url;
    if (!icon) return null;
    return icon.startsWith('http') ? icon : '/storage/' + icon;
});

const iconDarkUrl = computed(() => {
    // Favicon dark serves as the dark icon
    const icon = businessSettings.value.favicon_dark_url;
    if (!icon) return null;
    return icon.startsWith('http') ? icon : '/storage/' + icon;
});

const heroImage = computed(() => {
    const img = landing.value.landing_hero_image;
    if (!img) return null;
    return img.startsWith('http') ? img : '/storage/' + img;
});

const primaryColor = computed(() => branding.value.settings?.primary || '#179753');
</script>

<template>
    <Head :title="landing.landing_title || businessSettings.business_name || 'Lead Generation'" />
    <Toast />

    <div class="min-h-screen lg:h-screen lg:overflow-hidden flex flex-col lg:flex-row bg-white dark:bg-gray-900 font-sans text-slate-600 dark:text-slate-300 transition-colors duration-300 relative">
        <!-- Header Section -->
        <header 
            class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900"
        >
            <div class="max-w-full mx-auto px-6 sm:px-12 lg:px-16 xl:px-24 h-16 flex items-center justify-between">
                <!-- Branding Left -->
                <Link href="/" class="flex items-center gap-4 group hover:opacity-90 transition-opacity">
                    <div class="flex items-center gap-3">
                         <!-- Logo (Priority) -->
                        <div v-if="logoUrl" class="h-10 flex items-center">
                             <img :src="logoUrl" class="dark:hidden h-full w-auto object-contain" :alt="businessSettings.business_name || branding.name">
                             <img :src="logoDarkUrl || logoUrl" class="hidden dark:block h-full w-auto object-contain" :alt="businessSettings.business_name || branding.name">
                        </div>

                        <!-- Fallback: Icon (Favicon) -->
                        <div v-else-if="iconUrl || iconDarkUrl" class="w-10 h-10 bg-white dark:bg-slate-800 rounded-xl flex items-center justify-center p-1.5 shadow-lg border border-slate-200 dark:border-slate-700 transition-transform group-hover:scale-110">
                            <img :src="iconUrl" class="dark:hidden w-full h-full object-contain" :alt="branding.name">
                            <img :src="iconDarkUrl || iconUrl" class="hidden dark:block w-full h-full object-contain" :alt="branding.name">
                        </div>

                        <!-- Fallback: Letter -->
                        <div v-else class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center font-bold text-white shadow-lg shadow-primary-500/20 text-xl border border-primary-600/10">
                            {{ (businessSettings.business_name || branding.name || 'L').charAt(0) }}
                        </div>
                        
                        <!-- Name & Tagline (Always Show) -->
                        <div class="flex flex-col">
                            <span class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tighter leading-none">{{ businessSettings.business_name || branding.name }}</span>
                            <span v-if="businessSettings.tagline" class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-1">{{ businessSettings.tagline }}</span>
                        </div>
                    </div>
                </Link>

                <!-- Right Actions -->
                <div class="flex items-center gap-6">
                    <div class="hidden sm:block h-8 w-px bg-gray-200 dark:bg-gray-700 mx-2"></div>
                    <ThemeToggle class="text-slate-600 dark:text-slate-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" />
                </div>
            </div>
        </header>

        <!-- Main Content Area (Compact header space) -->
        <div class="flex-1 flex flex-col lg:flex-row mt-16 lg:h-[calc(100vh-4rem)] lg:overflow-hidden">

        <!-- Left Side: Branded Information -->
        <div class="lg:w-1/2 relative bg-slate-900 text-white flex flex-col justify-start px-8 sm:px-12 lg:px-16 xl:px-24 pt-20 pb-12 lg:pt-32 lg:pb-24 lg:h-full lg:overflow-y-auto hide-scrollbar">
            <!-- Background Image & Overlay -->
            <div class="absolute inset-0 z-0">
                <img v-if="heroImage" :src="heroImage" class="w-full h-full object-cover opacity-40 mix-blend-overlay" alt="Hero Background">
                <div v-else class="w-full h-full bg-gradient-to-br from-primary-900 to-slate-900 opacity-90"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-slate-900/50"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 max-w-xl">
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-6 leading-tight text-white drop-shadow-sm">
                    {{ landing.landing_title || 'Transform Your Business Today' }}
                </h1>
                <p class="text-lg sm:text-xl text-slate-300 mb-10 leading-relaxed max-w-lg">
                    {{ landing.landing_subtitle || 'Join thousands of satisfied customers who have revolutionized their workflow with our CRM solution.' }}
                </p>

                <!-- Feature List (Optional) -->
                <!-- Dynamic Feature List -->
                <ul class="space-y-4 text-slate-300">
                    <template v-for="i in 4" :key="i">
                        <li v-if="landing[`landing_feature_${i}_title`]" class="flex items-start">
                            <svg class="w-6 h-6 text-primary-400 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <div>
                                <h4 class="font-bold text-white">{{ landing[`landing_feature_${i}_title`] }}</h4>
                                <p v-if="landing[`landing_feature_${i}_description`]" class="text-sm text-slate-400 mt-1">
                                    {{ landing[`landing_feature_${i}_description`] }}
                                </p>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <!-- Right Side: Lead Form -->
        <div class="lg:w-1/2 flex justify-center p-4 pt-12 sm:p-8 sm:pt-16 lg:p-12 lg:pt-20 bg-gray-50 dark:bg-gray-900 lg:h-full lg:overflow-y-auto hide-scrollbar">
            <div class="w-full max-w-2xl space-y-8 my-auto">
                 <div class="lg:hidden text-center mb-8 space-y-4">
                    <div v-if="logoUrl || logoDarkUrl" class="h-12 w-auto mx-auto flex justify-center">
                        <!-- On mobile right side, we respect the current theme -->
                        <img :src="logoUrl" class="dark:hidden h-full w-auto object-contain" :alt="businessSettings.business_name">
                        <img :src="logoDarkUrl || logoUrl" class="hidden dark:block h-full w-auto object-contain" :alt="businessSettings.business_name">
                    </div>
                    <div class="flex items-center justify-center gap-3">
                         <div v-if="iconUrl || iconDarkUrl" class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center p-1.5">
                            <img :src="iconUrl" class="dark:hidden w-full h-full object-contain" :alt="branding.name">
                            <img :src="iconDarkUrl || iconUrl" class="hidden dark:block w-full h-full object-contain" :alt="branding.name">
                        </div>
                         <h2 class="text-2xl font-bold text-gray-900 dark:text-white uppercase tracking-tight">{{ businessSettings.business_name }}</h2>
                    </div>
                 </div>

                <LeadFormCard 
                    :form="props.leadForm" 
                    :branding="branding" 
                    :title-override="landing.landing_form_title"
                    :description-override="landing.landing_form_description"
                    class="bg-white dark:bg-gray-800 shadow-xl rounded-xl border border-gray-100 dark:border-gray-700 p-6 sm:p-8"
                />
            </div>
        </div>
        </div>
    </div>
</template>
