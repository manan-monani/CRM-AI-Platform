<script setup lang="ts">
import { useForm, Link, Head } from '@inertiajs/vue3';
import { computed, PropType } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/Core/InputError.vue';
import SearchableSelect from '@/Components/Core/SearchableSelect.vue';
import TextArea from '@/Components/Core/TextArea.vue';
import FilePreviewUploader from '@/Components/Common/FilePreviewUploader.vue';
import { wTrans as __ } from '@/Core/i18n';
import admin from '@/routes/admin';
import { 
    ChevronRight, 
    Briefcase, 
    DollarSign, 
    Calendar, 
    Percent, 
    FileText, 
    Target,
    LayoutGrid
} from 'lucide-vue-next';

const props = defineProps({
    leads: Array as PropType<any[]>,
    customers: Array as PropType<any[]>,
    users: Array as PropType<any[]>,
    preselected: Object as PropType<any>,
});

const form = useForm({
    title: '',
    value: '',
    stage: 'new',
    dealable_type: props.preselected?.dealable_type || 'App\\Models\\Lead',
    dealable_id: props.preselected?.dealable_id || '',
    expected_close_date: '',
    probability: 10,
    assigned_to: '',
    description: '',
    attachments: [] as File[],
});

const userOptions = computed(() => {
    return props.users?.map(user => ({
        value: user.id,
        label: user.name,
        image: user.profile_image ? `/storage/${user.profile_image}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random&color=fff`
    })) || [];
});

const entityOptions = computed(() => {
    const list = form.dealable_type === 'App\\Models\\Lead' ? props.leads : props.customers;
    return list?.map(item => ({
        value: item.id,
        label: item.name
    })) || [];
});

