<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PropType, ref, computed } from 'vue';
import admin from '@/routes/admin';
import { 
    Users, UserPlus, UserCheck, TrendingUp, Activity, Mail, Phone, 
    Building, ChevronRight, Eye, AlertCircle, Clock, Inbox, 
    LayoutDashboard, CheckCircle2, Search, Calendar, User, ShieldCheck,
    Briefcase
} from 'lucide-vue-next';
import { wTrans as __ } from '@/Core/i18n';

const props = defineProps({
    stats: Object as PropType<{
        customers: { total: number; new: number; active: number };
        leads: { total: number; new: number; active: number };
        deals: { total: number };
        tasks: { total: number };
        staff: any[];
    }>,
    monitoring: Object as PropType<{
        tasks: { overdue: any[]; upcoming: any[] };
        deals: { overdue: any[]; upcoming: any[] };
    }>,
    recentCustomers: Array as PropType<any[]>,
    recentLeads: Array as PropType<any[]>,
});

const activeTab = ref('tasks');

const overdueCount = computed(() => {
    return (props.monitoring?.tasks?.overdue?.length || 0) + (props.monitoring?.deals?.overdue?.length || 0);
});

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        'new': 'bg-blue-50 text-blue-600 dark:bg-blue-900/20',
        'contacted': 'bg-purple-50 text-purple-600 dark:bg-purple-900/20',
        'qualified': 'bg-green-50 text-green-600 dark:bg-green-900/20',
        'converted': 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20',
        'lost': 'bg-slate-50 text-slate-600 dark:bg-slate-900/20',
    };
    return colors[status] || 'bg-slate-50 text-slate-600';
};

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}
</script>

