<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { type PropType } from 'vue';

defineProps({
    data: {
        type: Object as PropType<any>,
        default: () => ({}),
    },
});
</script>

<template>
    <section class="py-24 px-6 lg:py-40">
        <div class="max-w-7xl mx-auto relative group overflow-hidden rounded-xl bg-brand-600 py-24 lg:py-32 px-10 text-center shadow-[0_40px_100px_rgba(37,99,235,0.3)] dark:shadow-none">
            <!-- Dotted pattern overlay -->
            <div class="absolute inset-0 opacity-15 pointer-events-none" style="background-image: radial-gradient(#fff 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
            
            <!-- Animated blob bg for CTA -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[150%] h-[150%] bg-gradient-to-tr from-brand-700 via-transparent to-indigo-600/30 blur-[100px] -z-0 opacity-50 group-hover:scale-110 transition-transform duration-1000"></div>

            <div class="relative z-10 flex flex-col items-center">
                <h2 class="text-4xl lg:text-7xl font-black text-white mb-8 leading-none tracking-tight max-w-4xl">
                    <span v-if="data.title">{{ data.title }}</span>
                    <span v-else>Stop hunting. Start <span class="bg-clip-text text-transparent bg-gradient-to-b from-white to-white/60">winning.</span></span>
                </h2>
                <p v-if="data.subtitle" class="text-brand-100 text-xl lg:text-2xl font-medium mb-16 max-w-2xl leading-relaxed">
                    {{ data.subtitle }}
                </p>
                <div class="flex flex-col sm:flex-row gap-6 w-full sm:w-auto items-center">
                    <Link 
                        v-if="data.button_link"
                        :href="data.button_link" 
                        class="w-full sm:w-auto px-12 py-6 bg-white text-brand-600 rounded-xl text-2xl font-black transition-all hover:scale-105 hover:shadow-2xl hover:shadow-white/20 active:scale-95"
                    >
                        {{ data.button_text || 'Create Free Account' }}
                    </Link>
                    <button 
                        v-if="data.secondary_button_text !== 'none'"
                        class="w-full sm:w-auto px-12 py-6 bg-transparent text-white border-2 border-white/20 hover:border-white/40 backdrop-blur-md rounded-xl text-xl font-black transition-all hover:bg-white/5 active:scale-95"
                    >
                        {{ data.secondary_button_text || 'Download Enterprise Deck' }}
                    </button>
                </div>
                
                <div v-if="data.show_trust_badges !== false" class="mt-12 flex items-center gap-8">
                    <div class="flex -space-x-3">
                        <div v-for="i in 4" :key="i" class="w-10 h-10 rounded-full border-2 border-brand-600 bg-slate-200 overflow-hidden ring-4 ring-brand-500/10 shadow-lg">
                            <img :src="`https://api.dicebear.com/7.x/avataaars/svg?seed=user${i}`" alt="user" class="w-full h-full object-cover" />
                        </div>
                    </div>
                    <div class="text-brand-50 text-sm font-bold flex items-center gap-2">
                        <div class="flex">
                            <span v-for="i in 5" :key="i" class="text-amber-300">★</span>
                        </div>
                        {{ data.trust_text || 'Trusted by top-tier SDRs' }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
