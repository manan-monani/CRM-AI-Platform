<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { PropType } from 'vue';
import { ChevronRight, Pencil, Phone, Mail, Building, MapPin, Calendar, Clock, Activity, ArrowLeft, DollarSign } from 'lucide-vue-next';
import admin from '@/routes/admin';

const props = defineProps({
    customer: Object as PropType<any>,
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head :title="customer.name" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in max-w-5xl mx-auto pb-12">
            <!-- Breadcrumbs & Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                        <Link :href="admin.customers.index.url()" class="hover:text-brand-500">{{ __('Customers') }}</Link>
                        <ChevronRight :size="8" class="opacity-40 rtl:rotate-180" />
                        <span class="text-brand-500">{{ customer.name }}</span>
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ customer.name }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('View customer profile and activity details.') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="admin.customers.index.url()" class="bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-4 py-2.5 rounded-xl font-bold text-xs border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all flex items-center gap-2">
                        <ArrowLeft :size="14" /> {{ __('Back') }}
                    </Link>
                    <Link :href="admin.deals.create.url() + '?customer_id=' + customer.id" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-xl shadow-emerald-600/20 flex items-center transition-all active:scale-95 gap-2">
                        <DollarSign :size="14" /> {{ __('Create Deal') }}
                    </Link>
                    <Link :href="admin.customers.edit.url(customer.id)" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-xl shadow-brand-600/20 flex items-center transition-all active:scale-95 gap-2">
                        <Pencil :size="14" /> {{ __('Edit Profile') }}
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Profile Overview -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden p-8 flex flex-col items-center text-center">
                        <img :src="customer.profile_image ? '/storage/' + customer.profile_image : customer.avatar || `https://ui-avatars.com/api/?name=${customer.name}&background=random`" class="w-32 h-32 rounded-xl object-cover mb-4 border-4 border-slate-50 dark:border-slate-900 shadow-xl">
                        <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ customer.name }}</h3>
                        <p class="text-sm font-medium text-slate-500 mb-6">{{ customer.email }}</p>
                        
                        <div class="w-full space-y-4 text-start">
                            <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center">
                                    <Activity :size="20" />
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">{{ __('Account Status') }}</p>
                                    <p class="text-xs font-bold text-slate-900 dark:text-white capitalize">{{ customer.status || 'Active' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Info -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-700">
                            <h4 class="text-lg font-black text-slate-900 dark:text-white">{{ __('Customer Details') }}</h4>
                        </div>
                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-slate-400 mb-1">
                                    <Mail :size="14" /> <span class="text-[10px] font-bold uppercase tracking-widest">{{ __('Email') }}</span>
                                </div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ customer.email }}</p>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-slate-400 mb-1">
                                    <Phone :size="14" /> <span class="text-[10px] font-bold uppercase tracking-widest">{{ __('Phone') }}</span>
                                </div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ customer.customer_profile?.phone || __('N/A') }}</p>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-slate-400 mb-1">
                                    <Building :size="14" /> <span class="text-[10px] font-bold uppercase tracking-widest">{{ __('Company') }}</span>
                                </div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ customer.customer_profile?.company || __('Individual') }}</p>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-slate-400 mb-1">
                                    <MapPin :size="14" /> <span class="text-[10px] font-bold uppercase tracking-widest">{{ __('Location') }}</span>
                                </div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ [customer.customer_profile?.city, customer.customer_profile?.address].filter(Boolean).join(', ') || __('N/A') }}</p>
                            </div>
                            <div class="space-y-1 text-start">
                                <div class="flex items-center gap-2 text-slate-400 mb-1">
                                    <Calendar :size="14" /> <span class="text-[10px] font-bold uppercase tracking-widest">{{ __('Joined Date') }}</span>
                                </div>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ formatDate(customer.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Logs (Placeholder/Summary) -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center text-start">
                            <h4 class="text-lg font-black text-slate-900 dark:text-white">{{ __('Recent Activity') }}</h4>
                            <Activity :size="20" class="text-slate-400" />
                        </div>
                        <div class="p-8">
                            <div v-if="customer.activity_logs?.length" class="space-y-6">
                                <div v-for="log in customer.activity_logs.slice(0, 5)" :key="log.id" class="flex gap-4">
                                    <div class="mt-1">
                                        <div class="w-2 h-2 rounded-full bg-brand-500"></div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ log.description }}</p>
                                        <div class="flex items-center gap-2 mt-1 text-slate-400">
                                            <Clock :size="12" />
                                            <span class="text-[10px] font-bold uppercase tracking-widest">{{ new Date(log.created_at).toLocaleString() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-12">
                                <Activity :size="48" class="mx-auto text-slate-200 dark:text-slate-700 mb-4" />
                                <p class="text-slate-500 font-medium">{{ __('No recent activity recorded.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
