<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    X, ClipboardList, TrendingUp, Calendar, 
    DollarSign, Clock, CheckCircle2, AlertCircle,
    Eye
} from 'lucide-vue-next';
import { wTrans as __ } from '@/Core/i18n';
import { formatMoney as money } from '@/Core/money';
import ActivityItem from '@/Components/CRM/ActivityItem.vue';
import customer from '@/routes/customer';

const props = defineProps({
    show: Boolean,
    deal: {
        type: Object as any,
        required: true,
    },
});

const emit = defineEmits(['close']);

const formatCurrency = (value: number) => {
    return money(value);
};

const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const getPriorityColor = (priority: string) => {
    switch(priority) {
        case 'urgent': return 'text-red-500 bg-red-50 dark:bg-red-900/20';
        case 'high': return 'text-orange-500 bg-orange-50 dark:bg-orange-900/20';
        case 'medium': return 'text-blue-500 bg-blue-50 dark:bg-blue-900/20';
        default: return 'text-slate-500 bg-slate-50 dark:bg-slate-900/20';
    }
};

const getStatusColor = (status: string) => {
    switch(status) {
        case 'completed': return 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20';
        case 'in_progress': return 'text-blue-500 bg-blue-50 dark:bg-blue-900/20';
        default: return 'text-amber-500 bg-amber-50 dark:bg-amber-900/20';
    }
};
</script>

<template>
    <div 
        v-if="show" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
    >
        <!-- Overlay -->
        <div 
            class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
            @click="emit('close')"
        ></div>

        <!-- Modal Content -->
        <div 
            class="relative bg-white dark:bg-slate-800 w-full max-w-4xl max-h-[90vh] rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col animate-in fade-in zoom-in duration-300"
        >
            <!-- Header -->
            <div class="p-6 sm:p-8 border-b border-slate-100 dark:border-slate-700 flex justify-between items-start shrink-0">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ deal.title }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-brand-500 text-white">
                            {{ deal.stage }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest flex items-center gap-2">
                        <Clock :size="12" /> {{ __('Quick Preview') }}
                    </p>
                </div>
                <button 
                    @click="emit('close')"
                    class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 dark:text-slate-500 transition-colors"
                >
                    <X :size="24" />
                </button>
            </div>

            <!-- Body (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-6 sm:p-8 space-y-8 custom-scrollbar font-sans">
                <!-- Top Row: Financials & Probability -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-6 bg-emerald-50/50 dark:bg-emerald-900/10 rounded-xl border border-emerald-100 dark:border-emerald-900/20 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] uppercase font-black text-emerald-600 dark:text-emerald-400 tracking-widest mb-1">{{ __('Value') }}</p>
                            <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300">{{ formatCurrency(deal.value) }}</p>
                        </div>
                        <div class="p-3 bg-white dark:bg-slate-800 rounded-xl shadow-sm text-emerald-500">
                            <!-- Currency icon removed as symbol is dynamic -->
                        </div>
                    </div>
                    <div class="p-6 bg-brand-50/50 dark:bg-brand-900/10 rounded-xl border border-brand-100 dark:border-brand-900/20">
                        <div class="flex justify-between items-center mb-2">
                            <p class="text-[10px] uppercase font-black text-brand-600 dark:text-brand-400 tracking-widest">{{ __('Probability') }}</p>
                            <p class="text-lg font-black text-brand-700 dark:text-brand-300">{{ deal.probability }}%</p>
                        </div>
                        <div class="w-full h-1.5 bg-brand-100 dark:bg-brand-900/30 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-brand-500 transition-all duration-1000" 
                                :style="{ width: `${deal.probability}%` }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Action Items Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl text-emerald-500">
                            <ClipboardList :size="18" />
                        </div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">{{ __('Action Items') }}</h3>
                    </div>

                    <div v-if="deal.tasks && deal.tasks.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div 
                            v-for="task in deal.tasks.slice(0, 4)" 
                            :key="task.id"
                            class="p-4 bg-slate-50 dark:bg-slate-900/30 rounded-xl border border-transparent hover:border-brand-300 dark:hover:border-brand-900/30 transition-all"
                        >
                            <div class="flex items-start gap-3">
                                <div 
                                    class="mt-1 w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                                    :class="task.status === 'completed' ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 dark:border-slate-600'"
                                >
                                    <CheckCircle2 v-if="task.status === 'completed'" :size="10" />
                                </div>
                                <div class="min-w-0">
                                    <p class="font-extrabold text-xs truncate" :class="task.status === 'completed' ? 'text-slate-400 line-through' : 'text-slate-900 dark:text-white'">
                                        {{ task.title }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span :class="getPriorityColor(task.priority)" class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-widest">
                                            {{ task.priority }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 rounded-xl bg-slate-50 dark:bg-slate-900/20 border-2 border-dashed border-slate-100 dark:border-slate-800">
                        <p class="text-xs font-bold text-slate-500">{{ __('No action items yet.') }}</p>
                    </div>
                </div>

                <!-- Timeline Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-brand-50 dark:bg-brand-900/20 rounded-xl text-brand-500">
                            <TrendingUp :size="18" />
                        </div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">{{ __('Activity Timeline') }}</h3>
                    </div>

                    <div v-if="deal.activities && deal.activities.length > 0" class="relative pl-6">
                        <div class="absolute left-3 top-5 bottom-5 w-0.5 bg-slate-100 dark:bg-slate-700/50"></div>
                        <div class="space-y-4">
                            <ActivityItem 
                                v-for="activity in deal.activities.slice(0, 3)" 
                                :key="activity.id" 
                                :activity="activity" 
                            />
                        </div>
                    </div>
                    <div v-else class="text-center py-6">
                        <p class="text-xs font-bold text-slate-400">{{ __('No recent activity.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 bg-slate-50 dark:bg-slate-900/30 shrink-0 border-t border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="flex items-center gap-3 text-sm text-slate-500 font-bold">
                    <Calendar :size="14" />
                    <span>{{ __('Closing Date:') }} {{ formatDate(deal.expected_close_date) }}</span>
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <button 
                        @click="emit('close')"
                        class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl font-bold text-sm text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-700 transition-all border border-transparent hover:border-slate-200 dark:hover:border-slate-600"
                    >
                        {{ __('Close') }}
                    </button>
                    <Link 
                        :href="customer.deals.show.url(deal.id)"
                        class="flex-1 sm:flex-none px-6 py-2.5 bg-brand-600 text-white rounded-xl font-black text-sm hover:bg-brand-500 transition-all shadow-lg shadow-brand-500/20 flex items-center justify-center gap-2"
                    >
                        <Eye :size="16" />
                        {{ __('View Detailed') }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>
