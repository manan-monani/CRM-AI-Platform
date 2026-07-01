<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PropType, ref, watch } from 'vue';
import admin from '@/routes/admin';
import { debounce } from 'lodash';
import { PlusCircle, Search, Pencil, Trash2, Eye, DollarSign } from 'lucide-vue-next';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';

const props = defineProps({
    customers: Object as PropType<any>,
});

const search = ref('');

watch(search, debounce((value) => {
    router.get(admin.customers.index.url(), { search: value }, { preserveState: true, replace: true });
}, 300));

// Delete Logic
const showDeleteModal = ref(false);
const customerToDelete = ref<any>(null);
const processingDelete = ref(false);

const confirmDelete = (customer: any) => {
    customerToDelete.value = customer;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    if (!customerToDelete.value) return;
    
    processingDelete.value = true;
    router.delete(admin.customers.destroy.url(customerToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            customerToDelete.value = null;
        },
        onFinish: () => {
            processingDelete.value = false;
        }
    });
};
</script>

<template>
    <Head :title="__('Customers')" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 text-start">
                <div class="text-start">
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Customer Management') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Manage customer accounts and profiles.') }}</p>
                </div>
                <Link :href="admin.customers.create ? admin.customers.create.url() : '#'" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-xl shadow-brand-600/20 flex items-center transition-all active:scale-95">
                    <PlusCircle :size="18" class="me-2" /> {{ __('Add New Customer') }}
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
                        <input v-model="search" type="text" :placeholder="__('Search customers...')" class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 text-slate-700 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-500">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-start border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/20 border-b border-slate-100 dark:border-slate-700/50">
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Customer') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Contact') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-start">{{ __('Joined') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-end">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50 text-start">
                            <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-all duration-200 group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <img :src="customer.profile_image ? '/storage/' + customer.profile_image : customer.avatar || `https://ui-avatars.com/api/?name=${customer.name}&background=random`" class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 object-cover">
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ customer.name }}</p>
                                            <p class="text-[11px] text-slate-500">{{ customer.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs text-slate-700 dark:text-slate-300">{{ customer.customer_profile?.phone || __('N/A') }}</p>
                                    <p class="text-[11px] text-slate-500">{{ customer.customer_profile?.city || '' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs text-slate-700 dark:text-slate-300">{{ new Date(customer.created_at).toLocaleDateString() }}</p>
                                </td>
                                <td class="px-6 py-4 text-end">
                                    <div class="flex justify-end items-center gap-2">
                                        <Link :href="admin.deals.create.url() + '?customer_id=' + customer.id" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all flex items-center justify-center" :title="__('Create Deal')">
                                            <DollarSign :size="14" />
                                        </Link>
                                        <Link :href="admin.customers.show ? admin.customers.show.url(customer.id) : '#'" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all flex items-center justify-center" :title="__('View Details')">
                                            <Eye :size="14" />
                                        </Link>
                                        <Link :href="admin.customers.edit ? admin.customers.edit.url(customer.id) : '#'" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-all flex items-center justify-center">
                                            <Pencil :size="14" />
                                        </Link>
                                        <button @click="confirmDelete(customer)" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all flex items-center justify-center">
                                            <Trash2 :size="14" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                 <div v-if="customers.links.length > 3" class="p-6 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                      <div class="flex space-x-1">
                        <Link v-for="(link, key) in customers.links" :key="key" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 text-xs rounded-lg transition-colors border border-transparent" :class="link.active ? 'bg-brand-600 text-white' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700'" :preserve-state="true" />
                      </div>
                 </div>
            </div>
            
            <ConfirmationModal 
                :show="showDeleteModal" 
                :title="__('Delete Customer?')"
                :message="__('Are you sure you want to delete this customer? This action cannot be undone.')"
                :confirmText="__('Yes, Delete Customer')"
                :cancelText="__('No, Cancel')"
                type="danger"
                :processing="processingDelete"
                @close="showDeleteModal = false"
                @confirm="handleDelete"
            />
        </div>
    </AdminLayout>
</template>
