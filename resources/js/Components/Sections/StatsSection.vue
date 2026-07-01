<script setup lang="ts">
defineProps({
    data: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <section class="py-16 md:py-24 border-y border-slate-200/50 dark:border-slate-900/50 bg-white/40 dark:bg-slate-900/20 backdrop-blur-sm shadow-[inset_0_20px_50px_rgba(0,0,0,0.02)]">
        <div class="max-w-7xl mx-auto px-6">
            <p v-if="data.title" class="text-center text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.3em] mb-12">
                {{ data.title }}
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-16 md:gap-8 items-center">
                <div v-for="(item, index) in data.items" :key="index" class="relative text-center md:px-8">
                    <div v-if="index !== 0" class="hidden md:block absolute -left-0.5 top-1/2 -translate-y-1/2 w-px h-16 bg-gradient-to-b from-transparent via-slate-200 dark:via-slate-800 to-transparent"></div>
                    <div class="text-5xl lg:text-7xl font-black text-slate-900 dark:text-white mb-3 tracking-tighter tabular-nums">
                        {{ item.value }}<span class="text-brand-600">{{ item.suffix || '' }}</span>
                    </div>
                    <div class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.3em]">
                        {{ item.label }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
