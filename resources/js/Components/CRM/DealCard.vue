<script setup lang="ts">
import { PropType } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Calendar, User, DollarSign, Clock, Pencil } from 'lucide-vue-next';
import { wTrans as __ } from '@/Core/i18n';
import { formatMoney as money } from '@/Core/money';
import admin from '@/routes/admin';

const props = defineProps({
    deal: {
        type: Object as PropType<any>,
        required: true,
    },
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const formatCurrency = (value: number) => {
    return money(value);
};
const handleDragStart = (e: DragEvent) => {
    if (e.dataTransfer) {
        e.dataTransfer.setData('dealId', props.deal.id.toString());
        e.dataTransfer.setData('sourceStage', props.deal.stage);
        e.dataTransfer.effectAllowed = 'move';
    }
};



const getCardStyle = () => {
    // Status overrides stage colors
    if (props.deal.status === 'won') return 'bg-emerald-50/50 border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800';
    if (props.deal.status === 'lost') return 'bg-red-50/50 border-red-100 dark:bg-red-900/10 dark:border-red-800 opacity-75';
    
    // Stage colors
    switch(props.deal.stage) {
        case 'new': return 'bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-slate-700';
        case 'proposal': return 'bg-blue-50/50 border-blue-100 dark:bg-blue-900/10 dark:border-blue-800';
        case 'negotiation': return 'bg-amber-50/50 border-amber-100 dark:bg-amber-900/10 dark:border-amber-800';
        case 'won': return 'bg-emerald-50/50 border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800';
        case 'lost': return 'bg-red-50/50 border-red-100 dark:bg-red-900/10 dark:border-red-800 opacity-75';
        default: return 'bg-white border-slate-200 dark:bg-slate-800 dark:border-slate-700';
    }
};
</script>

<template>
    <div 
        class="p-4 rounded-xl border shadow-sm hover:shadow-md transition-all cursor-move group relative"
        :class="getCardStyle()"
        draggable="true"
        @dragstart="handleDragStart"
    >
        <div class="flex justify-between items-start mb-2 pr-8">
            <Link :href="admin.deals.show.url(deal.id)" class="text-sm font-bold text-slate-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 transition-colors line-clamp-2 leading-tight">
                {{ deal.title }}
            </Link>
            <Link 
                :href="admin.deals.edit.url(deal.id)" 
                class="absolute right-3 top-3 p-1.5 text-slate-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/40 rounded-lg transition-all opacity-0 group-hover:opacity-100"
                :title="__('Edit Deal')"
            >
                <Pencil :size="14" />
            </Link>
        </div>
        
        <div class="text-xs text-slate-500 mb-4 flex items-center gap-1.5">
            <User :size="12" class="text-slate-400" />
            <span v-if="deal.dealable" class="font-medium text-slate-600 dark:text-slate-400 truncate">{{ deal.dealable.name }}</span>
            <span v-else class="italic text-slate-400">{{ __('Unknown') }}</span>
        </div>

        <div class="flex items-center justify-between mt-auto pt-3 border-t border-slate-100 dark:border-slate-700/50">
            <div class="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white text-sm">
                {{ formatCurrency(deal.value) }}
            </div>
            <div v-if="deal.expected_close_date" class="flex items-center gap-1 text-[11px] text-slate-400 bg-slate-50 dark:bg-slate-700/50 px-2 py-0.5 rounded-full">
                <Calendar :size="10" />
                {{ formatDate(deal.expected_close_date) }}
            </div>
        </div>
        
        <div v-if="deal.assigned_to_user" class="mt-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img :src="deal.assigned_to_user?.profile_photo_url || `https://ui-avatars.com/api/?name=${deal.assigned_to_user?.name}&background=random`" class="w-8 h-8 rounded-full object-cover">
                <div>
                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ deal.assigned_to_user?.name || __('Unassigned') }}</p>
                    <p class="text-xs text-slate-500">{{ deal.assigned_to_user?.email }}</p>
                </div>
            </div>
            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" 
                :class="deal.probability > 70 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : (deal.probability > 30 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400')">
                {{ deal.probability }}%
            </span>
        </div>
    </div>
</template>
