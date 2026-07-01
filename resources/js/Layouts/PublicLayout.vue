<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { login, register, contact, leadGeneration } from '@/routes';
import { dashboard as customerDashboard } from '@/routes/customer';
import { dashboard as adminDashboard } from '@/routes/admin';
import { privacy, terms, about } from '@/routes/legal';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import Toast from '@/Components/Common/Toast.vue';
import { computed, ref } from 'vue';
import { Menu, X } from 'lucide-vue-next';

const props = defineProps({
    fullWidth: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const businessSettings = computed(() => (page.props.branding as any)?.business_settings || {});

const logoUrl = computed(() => {
    const logo = businessSettings.value.logo_url;
    if (!logo) return null;
    return logo.startsWith('http') ? logo : '/storage/' + logo;
});

const logoDarkUrl = computed(() => {
    const logo = businessSettings.value.logo_dark_url;
    if (!logo) return null;
    return logo.startsWith('http') ? logo : '/storage/' + logo;
});

const iconUrl = computed(() => {
    const icon = businessSettings.value.favicon_url;
    if (!icon) return null;
    return icon.startsWith('http') ? icon : '/storage/' + icon;
});

const iconDarkUrl = computed(() => {
    const icon = businessSettings.value.favicon_dark_url;
    if (!icon) return null;
    return icon.startsWith('http') ? icon : '/storage/' + icon;
});

const dashboardUrl = computed(() => {
    if (!user.value) return '#';
    return user.value.type === 'admin' || user.value.type === 'super-admin' 
        ? adminDashboard.url() 
        : customerDashboard.url();
});

const shouldShowLegalLinks = computed(() => {
    const component = usePage().component;
    return component === 'Guest/Landing' || component === 'Guest/Legal/Show';
});

const isMobileMenuOpen = ref(false);

</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <Toast />
        
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-50 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo/Brand & Legal Links -->
                    <div class="flex items-center gap-8">
                    <Link href="/" class="flex items-center gap-3 group hover:opacity-90 transition-opacity">
                        <!-- Logo (Priority) -->
                        <div v-if="logoUrl" class="h-10 flex items-center">
                             <img :src="logoUrl" class="dark:hidden h-full w-auto object-contain" :alt="businessSettings.business_name">
                             <img :src="logoDarkUrl || logoUrl" class="hidden dark:block h-full w-auto object-contain" :alt="businessSettings.business_name">
                        </div>

                        <!-- Fallback: Icon (Favicon) -->
                        <div v-else-if="iconUrl || iconDarkUrl" class="w-10 h-10 bg-white dark:bg-slate-800 rounded-xl flex items-center justify-center p-1.5 shadow-lg border border-slate-200 dark:border-slate-700 transition-transform group-hover:scale-105">
                            <img :src="iconUrl" class="dark:hidden w-full h-full object-contain" :alt="businessSettings.business_name">
                            <img :src="iconDarkUrl || iconUrl" class="hidden dark:block w-full h-full object-contain" :alt="businessSettings.business_name">
                        </div>

                        <!-- Fallback: Letter -->
                        <div v-else class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center font-bold text-white shadow-lg shadow-primary-500/20 text-xl border border-primary-600/10">
                            {{ (businessSettings.business_name || 'L').charAt(0) }}
                        </div>

                        <!-- Name & Tagline (Always Show) -->
                        <div class="flex flex-col"> 
                            <span class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-none">
                                {{ businessSettings.business_name || 'Laravel CRM' }}
                            </span>
                            <span v-if="businessSettings.tagline" class="hidden sm:block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-1.5 leading-snug">
                                {{ businessSettings.tagline }}
                            </span>
                        </div>
                    </Link>

                    <!-- Desktop Legal Links (Conditional) -->
                    <div v-if="shouldShowLegalLinks" class="hidden lg:flex items-center gap-6">
                        <Link :href="privacy.url()" class="text-sm font-bold text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors">
                            {{ __('Privacy Policy') }}
                        </Link>
                        <Link :href="terms.url()" class="text-sm font-bold text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors">
                            {{ __('Terms') }}
                        </Link>
                        <Link :href="about.url()" class="text-sm font-bold text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors">
                            {{ __('About Us') }}
                        </Link>
                        <Link :href="contact.url()" class="text-sm font-bold text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition-colors">
                            {{ __('Contact Us') }}
                        </Link>
                    </div>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden lg:flex items-center gap-4">
                        <ThemeToggle />
                        <div class="h-6 w-px bg-gray-200 dark:bg-gray-700"></div>
                        
                        <template v-if="user">
                            <Link :href="dashboardUrl" class="text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                {{ __('Dashboard') }}
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="login.url()" class="text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                {{ __('Log in') }}
                            </Link>
                            <Link :href="leadGeneration.url()" class="bg-primary-600 hover:bg-primary-700 text-white px-5 py-2.5 rounded-full font-medium transition-all shadow-lg shadow-primary-500/30 hover:shadow-primary-500/50">
                                {{ __('Get Started') }}
                            </Link>
                        </template>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex items-center lg:hidden gap-3">
                        <ThemeToggle />
                        <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400">
                            <Menu v-if="!isMobileMenuOpen" class="w-6 h-6" />
                            <X v-else class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div v-show="isMobileMenuOpen" class="lg:hidden py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col gap-3">
                        
                        <!-- Mobile Legal Links -->
                        <div class="space-y-2 mb-2">
                            <Link :href="privacy.url()" class="block text-base font-bold text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 py-2">
                                {{ __('Privacy Policy') }}
                            </Link>
                            <Link :href="terms.url()" class="block text-base font-bold text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 py-2">
                                {{ __('Terms') }}
                            </Link>
                            <Link :href="about.url()" class="block text-base font-bold text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 py-2">
                                {{ __('About Us') }}
                            </Link>
                            <Link :href="contact.url()" class="block text-base font-bold text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 py-2">
                                {{ __('Contact Us') }}
                            </Link>
                        </div>

                        <div class="pt-2 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-3">
                            <template v-if="user">
                                <Link :href="dashboardUrl" class="text-center py-2 px-4 bg-primary-50 dark:bg-primary-900/10 text-primary-600 dark:text-primary-400 rounded-lg font-medium">
                                    {{ __('Dashboard') }}
                                </Link>
                            </template>
                            <template v-else>
                                <Link :href="login.url()" class="text-center text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 font-medium py-2">
                                    {{ __('Log in') }}
                                </Link>
                                <Link :href="register.url()" class="text-center bg-primary-600 hover:bg-primary-700 text-white px-5 py-3 rounded-lg font-bold transition-all shadow-lg shadow-primary-500/30">
                                    {{ __('Get Started') }}
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-grow">
            <div :class="[
                'mx-auto px-4 sm:px-6 lg:px-8',
                fullWidth ? 'w-full p-0' : 'max-w-4xl py-12'
            ]">
                <slot />
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400 text-center md:text-left">
                        &copy; {{ new Date().getFullYear() }} {{ businessSettings.business_name || 'Company Name' }}. {{ __('All rights reserved.') }}
                    </div>
                    <div class="flex flex-wrap justify-center gap-6">
                        <Link :href="privacy.url()" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                            {{ __('Privacy Policy') }}
                        </Link>
                        <Link :href="terms.url()" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                            {{ __('Terms & Conditions') }}
                        </Link>
                        <Link :href="about.url()" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                            {{ __('About Us') }}
                        </Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