<template>
    <Head :title="__('Dashboard')" />

    <AdminLayout>
        <div class="p-6 space-y-8 animate-fade-in">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('Administrative Dashboard') }}</h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-1">{{ __('Unified system monitoring and performance overview') }}</p>
                </div>

                <div v-if="overdueCount > 0" class="flex items-center gap-3 px-5 py-2.5 bg-rose-50 dark:bg-rose-900/30 border border-border-rose-200 dark:border-rose-800 rounded-xl shadow-sm">
                    <div class="w-2 h-2 rounded-full bg-rose-600 animate-pulse"></div>
                    <span class="text-sm font-black text-rose-700 dark:text-rose-300">{{ overdueCount }} {{ __('Critical Overdue Items') }}</span>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Customers Card -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all group overflow-hidden relative">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-brand-600 dark:text-brand-400 font-bold text-[10px] uppercase tracking-widest">{{ __('Customers') }}</span>
                            <div class="p-2 bg-brand-50 dark:bg-brand-900/50 rounded-xl">
                                <Users class="text-brand-600 dark:text-brand-400" :size="20" />
                            </div>
                        </div>
                        <p class="text-3xl font-black text-slate-900 dark:text-white">{{ props.stats?.customers?.total || 0 }}</p>
                        <div class="flex items-center gap-2 mt-2 text-xs font-bold">
                            <span class="text-emerald-600 dark:text-emerald-400">+{{ props.stats?.customers?.new || 0 }} {{ __('New') }}</span>
                            <span class="text-slate-400 dark:text-slate-500">{{ __('Last 30 days') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Leads Card -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all group overflow-hidden relative">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-purple-600 dark:text-purple-400 font-bold text-[10px] uppercase tracking-widest">{{ __('Sales Leads') }}</span>
                            <div class="p-2 bg-purple-50 dark:bg-purple-900/50 rounded-xl">
                                <TrendingUp class="text-purple-600 dark:text-purple-400" :size="20" />
                            </div>
                        </div>
                        <p class="text-3xl font-black text-slate-900 dark:text-white">{{ props.stats?.leads?.total || 0 }}</p>
                        <div class="flex items-center gap-2 mt-2 text-xs font-bold">
                            <span class="text-blue-600 dark:text-blue-400">{{ props.stats?.leads?.active || 0 }} {{ __('Active') }}</span>
                            <span class="text-slate-400 dark:text-slate-500">{{ __('In Pipeline') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Deals Card -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all group overflow-hidden relative">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold text-[10px] uppercase tracking-widest">{{ __('Deals') }}</span>
                            <div class="p-2 bg-indigo-50 dark:bg-indigo-900/50 rounded-xl">
                                <Briefcase class="text-indigo-600 dark:text-indigo-400" :size="20" />
                            </div>
                        </div>
                        <p class="text-3xl font-black text-slate-900 dark:text-white">{{ props.stats?.deals?.total || 0 }}</p>
                        <div class="flex items-center gap-2 mt-2 text-xs font-bold">
                            <span class="text-slate-400 dark:text-slate-500 italic">{{ __('Total Records') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tasks Card -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm hover:shadow-xl transition-all group overflow-hidden relative">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-blue-600 dark:text-blue-400 font-bold text-[10px] uppercase tracking-widest">{{ __('Tasks') }}</span>
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/50 rounded-xl">
                                <Inbox class="text-blue-600 dark:text-blue-400" :size="20" />
                            </div>
                        </div>
                        <p class="text-3xl font-black text-slate-900 dark:text-white">{{ props.stats?.tasks?.total || 0 }}</p>
                        <div class="flex items-center gap-2 mt-2 text-xs font-bold">
                            <span class="text-slate-400 dark:text-slate-500 italic">{{ __('Total Assignments') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Overdue Monitor -->
                <div class="bg-rose-50 dark:bg-rose-900/10 p-6 rounded-xl border border-rose-200 dark:border-rose-800/30 shadow-sm transition-all hover:bg-rose-100/50 transition-colors">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-rose-600 dark:text-rose-400 font-bold text-[10px] uppercase tracking-widest">{{ __('Priority Monitor') }}</span>
                        <div class="p-2 bg-rose-600 text-white rounded-xl shadow-lg shadow-rose-900/20">
                            <AlertCircle :size="20" />
                        </div>
                    </div>
                    <p class="text-3xl font-black text-rose-700 dark:text-rose-300">{{ overdueCount }}</p>
                    <p class="text-xs font-bold text-rose-600/60 mt-2 uppercase tracking-tight">{{ __('Total Overdue Items') }}</p>
                </div>

                <!-- Upcoming Highlights -->
                <div class="bg-amber-50 dark:bg-amber-900/10 p-6 rounded-xl border border-amber-200 dark:border-amber-800/30 shadow-sm transition-all hover:bg-amber-100/50 transition-colors">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-amber-600 dark:text-amber-400 font-bold text-[10px] uppercase tracking-widest">{{ __('Upcoming Deadlines') }}</span>
                        <div class="p-2 bg-amber-500 text-white rounded-xl shadow-lg shadow-amber-900/20">
                            <Clock :size="20" />
                        </div>
                    </div>
                    <p class="text-3xl font-black text-amber-700 dark:text-amber-300">
                        {{ (props.monitoring?.tasks?.upcoming?.length || 0) + (props.monitoring?.deals?.upcoming?.length || 0) }}
                    </p>
                    <p class="text-xs font-bold text-amber-600/60 mt-2 uppercase tracking-tight">{{ __('Items due this week') }}</p>
                </div>
            </div>

            <!-- Main Layout: Monitoring Boards (Full Width Top) -->
            <div class="space-y-8">
                <!-- Monitoring Boards (Full Width) -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-xl overflow-hidden min-h-[600px]">
                    <!-- Board Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 px-8 py-8 bg-slate-50/20 dark:bg-slate-800/20">
                        <div class="flex items-center space-x-10">
                            <button 
                                @click="activeTab = 'tasks'"
                                class="relative py-2 text-lg font-black transition-all"
                                :class="activeTab === 'tasks' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-300 hover:text-slate-500'"
                            >
                                {{ __('Tasks Monitoring') }}
                                <span v-if="activeTab === 'tasks'" class="absolute -bottom-8 left-0 right-0 h-1.5 bg-blue-500 rounded-full shadow-[0_4px_10px_rgba(59,130,246,0.3)]"></span>
                            </button>
                            <button 
                                @click="activeTab = 'deals'"
                                class="relative py-2 text-lg font-black transition-all"
                                :class="activeTab === 'deals' ? 'text-brand-600 dark:text-brand-400' : 'text-slate-300 hover:text-slate-500'"
                            >
                                {{ __('Deals Monitoring') }}
                                <span v-if="activeTab === 'deals'" class="absolute -bottom-8 left-0 right-0 h-1.5 bg-brand-500 rounded-full shadow-[0_4px_10px_rgba(79,70,229,0.3)]"></span>
                            </button>
                        </div>
                        <div class="flex items-center gap-2 p-3 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl shadow-sm italic text-[11px] font-bold text-slate-400">
                            <Activity :size="14" />
                            <span>{{ __('Real-time workload feed') }}</span>
                        </div>
                    </div>

                    <div class="p-8">
                        <!-- Tasks Monitoring Feed -->
                        <div v-if="activeTab === 'tasks'" class="space-y-12">
                            <!-- Overdue Tasks -->
                            <div v-if="monitoring?.tasks?.overdue?.length">
                                <h3 class="text-xs font-black text-rose-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <AlertCircle :size="16" />
                                    {{ __('Immediate Action Required (Overdue)') }}
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <Link v-for="task in monitoring.tasks.overdue" :key="task.id" :href="admin.tasks.show.url(task.id)" class="p-5 rounded-xl border border-rose-100 dark:border-rose-900/50 bg-rose-50/40 dark:bg-rose-900/10 hover:shadow-xl transition-all border-l-8 border-l-rose-500 block group/card">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex flex-col gap-1">
                                                <span class="text-[9px] font-black px-2 py-0.5 bg-rose-200 dark:bg-rose-900 text-rose-700 dark:text-rose-300 rounded-lg w-fit">{{ __('PAST DUE') }}</span>
                                                <span class="text-xs font-black text-slate-900 dark:text-white line-clamp-1 group-hover/card:text-rose-600 transition-colors">{{ task.title }}</span>
                                            </div>
                                            <span class="text-[10px] font-black text-rose-600 font-mono">{{ formatDate(task.due_date) }}</span>
                                        </div>
                                        <div class="flex items-center justify-between pt-4 border-t border-rose-100/50 dark:border-rose-900/30">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-[9px] font-black leading-none">
                                                    {{ task.assigned_to_user?.name?.charAt(0) || 'U' }}
                                                </div>
                                                <span class="text-[10px] font-bold text-slate-600 dark:text-slate-400" :class="task.assigned_to === $page.props.auth.user.id ? 'font-black text-brand-600' : ''">
                                                    {{ task.assigned_to === $page.props.auth.user.id ? __('Me') : (task.assigned_to_user?.name || __('Unassigned')) }}
                                                </span>
                                            </div>
                                            <span v-if="task.taskable" class="text-[9px] font-black text-blue-600 uppercase tracking-tighter italic">@{{ task.taskable.title || task.taskable.name }}</span>
                                        </div>
                                    </Link>
                                </div>
                            </div>

                            <!-- Upcoming Tasks -->
                            <div v-if="monitoring?.tasks?.upcoming?.length">
                                <h3 class="text-xs font-black text-blue-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <Clock :size="16" />
                                    {{ __('Upcoming This Week') }}
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <Link v-for="task in monitoring.tasks.upcoming" :key="task.id" :href="admin.tasks.show.url(task.id)" class="p-5 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-900/50 hover:shadow-xl transition-all block group/card">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex flex-col gap-1">
                                                <span class="text-[10px] font-black text-slate-900 dark:text-white line-clamp-1 group-hover/card:text-blue-600 transition-colors">{{ task.title }}</span>
                                                <span class="text-[9px] font-bold text-slate-400">{{ __('DUE SOON') }}</span>
                                            </div>
                                            <span class="text-[10px] font-black text-blue-600 font-mono">{{ formatDate(task.due_date) }}</span>
                                        </div>
                                        <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-[9px] font-black leading-none text-slate-400">
                                                    {{ task.assigned_to_user?.name?.charAt(0) || 'U' }}
                                                </div>
                                                <span class="text-[10px] font-bold text-slate-500 tracking-tight" :class="task.assigned_to === $page.props.auth.user.id ? 'font-black text-brand-600' : ''">
                                                    {{ task.assigned_to === $page.props.auth.user.id ? __('Me') : (task.assigned_to_user?.name || __('Unassigned')) }}
                                                </span>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>

                            <div v-if="!monitoring?.tasks?.overdue?.length && !monitoring?.tasks?.upcoming?.length" class="py-24 text-center">
                                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-900/50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-slate-800">
                                    <CheckCircle2 :size="32" class="text-emerald-500" />
                                </div>
                                <p class="text-lg font-black text-slate-900 dark:text-white">{{ __('No pending tasks monitoring needed!') }}</p>
                                <p class="text-sm font-bold text-slate-400 mt-1 uppercase tracking-tighter">{{ __('All workloads are currently current') }}</p>
                            </div>
                        </div>

                        <!-- Deals Monitoring Feed -->
                        <div v-if="activeTab === 'deals'" class="space-y-12">
                            <!-- Overdue Deals -->
                            <div v-if="monitoring?.deals?.overdue?.length">
                                <h3 class="text-xs font-black text-orange-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <TrendingUp :size="16" />
                                    {{ __('Stagnant Deals (Closing Overdue)') }}
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <Link v-for="deal in monitoring.deals.overdue" :key="deal.id" :href="admin.deals.show.url(deal.id)" class="p-6 rounded-xl border border-orange-100 dark:border-orange-900/30 bg-orange-50/30 dark:bg-orange-900/10 hover:shadow-2xl transition-all border-l-8 border-l-orange-500 block group/card">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex flex-col gap-1">
                                                <h4 class="text-base font-black text-slate-900 dark:text-white line-clamp-1 leading-none group-hover/card:text-orange-600 transition-colors">{{ deal.title }}</h4>
                                                <span class="text-[10px] font-bold text-orange-600 uppercase tracking-widest">{{ __('Expectation Missed') }}</span>
                                            </div>
                                            <span class="text-sm font-black text-rose-600 font-mono">${{ Number(deal.value).toLocaleString() }}</span>
                                        </div>
                                        <div class="flex items-center justify-between pt-4 border-t border-orange-100/50 dark:border-orange-900/30">
                                            <div class="flex flex-col">
                                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">{{ __('Should have closed') }}</p>
                                                <p class="text-[11px] font-black text-slate-700 dark:text-slate-300 italic">{{ formatDate(deal.expected_close_date) }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-xl bg-white dark:bg-slate-900 border border-orange-200 dark:border-orange-800 flex items-center justify-center text-[10px] font-black">
                                                    {{ deal.assigned_to_user?.name?.charAt(0) || 'U' }}
                                                </div>
                                                <span class="text-[10px] font-black" :class="deal.assigned_to === $page.props.auth.user.id ? 'text-brand-600' : 'text-slate-500'">
                                                    {{ deal.assigned_to === $page.props.auth.user.id ? __('Me') : (deal.assigned_to_user?.name || __('Unassigned')) }}
                                                </span>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>

                            <!-- Upcoming Deals -->
                            <div v-if="monitoring?.deals?.upcoming?.length">
                                <h3 class="text-xs font-black text-emerald-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <TrendingUp :size="16" />
                                    {{ __('Target Closings This Week') }}
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <Link v-for="deal in monitoring.deals.upcoming" :key="deal.id" :href="admin.deals.show.url(deal.id)" class="p-6 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-950/50 hover:shadow-2xl transition-all border-l-8 border-l-emerald-500 block group/card">
                                        <div class="flex justify-between items-start mb-4">
                                            <h4 class="text-base font-black text-slate-900 dark:text-white line-clamp-1 leading-none group-hover/card:text-emerald-600 transition-colors">{{ deal.title }}</h4>
                                            <span class="text-sm font-black text-emerald-600 font-mono">${{ Number(deal.value).toLocaleString() }}</span>
                                        </div>
                                        <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-700">
                                            <div class="flex flex-col">
                                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">{{ __('Target Date') }}</p>
                                                <p class="text-[11px] font-black text-slate-700 dark:text-slate-300 italic">{{ formatDate(deal.expected_close_date) }}</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                                    {{ deal.assigned_to_user?.name?.charAt(0) || 'U' }}
                                                </div>
                                                <span class="text-[10px] font-black" :class="deal.assigned_to === $page.props.auth.user.id ? 'text-brand-600' : 'text-slate-500'">
                                                    {{ deal.assigned_to === $page.props.auth.user.id ? __('Me') : (deal.assigned_to_user?.name || __('Unassigned')) }}
                                                </span>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>

                            <div v-if="!monitoring?.deals?.overdue?.length && !monitoring?.deals?.upcoming?.length" class="py-24 text-center">
                                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-900/50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-slate-800">
                                    <CheckCircle2 :size="32" class="text-emerald-500" />
                                </div>
                                <p class="text-lg font-black text-slate-900 dark:text-white">{{ __('All deals are targets on track!') }}</p>
                                <p class="text-sm font-bold text-slate-400 mt-1 uppercase tracking-tighter">{{ __('Keep up the pipeline momentum') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Staff Performance (Operational Oversight) - Full Width Below Monitoring -->
                <div v-if="stats?.staff?.length" class="space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-xl overflow-hidden flex flex-col">
                        <!-- Section Header -->
                        <div class="p-8 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex flex-col">
                                    <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Operational Oversight') }}</h3>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Executive Workload Analysis') }}</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-brand-50 dark:bg-brand-900/40 flex items-center justify-center">
                                        <ShieldCheck class="text-brand-600 dark:text-brand-400" :size="20" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Staff Grid (3 Columns) -->
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                <div v-for="staff in stats.staff" :key="staff.id" 
                                    class="relative p-6 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-900/40 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all duration-300 group cursor-default shadow-sm hover:shadow-xl"
                                    :class="{'ring-2 ring-brand-500/20 bg-brand-50/30 dark:bg-brand-900/10 border-brand-200 dark:border-brand-800': $page.props.auth.user.id === staff.id}"
                                >
                                    <div class="flex items-start justify-between mb-6">
                                        <div class="flex items-center gap-4">
                                            <!-- Avatar -->
                                            <div class="relative shrink-0">
                                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-200 font-bold text-xl shadow-sm group-hover:scale-105 transition-transform">
                                                    {{ staff.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <div v-if="$page.props.auth.user.id === staff.id" class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-brand-600 border-4 border-white dark:border-slate-800 rounded-full"></div>
                                            </div>
                                            <div>
                                                <p class="text-lg font-black text-slate-900 dark:text-white leading-none capitalize mb-1">{{ staff.name }}</p>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-md">{{ staff.type }}</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end">
                                            <div class="flex gap-2">
                                                <Link :href="`${admin.tasks.index.url()}?user_id=${staff.id}`" class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 text-slate-400 hover:text-blue-600 transition-colors" title="View Tasks">
                                                    <Inbox :size="16" />
                                                </Link>
                                                <Link :href="`${admin.deals.index.url()}?user_id=${staff.id}`" class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-brand-50 dark:hover:bg-brand-900/30 text-slate-400 hover:text-brand-600 transition-colors" title="View Deals">
                                                    <Briefcase :size="16" />
                                                </Link>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Enriched Stats Grid -->
                                    <div class="grid grid-cols-3 gap-3 mb-6">
                                        <div class="flex flex-col items-center p-3 rounded-xl bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100/50 dark:border-blue-900/20">
                                            <span class="text-[9px] font-black text-blue-600 uppercase mb-1">{{ __('Tasks') }}</span>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-lg font-black text-blue-700 dark:text-blue-400">{{ staff.pending_tasks }}</span>
                                            </div>
                                            <div class="flex gap-1.5 mt-1 border-t border-blue-100/30 w-full pt-1 justify-center">
                                                <span class="text-[8px] font-bold text-rose-500" title="Overdue">!{{ staff.due_tasks }}</span>
                                                <span class="text-[8px] font-bold text-emerald-500" title="Completed">✓{{ staff.completed_tasks }}</span>
                                                <span class="text-[8px] font-bold text-amber-500" title="Upcoming">>{{ staff.upcoming_tasks }}</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-center p-3 rounded-xl bg-brand-50/50 dark:bg-brand-900/10 border border-brand-100/50 dark:border-brand-900/20">
                                            <span class="text-[9px] font-black text-brand-600 uppercase mb-1">{{ __('Deals') }}</span>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-lg font-black text-brand-700 dark:text-brand-400">{{ staff.open_deals }}</span>
                                            </div>
                                            <div class="flex gap-1.5 mt-1 border-t border-brand-100/30 w-full pt-1 justify-center">
                                                <span class="text-[8px] font-bold text-rose-500" title="Overdue">!{{ staff.due_deals }}</span>
                                                <span class="text-[8px] font-bold text-emerald-500" title="Won">✓{{ staff.completed_deals }}</span>
                                                <span class="text-[8px] font-bold text-amber-500" title="Closing Soon">>{{ staff.upcoming_deals }}</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
                                            <span class="text-[9px] font-black text-slate-400 uppercase mb-1">{{ __('Efficiency') }}</span>
                                            <span class="text-lg font-black text-slate-600 dark:text-slate-300">{{ Math.round((staff.completed_tasks / Math.max(staff.pending_tasks + staff.completed_tasks, 1)) * 100) }}%</span>
                                            <div class="w-full h-1 bg-slate-200 dark:bg-slate-700 rounded-full mt-2 overflow-hidden">
                                                <div class="h-full bg-emerald-500" :style="{width: (staff.completed_tasks / Math.max(staff.pending_tasks + staff.completed_tasks, 1)) * 100 + '%'}"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Intensity Indicator (Full Width) -->
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-[9px] font-black uppercase tracking-tight mb-1">
                                            <span class="text-slate-400">{{ __('Real-time Intensity') }}</span>
                                            <span :class="{
                                                'text-emerald-600': (staff.pending_tasks + staff.open_deals) < 5,
                                                'text-amber-600': (staff.pending_tasks + staff.open_deals) >= 5 && (staff.pending_tasks + staff.open_deals) < 10,
                                                'text-rose-600': (staff.pending_tasks + staff.open_deals) >= 10
                                            }">
                                                {{ (staff.pending_tasks + staff.open_deals) > 10 ? __('Maximum') : (staff.pending_tasks + staff.open_deals) > 5 ? __('Average') : __('Stable') }}
                                            </span>
                                        </div>
                                        <div class="h-1 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                            <div 
                                                class="h-full rounded-full transition-all duration-1000"
                                                :style="{width: Math.min(((staff.pending_tasks + staff.open_deals) / 15) * 100, 100) + '%'}"
                                                :class="{
                                                    'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.3)]': (staff.pending_tasks + staff.open_deals) < 5,
                                                    'bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.3)]': (staff.pending_tasks + staff.open_deals) >= 5 && (staff.pending_tasks + staff.open_deals) < 10,
                                                    'bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.3)]': (staff.pending_tasks + staff.open_deals) >= 10
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Lists Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 pt-8">
                <!-- Recent Customers -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white text-start italic underline decoration-brand-500 underline-offset-8">{{ __('Newest Customers') }}</h3>
                        <Link :href="admin.customers.index.url()" class="px-4 py-2 bg-brand-50 hover:bg-brand-100 dark:bg-brand-900/30 text-[10px] font-black text-brand-600 dark:text-brand-400 uppercase tracking-widest rounded-xl transition-all">
                            {{ __('Global Directory') }}
                        </Link>
                    </div>
                    <div class="divide-y divide-slate-50 dark:divide-slate-700/50">
                        <div v-for="customer in recentCustomers" :key="customer.id" class="p-5 hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-all group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4 flex-1 min-w-0">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-600 to-indigo-700 flex items-center justify-center text-white font-black text-lg shadow-xl shadow-brand-900/20 group-hover:scale-105 transition-transform">
                                        {{ customer.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="text-start flex-1 min-w-0">
                                        <p class="text-sm font-black text-slate-900 dark:text-white truncate mb-0.5 tracking-tight">{{ customer.name }}</p>
                                        <p class="text-[11px] font-bold text-slate-400 truncate flex items-center gap-1.5 italic">
                                            <Mail :size="10" /> {{ customer.email }}
                                        </p>
                                    </div>
                                </div>
                                <Link :href="admin.customers.show.url(customer.id)" class="px-3 py-3 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/30 flex items-center justify-center transition-all border border-transparent hover:border-brand-200 shadow-sm">
                                    <ChevronRight :size="18" />
                                </Link>
                            </div>
                        </div>
                        <div v-if="!recentCustomers || recentCustomers.length === 0" class="p-12 text-center text-slate-300 font-bold uppercase text-xs tracking-widest">
                            {{ __('Dashboard is clean. No customers yet.') }}
                        </div>
                    </div>
                </div>

                <!-- Recent Leads -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white text-start italic underline decoration-purple-500 underline-offset-8">{{ __('Pipeline Activity') }}</h3>
                        <Link :href="admin.leads.index.url()" class="px-4 py-2 bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/30 text-[10px] font-black text-purple-600 dark:text-purple-400 uppercase tracking-widest rounded-xl transition-all">
                            {{ __('Lead Command') }}
                        </Link>
                    </div>
                    <div class="divide-y divide-slate-50 dark:divide-slate-700/50">
                        <div v-for="lead in recentLeads" :key="lead.id" class="p-5 hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-all group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4 flex-1 min-w-0">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-black text-lg shadow-xl shadow-purple-900/20 group-hover:scale-105 transition-transform">
                                        {{ lead.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="text-start flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <p class="text-sm font-black text-slate-900 dark:text-white truncate tracking-tight">{{ lead.name }}</p>
                                            <span class="text-[8px] px-1.5 py-0.5 rounded-md font-black capitalize whitespace-nowrap border" :class="getStatusColor(lead.status)">
                                                {{ __(lead.status) }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] font-bold text-slate-400 truncate flex items-center gap-1.5 italic">
                                            <Mail :size="10" /> {{ lead.email }}
                                        </p>
                                    </div>
                                </div>
                                <Link :href="admin.leads.show.url(lead.id)" class="px-3 py-3 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-500 hover:text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/30 flex items-center justify-center transition-all border border-transparent hover:border-purple-200 shadow-sm">
                                    <ChevronRight :size="18" />
                                </Link>
                            </div>
                        </div>
                        <div v-if="!recentLeads || recentLeads.length === 0" class="p-12 text-center text-slate-300 font-bold uppercase text-xs tracking-widest">
                            {{ __('Pipeline is quiet. No active leads.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Custom design aesthetics for premium dashboard feel */
.rounded-xl { border-radius: 1rem; }
.tracking-tighter { letter-spacing: -0.05em; }
.tracking-widest { letter-spacing: 0.15em; }

/* Dashboard Glassmorphism subtle effects */
.dark .bg-white\/5 { background-color: rgba(255, 255, 255, 0.05); }
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
