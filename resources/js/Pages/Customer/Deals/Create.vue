<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/Core/PrimaryButton.vue';
import InputLabel from '@/Components/Core/InputLabel.vue';
import TextInput from '@/Components/Core/TextInput.vue';
import TextArea from '@/Components/Core/TextArea.vue';
import InputError from '@/Components/Core/InputError.vue';
import { wTrans as __ } from '@/Core/i18n';
import { Package, Calendar, ClipboardList } from 'lucide-vue-next';
import FilePreviewUploader from '@/Components/Common/FilePreviewUploader.vue';

import customer from '@/routes/customer';

const props = defineProps({});

const form = useForm({
    title: '',
    value: '',
    description: '',
    expected_close_date: '',
    attachments: [],
});

const submit = () => {
    form.post(customer.deals.store.url(), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="__('Create Deal')" />

    <CustomerLayout>
        <div class="min-h-[calc(100vh-64px)] py-12 px-4 sm:px-6 lg:px-8 bg-slate-50 dark:bg-slate-950 transition-colors duration-500">
            <div class="max-w-3xl mx-auto">
                <!-- Header Section -->
                <div class="mb-10 animate-in fade-in slide-in-from-bottom-4 duration-700">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="p-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                            <Package :size="24" class="text-brand-600 dark:text-brand-400" />
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ __('Create New Deal') }}
                        </h2>
                    </div>
                </div>

                <!-- Main Form Card -->
                <form @submit.prevent="submit" class="animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-150">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] p-8 md:p-10 space-y-10">
                        
                        <!-- Deal Information Section -->
                        <div class="space-y-8">
                            <div class="pb-4 border-b border-slate-100 dark:border-slate-800">
                                <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">
                                    {{ __('Opportunity Details') }}
                                </h3>
                            </div>

                            <div class="space-y-6">
                                <!-- Title -->
                                <div>
                                    <InputLabel for="title" :value="__('Deal Title')" class="mb-1.5" />
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <ClipboardList :size="18" class="text-slate-400 group-focus-within:text-brand-500 transition-colors" />
                                        </div>
                                        <TextInput
                                            id="title"
                                            v-model="form.title"
                                            type="text"
                                            class="block w-full pl-11"
                                            :placeholder="__('E.g. Enterprise Software Licensing')"
                                            required
                                            autofocus
                                        />
                                    </div>
                                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400 italic">
                                        {{ __('Provide a name that is easy for your team to identify.') }}
                                    </p>
                                    <InputError :message="form.errors.title" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Value -->
                                    <div>
                                        <InputLabel for="value" :value="__('Estimated Value')" class="mb-1.5" />
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <span class="text-slate-400 font-bold text-sm group-focus-within:text-brand-500 transition-colors">$</span>
                                            </div>
                                            <TextInput
                                                id="value"
                                                v-model="form.value"
                                                type="number"
                                                step="0.01"
                                                class="block w-full pl-9"
                                                placeholder="0.00"
                                                required
                                            />
                                        </div>
                                        <InputError :message="form.errors.value" />
                                    </div>

                                    <!-- Date -->
                                    <div>
                                        <InputLabel for="expected_close_date" :value="__('Target Close Date')" class="mb-1.5" />
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <Calendar :size="18" class="text-slate-400 group-focus-within:text-brand-500 transition-colors" />
                                            </div>
                                            <TextInput
                                                id="expected_close_date"
                                                v-model="form.expected_close_date"
                                                type="date"
                                                class="block w-full pl-11"
                                                required
                                            />
                                        </div>
                                        <InputError :message="form.errors.expected_close_date" />
                                    </div>
                                </div>

                                <!-- Description -->
                                <div>
                                    <InputLabel for="description" :value="__('Notes & Context')" class="mb-1.5" />
                                    <TextArea
                                        id="description"
                                        v-model="form.description"
                                        class="mt-1 block w-full px-4 py-3"
                                        rows="4"
                                        :placeholder="__('Tell us more about this opportunity...')"
                                    />
                                    <InputError :message="form.errors.description" />
                                </div>

                                <!-- Attachments -->
                                <div>
                                    <InputLabel for="attachments" :value="__('Related Documents')" class="mb-2" />
                                    <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-1">
                                        <FilePreviewUploader 
                                            v-model="form.attachments"
                                            class="w-full"
                                        />
                                    </div>
                                    <InputError :message="form.errors['attachments.0']" />
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-6 border-t border-slate-100 dark:border-slate-800">
                            <Link 
                                :href="customer.deals.index.url()" 
                                class="text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors"
                            >
                                {{ __('Cancel') }}
                            </Link>
                            <PrimaryButton 
                                type="submit" 
                                :class="{ 'opacity-50 cursor-not-allowed': form.processing }" 
                                :disabled="form.processing" 
                                class="w-full sm:w-auto px-8"
                            >
                                {{ __('Create Deal') }}
                            </PrimaryButton>
                        </div>
                    </div>
                </form>

                <!-- Footer Hint -->
                <p class="mt-8 text-center text-sm text-slate-400 dark:text-slate-500 animate-in fade-in duration-1000 delay-500">
                    {{ __('Press') }} <kbd class="px-2 py-1 bg-slate-200 dark:bg-slate-800 rounded font-bold text-xs">CMD + S</kbd> {{ __('to quickly save your changes.') }}
                </p>
            </div>
        </div>
    </CustomerLayout>
</template>

<style scoped>
.animate-in {
    animation-fill-mode: both;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInFromBottom {
    from { transform: translateY(2rem); }
    to { transform: translateY(0); }
}

.fade-in {
    animation-name: fadeIn;
}

.slide-in-from-bottom-4 {
    animation-name: slideInFromBottom;
    transform: translateY(1rem);
}

.slide-in-from-bottom-8 {
    animation-name: slideInFromBottom;
    transform: translateY(2rem);
}
</style>
