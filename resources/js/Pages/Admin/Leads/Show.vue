```
<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PropType, ref } from 'vue';
import { ChevronRight, Pencil, Phone, Mail, Building, Clock, Activity, ArrowLeft, UserCheck, MessageSquare, Info, Briefcase } from 'lucide-vue-next';
import admin from '@/routes/admin';
import { wTrans as __ } from '@/Core/i18n';

const props = defineProps({
    lead: Object as PropType<any>,
});

const processingStatus = ref(false);

const updateStatus = (newStatus: string) => {
    processingStatus.value = true;
    router.patch(admin.leads.status.url(props.lead.id), {
        status: newStatus
    }, {
        onFinish: () => processingStatus.value = false
    });
};

const convertToCustomer = () => {
    if (confirm(__('Convert this lead to a customer?'))) {
        router.post(admin.leads.convert.url(props.lead.id));
    }
};

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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatLabel = (slug: string) => {
    return slug
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};
</script>

<template>
    <Head :title="lead.name" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in max-w-5xl mx-auto pb-12">
            <!-- Breadcrumbs & Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 text-start">
                        <Link :href="admin.leads.index.url()" class="hover:text-brand-500">{{ __('Leads') }}</Link>
                        <ChevronRight :size="8" class="opacity-40 rtl:rotate-180" />
                        <span class="text-brand-500">{{ lead.name }}</span>
                    </div>
                    <div class="flex items-center gap-4 text-start">
                        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ lead.name }}</h2>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider" :class="getStatusColor(lead.status)">
                            {{ lead.status }}
                        </span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 text-start">{{ __('Lead captured from') }} <span class="capitalize font-bold">{{ lead.lead_source?.name ?? lead.source.replace('_', ' ') }}</span></p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="admin.leads.index.url()" class="bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-4 py-2.5 rounded-xl font-bold text-xs border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all flex items-center gap-2">
                        <ArrowLeft :size="14" /> {{ __('Back') }}
                    </Link>
                    <Link :href="admin.deals.create.url({ query: { lead_id: lead.id } })" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-xl shadow-indigo-600/20 flex items-center transition-all active:scale-95 gap-2">
                        <Briefcase :size="14" /> {{ __('Create Deal') }}
                    </Link>
                    <button v-if="lead.status !== 'converted'" @click="convertToCustomer" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-xl shadow-emerald-600/20 flex items-center transition-all active:scale-95 gap-2">
                        <UserCheck :size="14" /> {{ __('Convert to Customer') }}
                    </button>
                    <Link v-if="lead.status !== 'converted'" :href="admin.leads.edit.url(lead.id)" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-xl shadow-brand-600/20 flex items-center transition-all active:scale-95 gap-2">
                        <Pencil :size="14" /> {{ __('Edit Lead') }}
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Status & Quick Info -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden p-8 space-y-6 text-start">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest mb-4">{{ __('Manage Status') }}</p>
                            <div class="grid grid-cols-1 gap-2">
                                <button 
                                    v-for="status in ['new', 'contacted', 'qualified', 'lost']" 
                                    :key="status"
                                    @click="updateStatus(status)"
                                    :disabled="processingStatus || lead.status === 'converted' || lead.status === status"
                                    class="w-full px-4 py-2.5 rounded-xl font-bold text-xs transition-all flex items-center justify-between group"
                                    :class="lead.status === status ? getStatusColor(status) : 'bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800/80'"
                                >
                                    <span class="capitalize">{{ status }}</span>
                                    <div v-if="lead.status === status" class="w-1.5 h-1.5 rounded-full bg-current"></div>
                                </button>
                            </div>
                        </div>

                        <div v-if="lead.status === 'converted' && lead.converted_customer" class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30">
                            <div class="flex items-center gap-3 mb-2">
                                <UserCheck :size="16" class="text-emerald-600" />
                                <span class="text-sm font-black text-emerald-900 dark:text-emerald-400 capitalize">{{ __('Lead Converted') }}</span>
                            </div>
                            <Link :href="admin.customers.show.url(lead.converted_to_customer_id)" class="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
                                {{ __('Go to Customer Profile') }} <ChevronRight :size="12" />
                            </Link>
                        </div>

                        <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                             <div class="flex items-center gap-3">
                                <Mail :size="16" class="text-slate-400" />
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ lead.email }}</span>
                            </div>
                            <div v-if="lead.phone" class="flex items-center gap-3">
                                <Phone :size="16" class="text-slate-400" />
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ lead.phone }}</span>
                            </div>
                             <div class="flex items-center gap-3">
                                <Clock :size="16" class="text-slate-400" />
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ formatDate(lead.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden text-start">
                        <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-700 flex items-center gap-3">
                            <Info :size="20" class="text-brand-500" />
                            <h4 class="text-lg font-black text-slate-900 dark:text-white">{{ __('Lead Information') }}</h4>
                        </div>
                        <div class="p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Company') }}</p>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ lead.company || __('N/A') }}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Source') }}</p>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 capitalize bg-slate-100 dark:bg-slate-900 px-2 py-1 rounded-lg">
                                        {{ lead.lead_source?.name ?? lead.source.replace('_', ' ') }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-slate-400">
                                    <MessageSquare :size="16" />
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Message / Inquiry') }}</p>
                                </div>
                                <div class="p-5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed italic">
                                        "{{ lead.message || __('No message provided.') }}"
                                    </p>
                                </div>
                            </div>

                            <!-- Custom Fields (Key-Value) -->
                            <div v-if="lead.custom_data && Object.keys(lead.custom_data).length > 0" class="pt-6 border-t border-slate-100 dark:border-slate-700">
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                                    <Info :size="16" class="text-brand-500" />
                                    {{ __('Additional Information') }}
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div v-for="(value, key) in lead.custom_data" :key="key" class="space-y-1">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ formatLabel(String(key)) }}</p>
                                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                            {{ value || __('N/A') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversion Summary (if applicable) -->
                     <div v-if="lead.status === 'converted'" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden text-start">
                        <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-700 flex items-center gap-3">
                            <Activity :size="20" class="text-emerald-500" />
                            <h4 class="text-lg font-black text-slate-900 dark:text-white">{{ __('Conversion Summary') }}</h4>
                        </div>
                        <div class="p-8">
                             <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                                {{ __('This lead has been successfully converted into a customer account.') }}
                             </p>
                             <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                 <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                                     <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Converted At') }}</p>
                                     <p class="text-xs font-bold text-slate-900 dark:text-white">{{ new Date(lead.updated_at).toLocaleDateString() }}</p>
                                 </div>
                             </div>
                        </div>
                     </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
