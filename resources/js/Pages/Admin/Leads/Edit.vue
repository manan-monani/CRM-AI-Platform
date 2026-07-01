<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import admin from '@/routes/admin';
import { ref, PropType } from 'vue';
import axios from 'axios';

const props = defineProps({
    lead: Object as PropType<any>,
    leadSources: Array as any,
});

const localSources = ref(props.leadSources || []);
const isAddingSource = ref(false);
const newSourceName = ref('');
const isSavingSource = ref(false);

const form = useForm({
    name: props.lead.name,
    email: props.lead.email,
    phone: props.lead.phone || '',
    company: props.lead.company || '',
    message: props.lead.message || '',
    source: props.lead.source,
    _method: 'PUT',
});

const addSource = async () => {
    if (!newSourceName.value.trim()) return;
    
    isSavingSource.value = true;
    try {
        const response = await axios.post(admin.leadSources.store.url(), {
            name: newSourceName.value
        });
        localSources.value.push(response.data);
        form.source = response.data.slug;
        newSourceName.value = '';
        isAddingSource.value = false;
    } catch (error: any) {
        console.error('Error adding source:', error);
        alert(error.response?.data?.message || 'Failed to add source');
    } finally {
        isSavingSource.value = false;
    }
};

const submit = () => {
    form.post(admin.leads.update.url(props.lead.id), {
        onSuccess: () => {},
    });
};
</script>

<template>
    <Head :title="__('Edit Lead')" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in max-w-2xl mx-auto pb-12">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                     <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                        <Link :href="admin.leads.index.url()" class="hover:text-brand-500">{{ __('Leads') }}</Link>
                        <ChevronRight :size="8" class="opacity-40 rtl:rotate-180" />
                        <span class="text-brand-500">{{ __('Edit Lead') }}</span>
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Edit Lead') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Update information for this potential customer.') }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Full Name') }}</label>
                            <input v-model="form.name" type="text" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Email Address') }}</label>
                            <input v-model="form.email" type="email" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Phone Number') }}</label>
                            <input v-model="form.phone" type="text" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                            <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">{{ form.errors.phone }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Company Name') }}</label>
                            <input v-model="form.company" type="text" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 block">{{ __('Lead Source') }}</label>
                            <button 
                                v-if="!isAddingSource" 
                                type="button" 
                                @click="isAddingSource = true"
                                class="text-[10px] font-bold text-brand-600 hover:text-brand-700 transition-colors uppercase tracking-wider"
                            >
                                + {{ __('Add Source') }}
                            </button>
                        </div>
                        
                        <div v-if="isAddingSource" class="flex gap-2 mb-3 animate-in slide-in-from-top-1 duration-200">
                            <input 
                                v-model="newSourceName" 
                                type="text" 
                                class="flex-1 px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium outline-none focus:border-brand-500"
                                :placeholder="__('Source Name')"
                                @keyup.enter="addSource"
                            >
                            <button 
                                type="button" 
                                @click="addSource"
                                :disabled="isSavingSource"
                                class="px-4 py-2 bg-brand-600 text-white rounded-xl text-xs font-bold hover:bg-brand-700 transition-all disabled:opacity-50"
                            >
                                {{ isSavingSource ? '...' : __('Save') }}
                            </button>
                            <button 
                                type="button" 
                                @click="isAddingSource = false; newSourceName = ''"
                                class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all"
                            >
                                {{ __('Cancel') }}
                            </button>
                        </div>

                        <select v-model="form.source" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white">
                            <option v-for="source in localSources" :key="source.id" :value="source.slug">
                                {{ source.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.source" class="text-red-500 text-xs mt-1">{{ form.errors.source }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Notes / Message') }}</label>
                        <textarea v-model="form.message" rows="4" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500" :placeholder="__('Any additional details about this lead...')"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Link :href="admin.leads.index.url()" class="px-6 py-3 rounded-xl border border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all text-center">
                            {{ __('Cancel') }}
                        </Link>
                        <button type="submit" :disabled="form.processing" class="bg-brand-600 text-white px-8 py-3 rounded-xl font-bold text-sm shadow-xl shadow-brand-600/20 hover:bg-brand-700 transition-all disabled:opacity-50">
                            {{ form.processing ? __('Saving...') : __('Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
