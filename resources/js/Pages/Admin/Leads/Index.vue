```
<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PropType, ref, watch } from 'vue';
import admin from '@/routes/admin';
import { debounce } from 'lodash';
import { PlusCircle, Search, Eye, Pencil, Trash2, UserCheck, TrendingUp, ChevronRight, Phone, Mail, Building, Clock, Activity, ArrowLeft, MessageSquare, Info, Briefcase } from 'lucide-vue-next';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import { wTrans as __ } from '@/Core/i18n';

const props = defineProps({
    leads: Object as PropType<any>,
});

const search = ref('');

watch(search, debounce((value) => {
    router.get(admin.leads.index.url(), { search: value }, { preserveState: true, replace: true });
}, 300));

// Delete Logic
const showDeleteModal = ref(false);
const leadToDelete = ref<any>(null);
const processingDelete = ref(false);

const confirmDelete = (lead: any) => {
    if (lead.status === 'converted') {
        alert(__('Cannot delete a converted lead.'));
        return;
    }
    leadToDelete.value = lead;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    if (!leadToDelete.value) return;
    
    processingDelete.value = true;
    router.delete(admin.leads.destroy.url(leadToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            leadToDelete.value = null;
        },
        onFinish: () => {
            processingDelete.value = false;
        }
    });
};

// Convert to Customer Logic
const showConvertModal = ref(false);
const leadToConvert = ref<any>(null);
const processingConvert = ref(false);

const confirmConvert = (lead: any) => {
    leadToConvert.value = lead;
    showConvertModal.value = true;
};

const handleConvert = () => {
    if (!leadToConvert.value) return;
    
    processingConvert.value = true;
    router.post(admin.leads.convert.url(leadToConvert.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            showConvertModal.value = false;
            leadToConvert.value = null;
        },
        onFinish: () => {
            processingConvert.value = false;
        }
    });
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
</script>

<template>
    <Head :title="__('Leads')" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 text-start">
                <div class="text-start">
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Lead Management') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Track, qualify, and convert leads to customers.') }}</p>
                </div>
                <Link :href="admin.leads.create ? admin.leads.create.url() : '#'" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-xl shadow-brand-600/20 flex items-center transition-all active:scale-95">
                    <PlusCircle :size="18" class="me-2" /> {{ __('Add New Lead') }}
                </Link>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden">
                 <!-- Search -->
                <div class="p-4 border-b border-slate-100 dark:border-slate-700">
                    <div class="relative w-full sm:w-64">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search :size="16" class="text-slate-400" />
                         </div>
                        <input v-model="search" type="text" :placeholder="__('Search leads...')" class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 text-slate-700 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-500">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-start border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/20 border-b border-slate-100 dark:border-slate-700/50">
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Lead Info') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Source') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Created') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-end">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50 text-start">
                            <tr v-for="lead in leads.data" :key="lead.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all duration-200 group">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ lead.name }}</p>
                                        <p class="text-[11px] text-slate-500">{{ lead.email }}</p>
                                        <p v-if="lead.company" class="text-[11px] text-slate-400">{{ lead.company }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 capitalize">
                                        {{ lead.lead_source?.name ?? (lead.source ? lead.source.replace('_', ' ') : '') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs px-2 py-1 rounded-lg font-semibold capitalize" :class="getStatusColor(lead.status)">
                                        {{ lead.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs text-slate-700 dark:text-slate-300">{{ new Date(lead.created_at).toLocaleDateString() }}</p>
                                </td>
                                <td class="px-6 py-4 text-end">
                                    <div class="flex justify-end items-center gap-2">
                                        <Link :href="admin.leads.show ? admin.leads.show.url(lead.id) : '#'" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all flex items-center justify-center">
                                            <Eye :size="14" />
                                        </Link>
                                        <Link :href="admin.deals.create.url({ query: { lead_id: lead.id } })" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-all flex items-center justify-center" :title="__('Create Deal')">
                                            <Briefcase :size="14" />
                                        </Link>
                                        <!-- Show convert button if: not converted OR converted but customer was deleted -->
                                        <button 
                                            v-if="lead.status !== 'converted' || (lead.status === 'converted' && !lead.converted_customer)" 
                                            @click="confirmConvert(lead)" 
                                            class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all flex items-center justify-center" 
                                            :title="lead.status === 'converted' ? __('Reconvert to Customer (Original customer was deleted)') : __('Convert to Customer')"
                                        >
                                            <UserCheck :size="14" />
                                        </button>
                                        <Link v-if="lead.status !== 'converted'" :href="admin.leads.edit ? admin.leads.edit.url(lead.id) : '#'" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-all flex items-center justify-center">
                                            <Pencil :size="14" />
                                        </Link>
                                        <button v-if="lead.status !== 'converted'" @click="confirmDelete(lead)" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all flex items-center justify-center">
                                            <Trash2 :size="14" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                 <div v-if="leads.links.length > 3" class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                      <div class="flex space-x-1">
                        <Link v-for="(link, key) in leads.links" :key="key" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded-lg transition-colors border border-transparent" :class="link.active ? 'bg-brand-600 text-white' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700'" :preserve-state="true" />
                      </div>
                 </div>
            </div>
            
            <!-- Delete Confirmation Modal -->
            <ConfirmationModal 
                :show="showDeleteModal" 
                :title="__('Delete Lead?')"
                :message="__('Are you sure you want to delete this lead? This action cannot be undone.')"
                :confirmText="__('Yes, Delete Lead')"
                :cancelText="__('No, Cancel')"
                type="danger"
                :processing="processingDelete"
                @close="showDeleteModal = false"
                @confirm="handleDelete"
            />

            <!-- Convert to Customer Confirmation Modal -->
            <ConfirmationModal 
                :show="showConvertModal" 
                :title="__('Convert Lead to Customer?')"
                :message="__('This will create a new customer account from this lead. The lead status will be updated to converted.')"
                :confirmText="__('Yes, Convert to Customer')"
                :cancelText="__('No, Cancel')"
                type="info"
                :processing="processingConvert"
                @close="showConvertModal = false"
                @confirm="handleConvert"
            />
        </div>
    </AdminLayout>
</template>
