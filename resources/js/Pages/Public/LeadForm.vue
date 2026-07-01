<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { PropType, computed } from 'vue';
import LeadFormCard from '@/Components/Public/LeadFormCard.vue';

const props = defineProps({
    form: Object as PropType<any>,
    branding: {
        type: Object as PropType<any>,
        default: () => ({}),
    },
    landing: {
        type: Object as PropType<any>,
        default: () => ({}),
    },
});

const heroImage = computed(() => {
    if (props.landing?.landing_hero_image) {
        const img = props.landing.landing_hero_image;
        // Handle JSON string (if image is stored as JSON)
        if (typeof img === 'string' && img.startsWith('{')) {
            try {
                const parsed = JSON.parse(img);
                return parsed.url || parsed.path || null;
            } catch (e) {
                // Not valid JSON, continue with normal handling
            }
        }
        // Handle full URLs or blob URLs
        if (img.startsWith('http://') || img.startsWith('https://') || img.startsWith('blob:')) {
            return img;
        }
        // Handle relative paths - add /storage/ prefix if not present
        if (!img.startsWith('/')) {
            return `/storage/${img}`;
        }
        return img;
    }
    return null;
});

const brandLogo = computed(() => {
    if (props.branding?.logo) {
        const logo = props.branding.logo;
        // Handle JSON string (if logo is stored as JSON)
        if (typeof logo === 'string' && logo.startsWith('{')) {
            try {
                const parsed = JSON.parse(logo);
                return parsed.url || parsed.path || null;
            } catch (e) {
                // Not valid JSON, continue with normal handling
            }
        }
        // Handle full URLs or blob URLs
        if (logo.startsWith('http://') || logo.startsWith('https://') || logo.startsWith('blob:')) {
            return logo;
        }
        // Handle relative paths - add /storage/ prefix if not present
        if (!logo.startsWith('/')) {
            return `/storage/${logo}`;
        }
        return logo;
    }
    return null;
});

const brandColor = computed(() => props.branding?.primary || '#179753');
</script>

<template>
    <Head :title="props.form?.title || 'Contact Us'" />

    <div class="min-h-screen flex flex-col relative overflow-hidden bg-slate-50 dark:bg-slate-900">
        
        <!-- Hero Banner Section -->
        <div 
            class="relative w-full bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 text-white overflow-hidden"
            :style="heroImage ? `background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), url('${heroImage}'); background-size: cover; background-position: center;` : `background: linear-gradient(135deg, ${brandColor} 0%, ${brandColor}dd 100%)`"
        >
            <!-- Pattern Overlay -->
            <div class="absolute inset-0 pattern-dots opacity-10"></div>
            
            <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20">
                <!-- Logo -->
                <div class="flex justify-center mb-8" v-if="brandLogo">
                    <img :src="brandLogo" :alt="branding?.business_name || 'Logo'" class="h-12 sm:h-16 w-auto object-contain filter drop-shadow-lg">
                </div>
                <div class="flex justify-center mb-8" v-else-if="branding?.business_name">
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ branding.business_name }}</h2>
                </div>

                <!-- Landing Content -->
                <div class="text-center max-w-3xl mx-auto">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold mb-4 leading-tight">
                        {{ landing?.landing_title || 'Transform Your Business' }}
                    </h1>
                    <p class="text-lg sm:text-xl text-slate-200 max-w-2xl mx-auto leading-relaxed">
                        {{ landing?.landing_subtitle || 'Join thousands of businesses already growing with our platform. Get started in minutes.' }}
                    </p>
                </div>
            </div>

            <!-- Decorative Wave -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg class="w-full h-12 sm:h-16 fill-slate-50 dark:fill-slate-900" viewBox="0 0 1200 120" preserveAspectRatio="none">
                    <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path>
                </svg>
            </div>
        </div>

        <!-- Form Section -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12 -mt-6 sm:-mt-8 relative z-20">
            <div class="w-full max-w-2xl animate-fade-in-up">
                <LeadFormCard :form="props.form" :branding="props.branding" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.pattern-dots {
    background-image: radial-gradient(circle, currentColor 1px, transparent 1px);
    background-size: 20px 20px;
}

@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out;
}
</style>
