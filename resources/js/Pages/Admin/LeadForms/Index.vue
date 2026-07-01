<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PropType, ref, watch } from 'vue';
import admin from '@/routes/admin';
import { debounce } from 'lodash';
import { PlusCircle, Search, ExternalLink, Pencil, Trash2, FileText, CheckCircle, Loader2 } from 'lucide-vue-next';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';

const props = defineProps({
    forms: Object as PropType<any>,
});

const search = ref('');

// search logic if implemented in backend, currently not
// watch(search, debounce((value) => {
//     router.get(admin.leadGenerationForm.index.url(), { search: value }, { preserveState: true, replace: true });
// }, 300));

// Delete Logic
const showDeleteModal = ref(false);
const formToDelete = ref<any>(null);
const processingDelete = ref(false);

const confirmDelete = (form: any) => {
    formToDelete.value = form;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    if (!formToDelete.value) return;
    
    processingDelete.value = true;
    router.delete(admin.leadGenerationForm.destroy.url(formToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            formToDelete.value = null;
        },
        onFinish: () => {
            processingDelete.value = false;
        }
    });
};

const getPublicUrl = (slug: string) => {
    return `${window.location.origin}/form/${slug}`;
};

const settingDefault = ref<number | null>(null);
const setDefault = (form: any) => {
    if (form.is_default || form.status !== 'active') return;
    
    settingDefault.value = form.id;
    router.patch(route('admin.lead-generation-form.default', form.id), {}, {
        onFinish: () => {
            settingDefault.value = null;
        }
    });
};
</script>

<template>
    <Head :title="__('Lead Forms')" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 text-start">
                <div class="text-start">
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Lead Forms') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Manage your lead capture forms.') }}</p>
                </div>
                <Link :href="admin.leadGenerationForm.create.url()" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-xl shadow-brand-600/20 flex items-center transition-all active:scale-95">
                    <PlusCircle :size="18" class="me-2" /> {{ __('Create New Form') }}
                </Link>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden">
                 <!-- Search (Placeholder) -->
                <div class="p-4 border-b border-slate-100 dark:border-slate-700 hidden">
                    <div class="relative w-full sm:w-64">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search :size="16" class="text-slate-400" />
                         </div>
                        <input v-model="search" type="text" :placeholder="__('Search forms...')" class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 text-slate-700 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-500">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-start border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/20 border-b border-slate-100 dark:border-slate-700/50">
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Form Name') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Leads') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-end">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50 text-start">
                            <tr v-for="form in forms.data" :key="form.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all duration-200 group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                                            <FileText :size="20" />
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ form.name }}</p>
                                                <span v-if="form.is_default" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/50 uppercase tracking-wider">
                                                    <CheckCircle :size="10" /> {{ __('Default') }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-500">{{ form.title || 'No title set' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ form.leads_count }} Leads
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="{'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': form.status === 'active', 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': form.status === 'inactive'}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                                        {{ form.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-end">
                                    <div class="flex justify-end items-center gap-2">
                                        <button 
                                            v-if="!form.is_default && form.status === 'active'" 
                                            @click="setDefault(form)"
                                            :disabled="settingDefault !== null"
                                            class="px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all flex items-center gap-2"
                                            :class="settingDefault === form.id ? 'bg-slate-100 text-slate-400' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 dark:hover:text-brand-400'"
                                        >
                                            <Loader2 v-if="settingDefault === form.id" :size="12" class="animate-spin" />
                                            {{ settingDefault === form.id ? __('Setting...') : __('Set Default') }}
                                        </button>
                                        <a :href="getPublicUrl(form.slug)" target="_blank" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-all flex items-center justify-center" title="View Public Page">
                                            <ExternalLink :size="14" />
                                        </a>
                                        <Link :href="admin.leadGenerationForm.edit.url(form.id)" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-all flex items-center justify-center">
                                            <Pencil :size="14" />
                                        </Link>
                                        <button 
                                            @click="confirmDelete(form)" 
                                            :disabled="form.is_default"
                                            :class="form.is_default ? 'opacity-30 cursor-not-allowed text-slate-400' : 'text-slate-500 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20'"
                                            class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 transition-all flex items-center justify-center"
                                            :title="form.is_default ? __('Default form cannot be deleted') : __('Delete Form')"
                                        >
                                            <Trash2 :size="14" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="forms.data.length === 0">
                                <td colspan="4" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="w-20 h-20 rounded-xl bg-slate-50 dark:bg-slate-900 flex items-center justify-center text-slate-300 dark:text-slate-700 mb-6">
                                            <FileText :size="40" stroke-width="1.5" />
                                        </div>
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('No lead forms found') }}</h3>
                                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-8 px-4">
                                            {{ __('Create your first lead capture form to start generating leads from your website or landing pages.') }}
                                        </p>
                                        <Link :href="admin.leadGenerationForm.create.url()" class="inline-flex items-center px-6 py-3 rounded-xl bg-brand-600 text-white font-bold text-sm hover:bg-brand-700 transition-all active:scale-95 shadow-lg shadow-brand-600/20">
                                            <PlusCircle :size="18" class="mr-2" />
                                            {{ __('Create First Form') }}
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                 <div v-if="forms.links.length > 3" class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                     <div class="flex space-x-1">
                        <Link v-for="(link, key) in forms.links" :key="key" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded-lg transition-colors border border-transparent" :class="link.active ? 'bg-brand-600 text-white' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700'" :preserve-state="true" />
                     </div>
                 </div>
            </div>
            
            <ConfirmationModal 
                :show="showDeleteModal" 
                :title="__('Delete Form?')"
                :message="__('Are you sure you want to delete this form? All associated data will be deleted.')"
                :confirmText="__('Yes, Delete Form')"
                :cancelText="__('No, Cancel')"
                type="danger"
                :processing="processingDelete"
                @close="showDeleteModal = false"
                @confirm="handleDelete"
            />
        </div>
    </AdminLayout>
</template>
