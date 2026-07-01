<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import Pagination from '@/Components/Core/Pagination.vue';
import { wTrans as __ } from '@/Core/i18n';
import { formatMoney as money } from '@/Core/money';
import customer from '@/routes/customer';
import { 
    DollarSign, Calendar, User, Eye, ArrowUpRight, 
    TrendingUp, ClipboardList, Briefcase, Clock, 
    CheckCircle2, XCircle, PieChart 
} from 'lucide-vue-next';
import DealPreviewModal from '@/Components/CRM/DealPreviewModal.vue';

const props = defineProps({
    deals: Object as any,
    stats: Object as any,
});

const selectedDeal = ref(null);
const showPreview = ref(false);

const openPreview = (deal: any) => {
    selectedDeal.value = deal;
    showPreview.value = true;
};

const formatCurrency = (value: number) => {
    return money(value);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const getStageColor = (stage: string) => {
    switch(stage) {
        case 'new': return 'bg-slate-100 text-slate-700';
        case 'proposal': return 'bg-blue-100 text-blue-700';
        case 'negotiation': return 'bg-amber-100 text-amber-700';
        case 'won': return 'bg-emerald-100 text-emerald-700';
        case 'lost': return 'bg-red-100 text-red-700';
        default: return 'bg-gray-100';
    }
};
</script>

<template>
    <Head :title="__('My Deals')" />

    <CustomerLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('My Pipeline') }}</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium mt-1">{{ __('Track and manage your opportunities at a glance.') }}</p>
                </div>
                <Link 
                    :href="customer.deals.create.url()" 
                    class="inline-flex items-center px-6 py-3 bg-brand-600 border border-transparent rounded-xl font-black text-sm text-white hover:bg-brand-500 focus:outline-none transition-all shadow-xl shadow-brand-500/20 cursor-pointer"
                >
                    <span class="mr-2 text-lg">+</span>
                    {{ __('Create Deal') }}
                </Link>
            </div>
        </template>

        <!-- Stats Grid -->
        <div v-if="stats" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Deals -->
            <div class="p-6 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-slate-50 dark:bg-slate-700/50 rounded-full group-hover:bg-slate-100 dark:group-hover:bg-slate-700 transition-colors"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-slate-100 dark:bg-slate-900/50 rounded-xl text-slate-600 dark:text-slate-400">
                            <Briefcase :size="20" />
                        </div>
                        <span class="text-xs font-black uppercase text-slate-400 tracking-widest">{{ __('Total') }}</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-900 dark:text-white">{{ stats.total_count }}</h3>
                        <span class="text-xs font-bold text-slate-500">{{ __('Deals') }}</span>
                    </div>
                    <p class="text-sm font-bold text-slate-500 mt-1">{{ formatCurrency(stats.total_value) }}</p>
                </div>
            </div>

            <!-- Open Deals -->
            <div class="p-6 bg-white dark:bg-slate-800 rounded-xl border border-brand-100 dark:border-slate-700 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-brand-50 dark:bg-brand-900/10 rounded-full group-hover:bg-brand-100 dark:group-hover:bg-brand-900/20 transition-colors"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-brand-50 dark:bg-brand-900/20 rounded-xl text-brand-600 dark:text-brand-400">
                            <Clock :size="20" />
                        </div>
                        <span class="text-xs font-black uppercase text-brand-600 dark:text-brand-400 tracking-widest">{{ __('Open') }}</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-900 dark:text-white">{{ stats.open_count }}</h3>
                        <span class="text-xs font-bold text-slate-500">{{ __('Active') }}</span>
                    </div>
                    <p class="text-sm font-bold text-slate-500 mt-1">{{ formatCurrency(stats.open_value) }}</p>
                </div>
            </div>

            <!-- Won Deals -->
            <div class="p-6 bg-white dark:bg-slate-800 rounded-xl border border-emerald-100 dark:border-slate-700 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-50 dark:bg-emerald-900/10 rounded-full group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/20 transition-colors"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 :size="20" />
                        </div>
                        <span class="text-xs font-black uppercase text-emerald-600 dark:text-emerald-400 tracking-widest">{{ __('Won') }}</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-900 dark:text-white">{{ stats.won_count }}</h3>
                        <span class="text-xs font-bold text-slate-500">{{ __('Closed') }}</span>
                    </div>
                    <p class="text-sm font-bold text-slate-500 mt-1">{{ formatCurrency(stats.won_value) }}</p>
                </div>
            </div>

            <!-- Lost Deals -->
            <div class="p-6 bg-white dark:bg-slate-800 rounded-xl border border-red-100 dark:border-slate-700 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-50 dark:bg-red-900/10 rounded-full group-hover:bg-red-100 dark:group-hover:bg-red-900/20 transition-colors"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-xl text-red-600 dark:text-red-400">
                            <XCircle :size="20" />
                        </div>
                        <span class="text-xs font-black uppercase text-red-600 dark:text-red-400 tracking-widest">{{ __('Lost') }}</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-900 dark:text-white">{{ stats.lost_count }}</h3>
                        <span class="text-xs font-bold text-slate-500">{{ __('Archived') }}</span>
                    </div>
                    <p class="text-sm font-bold text-slate-500 mt-1">{{ formatCurrency(stats.lost_value) }}</p>
                </div>
            </div>
        </div>

        <div v-if="deals.data.length > 0">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
                <div 
                    v-for="deal in deals.data" 
                    :key="deal.id"
                    @click="openPreview(deal)"
                    class="relative group bg-white dark:bg-slate-800 p-8 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 cursor-pointer overflow-hidden"
                >
                    <!-- Background Decoration -->
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-brand-50 dark:bg-brand-900/10 rounded-full blur-2xl group-hover:bg-brand-100 dark:group-hover:bg-brand-900/20 transition-colors"></div>
                    
                    <div class="flex justify-between items-start mb-6 relative z-10">
                        <div class="space-y-1">
                            <h3 class="font-black text-xl text-slate-900 dark:text-white group-hover:text-brand-600 transition-colors tracking-tight">
                                {{ deal.title }}
                            </h3>
                            <div class="flex items-center gap-2">
                                <TrendingUp :size="12" class="text-brand-500" />
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">{{ deal.probability }}% {{ __('Probability') }}</span>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm" :class="getStageColor(deal.stage)">
                            {{ deal.stage }}
                        </span>
                    </div>
                    
                    <div class="space-y-4 relative z-10">
                        <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="font-black text-lg text-slate-900 dark:text-white">{{ formatCurrency(deal.value) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-400">
                                <Calendar :size="14" />
                                <span class="text-[10px] font-bold uppercase tracking-tighter">{{ formatDate(deal.expected_close_date) }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <div v-if="deal.assigned_to_user" class="flex items-center gap-3">
                                <img :src="deal.assigned_to_user.profile_photo_url || `https://ui-avatars.com/api/?name=${deal.assigned_to_user.name}&background=random`" class="w-8 h-8 rounded-xl border border-white dark:border-slate-700 shadow-sm object-cover">
                                <div class="hidden sm:block">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">{{ __('Assigned To') }}</p>
                                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300 leading-none">{{ deal.assigned_to_user.name }}</p>
                                </div>
                            </div>
                            
                            <div class="flex gap-2">
                                <button 
                                    class="p-2.5 bg-brand-50 dark:bg-brand-900/20 text-brand-600 dark:text-brand-400 rounded-xl hover:bg-brand-600 hover:text-white transition-all shadow-sm"
                                    @click.stop="openPreview(deal)"
                                    :title="__('Quick Preview')"
                                >
                                    <Eye :size="18" />
                                </button>
                                <Link 
                                    :href="customer.deals.show.url(deal.id)"
                                    class="p-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-900 hover:text-white dark:hover:bg-white dark:hover:text-slate-900 transition-all shadow-sm"
                                    @click.stop
                                >
                                    <ArrowUpRight :size="18" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-slate-100 dark:bg-slate-800">
                        <div 
                            class="h-full bg-brand-500 transition-all duration-500" 
                            :style="{ width: `${deal.probability}%` }"
                        ></div>
                    </div>
                </div>
            </div>
            
            <Pagination :links="deals.links" :meta="{ from: deals.from, to: deals.to, total: deals.total }" />
        </div>
        
        <div v-else class="bg-white dark:bg-slate-800 rounded-xl p-20 text-center border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden relative">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-brand-50 dark:bg-brand-900/10 rounded-full blur-3xl"></div>
            <div class="relative z-10 max-w-md mx-auto">
                <div class="w-20 h-20 bg-brand-50 dark:bg-brand-900/20 rounded-xl flex items-center justify-center mx-auto mb-8 text-brand-500 shadow-inner">
                    <ClipboardList :size="40" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mb-2">{{ __('Start Your Journey') }}</h3>
                <p class="text-slate-500 dark:text-slate-400 font-medium mb-10">{{ __('You don\'t have any deals in your pipeline yet. Create your first opportunity to get started.') }}</p>
                <Link 
                    :href="customer.deals.create.url()" 
                    class="inline-flex items-center px-10 py-4 bg-brand-600 text-white rounded-xl font-black text-sm hover:bg-brand-500 transition-all shadow-2xl shadow-brand-500/40 cursor-pointer"
                >
                    <span class="mr-2 text-xl">+</span>
                    {{ __('Create Your First Deal') }}
                </Link>
            </div>
        </div>

        <!-- Preview Modal -->
        <DealPreviewModal 
            v-if="selectedDeal"
            :show="showPreview"
            :deal="selectedDeal"
            @close="showPreview = false"
        />
    </CustomerLayout>
</template>
