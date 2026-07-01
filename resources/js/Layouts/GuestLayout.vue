<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import Toast from '@/Components/Common/Toast.vue';
import { onMounted, computed } from 'vue';

onMounted(() => {
    const direction = localStorage.getItem('direction') || 'ltr';
    document.documentElement.setAttribute('dir', direction);
});

const page = usePage();
const businessSettings = computed(() => (page.props.branding as any)?.business_settings || {});

const logoUrl = computed(() => {
    const logo = businessSettings.value.logo_url;
    if (!logo) return null;
    return logo.startsWith('http') || logo.startsWith('/storage') ? logo : '/storage/' + logo;
});

const logoDarkUrl = computed(() => {
    const logo = businessSettings.value.logo_dark_url;
    if (!logo) return null;
    return logo.startsWith('http') || logo.startsWith('/storage') ? logo : '/storage/' + logo;
});
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
        <Toast />
        <div class="absolute top-4 right-4 flex items-center space-x-2">
            <ThemeToggle />
        </div>
        <div>
            <Link href="/" class="flex justify-center mb-6">
                <div v-if="logoUrl" class="h-20 flex items-center">
                    <img :src="logoUrl" class="dark:hidden h-full w-auto object-contain" :alt="businessSettings.business_name">
                    <img :src="logoDarkUrl || logoUrl" class="hidden dark:block h-full w-auto object-contain" :alt="businessSettings.business_name">
                </div>
                <span v-else class="text-3xl font-bold text-gray-800 dark:text-white">{{ businessSettings.business_name || 'Laravel Factory' }}</span>
            </Link>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
            <slot />
        </div>
    </div>
</template>
