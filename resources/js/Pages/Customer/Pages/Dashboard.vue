<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Package, Clock, Settings } from 'lucide-vue-next';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { wTrans as __ } from '@/Core/i18n';
import customer from '@/routes/customer';

const props = defineProps<{
    auth: any;
    dealsCount: number;
    lastActivity: string;
}>();
</script>

<template>
    <Head :title="__('Customer Dashboard')" />

    <CustomerLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ __('Welcome back,') }} {{ auth.user.name }}!</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __("Here's what's happening with your account today.") }}</p>
                </div>
                <Link :href="customer.deals.create.url()" class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 focus:bg-brand-700 active:bg-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <Package class="w-4 h-4 mr-2" />
                    {{ __('Create Deal') }}
                </Link>
            </div>
        </template>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm transition-all hover:shadow-xl hover:shadow-brand-500/5 group">
                <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-900/20 text-brand-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-all">
                    <Package :size="24" />
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">{{ __('Active Deals') }}</p>
                <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ dealsCount }}</h3>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm transition-all hover:shadow-xl hover:shadow-brand-500/5 group">
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-all">
                    <Clock :size="24" />
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">{{ __('Last Activity') }}</p>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-2">{{ lastActivity }}</h3>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm transition-all hover:shadow-xl hover:shadow-brand-500/5 group">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-all">
                    <Settings :size="24" />
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">{{ __('Account Status') }}</p>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ __('Active') }}
                </h3>
            </div>
        </div>

        <!-- Recent Activity Placeholder -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-8 border-b border-slate-50 dark:border-slate-700">
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ __('Recent Activity') }}</h2>
            </div>
            <div class="p-8 text-center py-20">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-900/50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                    <Clock :size="32" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('No recent activity') }}</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm max-w-xs mx-auto">
                    {{ __("When you interact with our platform, your history will appear here.") }}
                </p>
            </div>
        </div>
    </CustomerLayout>
</template>
