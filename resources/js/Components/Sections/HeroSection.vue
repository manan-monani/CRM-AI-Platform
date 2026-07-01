<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { type PropType } from 'vue';
import { Zap, Play, CheckCircle } from 'lucide-vue-next';

defineProps({
    data: {
        type: Object as PropType<any>,
        default: () => ({}),
    },
});
</script>

<template>
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden bg-[#FDFDFC] dark:bg-slate-950 transition-colors duration-500">
        <!-- Decorative Orbs -->
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-brand-600/10 dark:bg-brand-500/5 rounded-full blur-[120px] -z-10 animate-pulse"></div>
        <div class="absolute top-[20%] -right-[10%] w-[30%] h-[30%] bg-indigo-600/10 dark:bg-indigo-500/5 rounded-full blur-[100px] -z-10 animate-pulse delay-1000"></div>

        <div class="max-w-7xl mx-auto px-6 text-center relative">
            
            <h1 class="text-5xl lg:text-8xl font-black leading-[1] mb-10 tracking-tight text-slate-900 dark:text-white">
                <span class="block">{{ data.title_main || 'Close bigger deals,' }}</span>
                <span class="block bg-clip-text text-transparent bg-gradient-to-r from-brand-600 via-brand-500 to-indigo-600 dark:from-brand-400 dark:via-brand-300 dark:to-indigo-400">
                    {{ data.title_gradient || 'without the friction.' }}
                </span>
            </h1>
            
            <p class="text-lg lg:text-xl text-slate-600 dark:text-slate-400 max-w-3xl mx-auto mb-14 leading-relaxed font-medium">
                {{ data.subtitle || 'Nexus CRM empowers sales teams with a high-impact pipeline, AI-driven automation, and deep intelligence for every customer interaction.' }}
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                <Link 
                    v-if="data.cta_link"
                    :href="data.cta_link" 
                    class="group relative w-full sm:w-auto px-12 py-5 bg-brand-600 text-white rounded-xl text-xl font-black transition-all hover:scale-105 active:scale-95 shadow-[0_20px_50px_rgba(37,99,235,0.3)] hover:shadow-brand-600/40"
                >
                    {{ data.cta_text || 'Start Your Free Trial' }}
                    <div class="absolute inset-0 rounded-xl bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </Link>
            </div>
        </div>
    </section>
</template>
