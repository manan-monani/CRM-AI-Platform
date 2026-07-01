<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next';
import { wTrans as __ } from '@/Core/i18n';

const props = defineProps({
    links: {
        type: Array,
        required: true
    },
    meta: {
        type: Object,
        default: () => ({})
    }
});

const isNumeric = (label: string) => !isNaN(Number(label));

// Get first and last page links
const firstPage = props.links?.find((link: any) => link.label === '1');
const lastPage = props.links?.find((link: any, index: number) => 
    index === props.links.length - 2 && isNumeric(link.label)
);
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-col lg:flex-row items-center justify-between gap-6 p-4 sm:p-5 bg-gradient-to-br from-white to-slate-50/50 dark:from-slate-800 dark:to-slate-800/50 rounded-xl border border-slate-200/60 dark:border-slate-700/50 shadow-lg shadow-slate-100/50 dark:shadow-none backdrop-blur-sm">
        <!-- Results Info with animated gradient -->
        <div v-if="meta.total" class="flex items-center gap-2 text-sm">
            <div class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-slate-50 to-slate-100/50 dark:from-slate-900/50 dark:to-slate-800/50 border border-slate-200/50 dark:border-slate-700/50 shadow-sm">
                <span class="text-slate-500 dark:text-slate-400">{{ __('Showing') }}</span>
                <span class="font-black text-brand-600 dark:text-brand-400 text-base">{{ meta.from }}</span>
                <span class="text-slate-400 dark:text-slate-500">-</span>
                <span class="font-black text-brand-600 dark:text-brand-400 text-base">{{ meta.to }}</span>
                <span class="text-slate-500 dark:text-slate-400">{{ __('of') }}</span>
                <span class="font-black text-slate-900 dark:text-white text-base">{{ meta.total }}</span>
            </div>
        </div>
        <div v-else class="flex-1"></div>

        <!-- Enhanced Pagination Links -->
        <div class="flex items-center gap-2">
            <!-- Previous Button -->
            <template v-for="(link, key) in links" :key="key">
                <!-- First (Previous) button -->
                <template v-if="key === 0">
                    <div v-if="link.url === null" 
                         class="flex items-center justify-center w-10 h-10 text-slate-300 dark:text-slate-700 bg-slate-50/50 dark:bg-slate-900/30 border border-slate-200/50 dark:border-slate-700/30 rounded-xl cursor-not-allowed transition-all">
                        <ChevronLeft :size="18" />
                    </div>
                    <Link v-else 
                          :href="link.url"
                          class="group flex items-center justify-center w-10 h-10 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-brand-50 dark:hover:bg-brand-900/30 hover:border-brand-300 dark:hover:border-brand-700 hover:text-brand-700 dark:hover:text-brand-300 transition-all duration-200 shadow-sm hover:shadow-md hover:shadow-brand-100 dark:hover:shadow-none hover:-translate-y-0.5 active:translate-y-0">
                        <ChevronLeft :size="18" class="group-hover:scale-110 transition-transform" />
                    </Link>
                </template>

                <!-- Page number buttons -->
                <template v-else-if="key !== links.length - 1">
                    <!-- Active page -->
                    <div v-if="link.active"
                         class="relative flex items-center justify-center min-w-[40px] h-10 px-3 font-bold text-white bg-gradient-to-br from-brand-500 to-brand-600 dark:from-brand-600 dark:to-brand-700 rounded-xl shadow-lg shadow-brand-200 dark:shadow-brand-900/30 border-2 border-brand-300 dark:border-brand-500 transition-all scale-110">
                        <span class="relative z-10" v-html="link.label"></span>
                        <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent rounded-xl pointer-events-none"></div>
                    </div>
                    
                    <!-- Regular page link -->
                    <Link v-else 
                          :href="link.url"
                          class="group flex items-center justify-center min-w-[40px] h-10 px-3 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-gradient-to-br hover:from-brand-50 hover:to-brand-100/50 dark:hover:from-brand-900/20 dark:hover:to-brand-800/20 hover:border-brand-300 dark:hover:border-brand-600 hover:text-brand-700 dark:hover:text-brand-300 transition-all duration-200 shadow-sm hover:shadow-md hover:shadow-brand-50 dark:hover:shadow-none hover:-translate-y-0.5 active:translate-y-0">
                        <span v-html="link.label" class="group-hover:scale-105 transition-transform"></span>
                    </Link>
                </template>

                <!-- Last (Next) button -->
                <template v-else>
                    <div v-if="link.url === null"
                         class="flex items-center justify-center w-10 h-10 text-slate-300 dark:text-slate-700 bg-slate-50/50 dark:bg-slate-900/30 border border-slate-200/50 dark:border-slate-700/30 rounded-xl cursor-not-allowed transition-all">
                        <ChevronRight :size="18" />
                    </div>
                    <Link v-else 
                          :href="link.url"
                          class="group flex items-center justify-center w-10 h-10 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-brand-50 dark:hover:bg-brand-900/30 hover:border-brand-300 dark:hover:border-brand-700 hover:text-brand-700 dark:hover:text-brand-300 transition-all duration-200 shadow-sm hover:shadow-md hover:shadow-brand-100 dark:hover:shadow-none hover:-translate-y-0.5 active:translate-y-0">
                        <ChevronRight :size="18" class="group-hover:scale-110 transition-transform" />
                    </Link>
                </template>
            </template>
        </div>
    </div>
</template>
