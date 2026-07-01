<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { wTrans as __ } from '@/Core/i18n';
import { formatMoney as money } from '@/Core/money';
import customer from '@/routes/customer';
import ActivityItem from '@/Components/CRM/ActivityItem.vue';
import { 
    ArrowLeft, Calendar, DollarSign, User, Mail, 
    ClipboardList, FileText, FileImage, FileVideo, 
    FileArchive, Info, TrendingUp, CheckCircle2, 
    Clock, AlertCircle, ExternalLink, Download, 
    Image as ImageIcon
} from 'lucide-vue-next';

const props = defineProps({
    deal: {
        type: Object as any,
        required: true,
    },
});

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

const isImage = (mimeType: string) => {
    return mimeType && mimeType.startsWith('image/');
};

const getFileIcon = (mimeType: string) => {
    if (!mimeType) return FileText;
    if (mimeType.startsWith('image/')) return FileImage;
    if (mimeType.startsWith('video/')) return FileVideo;
    if (mimeType.includes('zip') || mimeType.includes('rar') || mimeType.includes('archive')) return FileArchive;
    if (mimeType.includes('pdf')) return FileText;
    return ClipboardList;
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
    <Head :title="deal.title" />

    <CustomerLayout>
        <template #header>
            <div class="flex flex-col gap-4">
                <Link 
                    :href="customer.deals.index.url()" 
                    class="group flex items-center text-slate-500 hover:text-brand-600 dark:hover:text-brand-400 text-sm font-bold transition-all"
                >
                    <ArrowLeft :size="16" class="mr-2 group-hover:-translate-x-1 transition-transform" />
                    {{ __('Back to Pipeline') }}
                </Link>
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ deal.title }}</h1>
                            <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-widest bg-brand-500 text-white shadow-lg shadow-brand-500/20">
                                {{ deal.stage }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-500 dark:text-slate-400 font-medium">
                            <span class="flex items-center gap-1.5"><Clock :size="14" /> {{ __('Opened on') }} {{ formatDate(deal.created_at) }}</span>
                            <span v-if="deal.status === 'won'" class="flex items-center gap-1.5 text-emerald-500"><CheckCircle2 :size="14" /> {{ __('Won') }}</span>
                            <span v-else-if="deal.status === 'lost'" class="flex items-center gap-1.5 text-red-500"><AlertCircle :size="14" /> {{ __('Lost') }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-[10px] uppercase font-black text-slate-400 tracking-widest">{{ __('Probability') }}</p>
                            <p class="text-xl font-black text-brand-600 dark:text-brand-400">{{ deal.probability }}%</p>
                        </div>
                        <div class="w-24 h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div 
                                class="h-full bg-brand-500 transition-all duration-1000" 
                                :style="{ width: `${deal.probability}%` }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Left: Major Sections -->
            <div class="xl:col-span-2 space-y-8">
                <!-- Overview & Description -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-8 shadow-sm">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2.5 bg-brand-50 dark:bg-brand-900/20 rounded-xl text-brand-500">
                            <Info :size="20" />
                        </div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Deal Description') }}</h3>
                    </div>
                    
                    <div class="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-400 leading-relaxed font-medium">
                        {{ deal.description || __('No detailed description provided for this deal.') }}
                    </div>
                </div>

                <!-- Tasks Section -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl text-emerald-500">
                                <ClipboardList :size="20" />
                            </div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Action Items') }}</h3>
                        </div>
                        <span v-if="deal.tasks?.length" class="text-xs font-black text-slate-400 uppercase tracking-widest">
                            {{ deal.tasks.length }} {{ __('Tasks total') }}
                        </span>
                    </div>

                    <div v-if="deal.tasks && deal.tasks.length > 0" class="space-y-4">
                        <div 
                            v-for="task in deal.tasks" 
                            :key="task.id"
                            class="group flex flex-col sm:flex-row sm:items-center justify-between p-5 bg-slate-50 dark:bg-slate-900/30 rounded-xl border border-transparent hover:border-brand-300 dark:hover:border-brand-900/30 transition-all duration-300"
                        >
                            <div class="flex items-start gap-4">
                                <div 
                                    class="mt-1 w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                                    :class="task.status === 'completed' ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 dark:border-slate-600'"
                                >
                                    <CheckCircle2 v-if="task.status === 'completed'" :size="12" />
                                </div>
                                <div>
                                    <p 
                                        class="font-extrabold text-sm tracking-tight"
                                        :class="task.status === 'completed' ? 'text-slate-400 line-through' : 'text-slate-900 dark:text-white'"
                                    >
                                        {{ task.title }}
                                    </p>
                                    <div class="flex items-center gap-3 mt-1 text-[11px] font-bold text-slate-500 uppercase tracking-tighter">
                                        <span class="flex items-center gap-1"><Calendar :size="12" /> {{ formatDate(task.due_date) }}</span>
                                        <span :class="getPriorityColor(task.priority)" class="px-1.5 py-0.5 rounded-md text-[10px]">
                                            {{ task.priority }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 sm:mt-0 flex items-center gap-2">
                                <span :class="getStatusColor(task.status)" class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full">
                                    {{ task.status.replace('_', ' ') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 rounded-xl bg-slate-50 dark:bg-slate-900/20 border-2 border-dashed border-slate-100 dark:border-slate-800">
                        <ClipboardList :size="40" class="mx-auto text-slate-300 dark:text-slate-700 mb-3" />
                        <p class="text-sm font-bold text-slate-500">{{ __('No action items assigned to this deal yet.') }}</p>
                    </div>
                </div>

                <!-- Timeline / Activity -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-8 shadow-sm">
                    <div class="flex items-center gap-3 mb-10">
                        <div class="p-2.5 bg-brand-50 dark:bg-brand-900/20 rounded-xl text-brand-500">
                            <TrendingUp :size="20" />
                        </div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ __('Activity Timeline') }}</h3>
                    </div>

                    <div v-if="deal.activities && deal.activities.length > 0" class="relative">
                        <div class="absolute left-3 top-5 bottom-5 w-0.5 bg-slate-100 dark:bg-slate-700/50"></div>
                        <div class="space-y-6">
                            <ActivityItem 
                                v-for="activity in deal.activities" 
                                :key="activity.id" 
                                :activity="activity" 
                            />
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <p class="text-sm font-bold text-slate-400">{{ __('No recent activity to display.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar: Financials & Stakeholders -->
            <div class="space-y-8">
                <!-- Financials Card -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-8 shadow-sm">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl text-emerald-500">
                            <!-- Icon removed for dynamic symbol compatibility -->
                        </div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">{{ __('Financial Summary') }}</h3>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="p-6 bg-emerald-50/50 dark:bg-emerald-900/10 rounded-xl border border-emerald-100 dark:border-emerald-900/20 text-center">
                            <p class="text-[10px] uppercase font-black text-emerald-600 dark:text-emerald-400 tracking-[0.2em] mb-1">
                                {{ __('Estimated Value') }}
                            </p>
                            <p class="text-3xl font-black text-emerald-700 dark:text-emerald-300">
                                {{ formatCurrency(deal.value) }}
                            </p>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center px-2">
                                <span class="text-sm font-bold text-slate-500 uppercase tracking-tighter">{{ __('Closing Date') }}</span>
                                <span class="flex items-center gap-2 font-black text-slate-900 dark:text-white">
                                    <Calendar :size="16" class="text-brand-500" />
                                    {{ formatDate(deal.expected_close_date) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center px-2">
                                <span class="text-sm font-bold text-slate-500 uppercase tracking-tighter">{{ __('Confidence') }}</span>
                                <span class="flex items-center gap-2 font-black text-slate-900 dark:text-white">
                                    <TrendingUp :size="16" class="text-brand-500" />
                                    {{ deal.probability }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachments Grid -->
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-blue-500">
                                <ClipboardList :size="18" />
                            </div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">{{ __('Attachments') }}</h3>
                        </div>
                    </div>

                    <div v-if="deal.media && deal.media.length > 0" class="space-y-4">
                        <div v-for="file in deal.media" :key="file.id" class="group relative bg-slate-50 dark:bg-slate-900/50 rounded-xl overflow-hidden border border-slate-100 dark:border-slate-800 hover:border-brand-500 transition-all duration-300">
                            <!-- Image Hover Preview -->
                            <div v-if="isImage(file.mime_type)" class="h-32 w-full overflow-hidden">
                                <img :src="file.original_url" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </div>
                            
                            <!-- File Details Row -->
                            <div class="p-4 flex items-center gap-3">
                                <div class="p-2.5 bg-white dark:bg-slate-800 rounded-xl shadow-sm text-brand-500">
                                    <component :is="getFileIcon(file.mime_type)" :size="18" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-extrabold text-slate-900 dark:text-white truncate" :title="file.file_name">
                                        {{ file.file_name }}
                                    </p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        {{ (file.size / 1024 / 1024).toFixed(2) }} MB
                                    </p>
                                </div>
                                <div class="flex gap-1.5">
                                    <a 
                                        :href="file.original_url" 
                                        target="_blank"
                                        class="p-2 rounded-lg bg-white dark:bg-slate-700 hover:text-brand-500 transition-colors shadow-sm"
                                    >
                                        <ExternalLink :size="14" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10 px-4 bg-slate-50 dark:bg-slate-900/20 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                        <ImageIcon :size="30" class="mx-auto text-slate-300 dark:text-slate-700 mb-2" />
                        <p class="text-xs font-bold text-slate-500">{{ __('No documents attached.') }}</p>
                    </div>
                </div>

                <!-- Support Representative -->
                <div v-if="deal.assignedToUser" class="bg-brand-600 rounded-xl p-8 shadow-xl shadow-brand-500/20 text-white overflow-hidden relative group">
                    <!-- Background Decoration -->
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-700"></div>
                    
                    <h4 class="text-[10px] uppercase font-black text-brand-200 tracking-[0.2em] mb-6 relative z-10">{{ __('Your Representative') }}</h4>
                    
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="relative">
                            <img 
                                :src="deal.assignedToUser.profile_photo_url || `https://ui-avatars.com/api/?name=${deal.assignedToUser.name}&background=random`" 
                                class="w-16 h-16 rounded-xl border-2 border-brand-400/50 shadow-lg object-cover"
                            >
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 border-2 border-brand-600 rounded-full"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h5 class="text-xl font-black truncate">{{ deal.assignedToUser.name }}</h5>
                            <p class="text-sm font-bold text-brand-200 flex items-center gap-1 mt-1">
                                <Mail :size="12" /> {{ __('Account Manager') }}
                            </p>
                        </div>
                    </div>
                    
                    <a 
                        :href="`mailto:${deal.assignedToUser.email}`" 
                        class="mt-8 flex items-center justify-center gap-2 w-full py-4 bg-white text-brand-600 rounded-xl font-black text-sm hover:bg-brand-50 transition-colors relative z-10"
                    >
                        <Mail :size="16" />
                        {{ __('Send Message') }}
                    </a>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>

<style scoped>
.prose :deep(p) {
    margin-bottom: 1rem;
}
.prose :deep(p:last-child) {
    margin-bottom: 0;
}
</style>