const submit = () => {
    form.post(admin.deals.store.url(), {
        forceFormData: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head :title="__('Create New Deal')" />

    <AdminLayout>
        <div class="space-y-8 animate-fade-in max-w-6xl mx-auto pb-20">
            <!-- Breadcrumbs & Header -->
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                    <Link :href="admin.deals.index.url()" class="hover:text-brand-500 transition-colors">{{ __('Deals') }}</Link>
                    <ChevronRight :size="8" class="opacity-40 rtl:rotate-180" />
                    <span class="text-brand-500">{{ __('Create Entry') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ __('Create New Deal') }}</h2>
                    <div class="px-4 py-1.5 bg-brand-500/10 text-brand-600 dark:text-brand-400 rounded-full text-[10px] font-bold uppercase tracking-widest border border-brand-500/20">
                        {{ __('New Pipeline Opportunity') }}
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left Column: Form Fields -->
                <div class="lg:col-span-8 space-y-8 order-1">
                    <!-- Section: Primary Information -->
                    <div class="bg-white dark:bg-slate-800 p-8 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm relative">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="p-3 bg-blue-500/10 text-blue-500 rounded-xl">
                                <Briefcase :size="24" />
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white leading-tight">{{ __('Primary Information') }}</h3>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Basic Deal details') }}</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="text-[11px] font-black text-slate-500 dark:text-slate-400 mb-2 block uppercase tracking-widest">{{ __('Deal Title') }}</label>
                                <input 
                                    v-model="form.title" 
                                    type="text" 
                                    required 
                                    placeholder="e.g. Q1 Marketing Campaign for TechNova"
                                    class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400/80"
                                >
                                <InputError :message="form.errors.title" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="text-[11px] font-black text-slate-500 dark:text-slate-400 mb-2 block uppercase tracking-widest">{{ __('Related To') }}</label>
                                    <div class="relative group">
                                         <select 
                                            v-model="form.dealable_type" 
                                            class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-semibold outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white appearance-none"
                                            @change="form.dealable_id = ''"
                                        >
                                            <option value="App\Models\Lead">{{ __('Lead') }}</option>
                                            <option value="App\Models\User">{{ __('Customer') }}</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-slate-400">
                                            <ChevronRight :size="16" class="rotate-90" />
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-[11px] font-black text-slate-500 dark:text-slate-400 mb-2 block uppercase tracking-widest">{{ __('Select Entity') }}</label>
                                    <SearchableSelect
                                        v-model="form.dealable_id"
                                        :options="entityOptions"
                                        :placeholder="__('Search for a lead or customer...')"
                                        :error="form.errors.dealable_id"
                                    />
                                    <InputError :message="form.errors.dealable_id" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <label class="text-[11px] font-black text-slate-500 dark:text-slate-400 mb-2 block uppercase tracking-widest">{{ __('Assign Sales Representative') }}</label>
                                <SearchableSelect
                                    v-model="form.assigned_to"
                                    :options="userOptions"
                                    :placeholder="__('Search for a rep...')"
                                    :error="form.errors.assigned_to"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Section: Financials & Success Metrics -->
                    <div class="bg-white dark:bg-slate-800 p-8 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm relative">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="p-3 bg-emerald-500/10 text-emerald-500 rounded-xl">
                                <DollarSign :size="24" />
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white leading-tight">{{ __('Financials & Success') }}</h3>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Revenue and probability') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[11px] font-black text-slate-500 dark:text-slate-400 mb-2 block uppercase tracking-widest">{{ __('Contract Value') }}</label>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-brand-500 font-bold">
                                            <span class="text-sm">$</span>
                                        </div>
                                        <input 
                                            v-model="form.value" 
                                            type="number" 
                                            step="0.01" 
                                            required
                                            class="w-full pl-12 pr-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-bold outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white"
                                        >
                                    </div>
                                    <InputError :message="form.errors.value" class="mt-2" />
                                </div>

                                <div>
                                    <label class="text-[11px] font-black text-slate-500 dark:text-slate-400 mb-2 block uppercase tracking-widest">{{ __('Closing Probability (%)') }}</label>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-slate-400 group-focus-within:text-brand-500 transition-colors">
                                            <Percent :size="14" class="stroke-[3]" />
                                        </div>
                                        <input 
                                            v-model="form.probability" 
                                            type="number" 
                                            min="0" 
                                            max="100"
                                            class="w-full pl-12 pr-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-bold outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white"
                                        >
                                    </div>
                                    <InputError :message="form.errors.probability" class="mt-2" />
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label class="text-[11px] font-black text-slate-500 dark:text-slate-400 mb-2 block uppercase tracking-widest">{{ __('Expected Close Date') }}</label>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-slate-400 group-focus-within:text-brand-500 transition-colors">
                                            <Calendar :size="16" class="stroke-[2.5]" />
                                        </div>
                                        <input 
                                            v-model="form.expected_close_date" 
                                            type="date" 
                                            required
                                            class="w-full pl-12 pr-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-bold outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white"
                                        >
                                    </div>
                                    <InputError :message="form.errors.expected_close_date" class="mt-2" />
                                </div>

                                <div>
                                    <label class="text-[11px] font-black text-slate-500 dark:text-slate-400 mb-2 block uppercase tracking-widest">{{ __('Pipeline Stage') }}</label>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-slate-400 group-focus-within:text-brand-500 transition-colors">
                                            <Target :size="16" class="stroke-[2.5]" />
                                        </div>
                                        <select 
                                            v-model="form.stage" 
                                            class="w-full pl-12 pr-6 py-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-bold outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white appearance-none"
                                        >
                                            <option value="lead">{{ __('Lead Stage') }}</option>
                                            <option value="new">{{ __('New Opportunity') }}</option>
                                            <option value="qualified">{{ __('Qualified') }}</option>
                                            <option value="proposal">{{ __('Value Proposal') }}</option>
                                            <option value="negotiation">{{ __('Final Negotiation') }}</option>
                                            <option value="won">{{ __('Closed Won') }}</option>
                                            <option value="lost">{{ __('Closed Lost') }}</option>
                                        </select>
                                         <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-slate-400">
                                            <ChevronRight :size="16" class="rotate-90" />
                                        </div>
                                    </div>
                                    <InputError :message="form.errors.stage" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Attachments & Actions -->
                <div class="lg:col-span-4 space-y-8 lg:sticky lg:top-24 order-3 lg:order-2">
                    <!-- Section: Media & File Attachments -->
                    <div class="bg-white dark:bg-slate-800 p-8 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="p-3 bg-brand-500/10 text-brand-500 rounded-xl">
                                <FileText :size="24" />
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white leading-tight">{{ __('Media Assets') }}</h3>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Documents & Images') }}</p>
                            </div>
                        </div>

                        <FilePreviewUploader v-model="form.attachments" />
                        <InputError :message="form.errors.attachments" class="mt-4" />
                        <div v-for="(error, index) in form.errors" :key="index">
                            <InputError v-if="index.toString().startsWith('attachments.')" :message="error" class="mt-1" />
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="bg-slate-900 dark:bg-slate-950 p-8 rounded-xl shadow-2xl shadow-slate-900/20 relative overflow-hidden group">
                        <!-- Decorative glow -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-brand-500/20 rounded-full blur-[60px] group-hover:bg-brand-500/30 transition-all"></div>
                        
                        <div class="relative z-10 space-y-6">
                            <div class="space-y-2">
                                <h4 class="font-bold text-white text-lg leading-tight">{{ __('Ready to Launch?') }}</h4>
                                <p class="text-xs text-slate-400 font-medium">{{ __('Review all details before submitting this deal to the CRM pipeline.') }}</p>
                            </div>
                            
                            <div class="flex flex-col gap-3">
                                <button 
                                    type="submit" 
                                    :disabled="form.processing" 
                                    class="w-full bg-brand-500 text-white px-8 py-5 rounded-xl font-black text-sm shadow-xl shadow-brand-500/20 hover:bg-brand-400 hover:-translate-y-0.5 active:translate-y-0 transition-all disabled:opacity-50 inline-flex items-center justify-center gap-3"
                                >
                                    <span v-if="form.processing" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        {{ __('Processing...') }}
                                    </span>
                                    <template v-else>
                                        {{ __('Register Asset') }}
                                        <LayoutGrid :size="18" class="stroke-[2.5]" />
                                    </template>
                                </button>
                                
                                <Link :href="admin.deals.index.url()" class="w-full px-6 py-4 rounded-xl border border-white/10 font-bold text-xs text-slate-400 hover:text-white hover:bg-white/5 transition-all text-center uppercase tracking-widest">
                                    {{ __('Discard Draft') }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Full-Width: Description -->
                <div class="lg:col-span-8 space-y-8 order-2 lg:order-3">
                     <div class="bg-white dark:bg-slate-800 p-8 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm relative">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="p-3 bg-indigo-500/10 text-indigo-500 rounded-xl">
                                <FileText :size="24" />
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white leading-tight">{{ __('Internal Notes') }}</h3>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ __('Detailed deal context') }}</p>
                            </div>
                        </div>

                        <div>
                            <TextArea 
                                v-model="form.description" 
                                rows="6"
                                placeholder="Add strategic notes, next steps, or project requirements here..."
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Custom Hide Scrollbar but keep functionality */
textarea::-webkit-scrollbar {
    width: 6px;
}
textarea::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.dark textarea::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>
