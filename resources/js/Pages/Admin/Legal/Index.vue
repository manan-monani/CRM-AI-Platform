<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FileText, Save, Scale } from 'lucide-vue-next';
import Toast from '@/Components/Common/Toast.vue';
import RichTextEditor from '@/Components/Common/RichTextEditor.vue';

import { update } from '@/routes/admin/legal';

const props = defineProps<{
    settings: Record<string, string>;
}>();

const tabs = [
    { id: 'privacy_policy', label: 'Privacy Policy' },
    { id: 'terms_and_conditions', label: 'Terms & Conditions' },
    { id: 'about_us', label: 'About Us' },
];

const activeTab = ref(tabs[0].id);

const form = useForm({
    privacy_policy: props.settings.privacy_policy || '',
    terms_and_conditions: props.settings.terms_and_conditions || '',
    about_us: props.settings.about_us || '',
});

const submit = () => {
    form.post(update.url(), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="__('Legal Center')" />
    <AdminLayout>
        <Toast />
        <div class="space-y-6 animate-fade-in max-w-5xl mx-auto w-full p-4 lg:p-0">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-2">
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">{{ __('Legal Center') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Craft and manage your business legal documents and policies.') }}</p>
                </div>
            </div>

            <!-- Content -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex flex-col lg:flex-row min-h-[600px]">
                    <!-- Sidebar Tabs -->
                    <div class="w-full lg:w-64 bg-slate-50/50 dark:bg-slate-800/30 border-e border-slate-200 dark:border-slate-800 p-4 space-y-2">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            class="w-full text-start px-4 py-2 rounded-xl font-bold text-sm transition-all flex items-center gap-3"
                            :class="activeTab === tab.id 
                                ? 'bg-white dark:bg-slate-800 text-brand-600 dark:text-brand-400 shadow-sm ring-1 ring-slate-200 dark:ring-slate-700' 
                                : 'text-slate-500 hover:bg-white/50 dark:hover:bg-slate-800/50'"
                        >
                            <Scale :size="16" v-if="tab.id.includes('policy')" />
                            <FileText :size="16" v-else />
                            {{ __(tab.label) }}
                        </button>
                    </div>

                    <!-- Editor Area -->
                    <div class="flex-1 p-6 lg:p-8 bg-white dark:bg-slate-900/50">
                        <div v-for="tab in tabs" :key="tab.id" v-show="activeTab === tab.id" class="h-full flex flex-col space-y-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ __(tab.label) }}</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Edit the content for your') }} {{ __(tab.label) }}.</p>
                                </div>
                                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-brand-50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-300 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                    <FileText :size="12" />
                                    {{ __('Live Section') }}
                                </div>
                            </div>

                            <div class="flex-1 min-h-[500px] rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm transition-all focus-within:ring-2 focus-within:ring-brand-500/20 focus-within:border-brand-500/30">
                                <RichTextEditor v-model="(form as any)[tab.id]" />
                            </div>

                            <div class="flex justify-end pt-4">
                                <button 
                                    @click="submit"
                                    :disabled="form.processing"
                                    class="bg-brand-600 dark:bg-brand-500 text-white px-8 py-3 rounded-xl font-black text-sm shadow-xl shadow-brand-500/20 hover:shadow-brand-500/40 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center gap-2.5"
                                >
                                    <Save :size="18" />
                                    {{ form.processing ? __('Saving...') : __('Save Changes') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
