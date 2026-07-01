<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PropType, ref } from 'vue';
import { ChevronRight, Eye, EyeOff } from 'lucide-vue-next';
import admin from '@/routes/admin';

const props = defineProps({
    customer: Object as PropType<any>,
});

const form = useForm({
    _method: 'PUT',
    name: props.customer.name,
    email: props.customer.email,
    password: '',
    password_confirmation: '',
    phone: props.customer.customer_profile?.phone || '',
    company: props.customer.customer_profile?.company || '',
    address: props.customer.customer_profile?.address || '',
    city: props.customer.customer_profile?.city || '',
    profile_image: null as File | null,
});

const showPasswordFields = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const imagePreview = ref<string | null>(null);

const handleImageChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.profile_image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    // We use POST with _method=PUT to support file uploads
    form.post(admin.customers.update.url(props.customer.id), {
        forceFormData: true,
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
            showPasswordFields.value = false;
        },
    });
};
</script>

<template>
    <Head :title="__('Edit Customer')" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in max-w-2xl mx-auto pb-12">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 border-b border-slate-200 dark:border-slate-800 pb-6">
                <div>
                     <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                        <Link :href="admin.customers.index.url()" class="hover:text-brand-500">{{ __('Customers') }}</Link>
                        <ChevronRight :size="8" class="opacity-40 rtl:rotate-180" />
                        <span class="text-brand-500">{{ __('Edit Customer') }}</span>
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Edit Customer') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Update customer details and profile.') }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Profile Image -->
                    <div class="flex flex-col items-center gap-4 py-4">
                        <div class="relative group">
                            <img :src="imagePreview || (props.customer.profile_image ? '/storage/' + props.customer.profile_image : props.customer.avatar || `https://ui-avatars.com/api/?name=${props.customer.name}&background=random`)" class="w-24 h-24 rounded-xl object-cover border-4 border-slate-50 dark:border-slate-900 shadow-xl group-hover:opacity-75 transition-opacity">
                            <label class="absolute inset-0 flex items-center justify-center cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="bg-black/50 text-white rounded-full p-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </span>
                                <input type="file" @change="handleImageChange" class="hidden" accept="image/*">
                            </label>
                        </div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ __('Profile Picture') }}</p>
                        <p v-if="form.errors.profile_image" class="text-red-500 text-xs mt-1">{{ form.errors.profile_image }}</p>
                    </div>

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
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Address') }}</label>
                        <input v-model="form.address" type="text" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('City') }}</label>
                            <input v-model="form.city" type="text" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                        </div>
                    </div>

                    <!-- Password Update Section -->
                    <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                        <button 
                            type="button" 
                            @click="showPasswordFields = !showPasswordFields"
                            class="text-sm font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors flex items-center gap-2"
                        >
                            <span v-if="!showPasswordFields">{{ __('Change Password') }}</span>
                            <span v-else>{{ __('Cancel Password Change') }}</span>
                        </button>

                        <div v-if="showPasswordFields" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-6">
                             <div>
                                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('New Password') }}</label>
                                <div class="relative">
                                    <input v-model="form.password" :type="showNewPassword ? 'text' : 'password'" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 pr-10">
                                    <button type="button" @click="showNewPassword = !showNewPassword" class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                        <Eye v-if="!showNewPassword" class="w-5 h-5" />
                                        <EyeOff v-else class="w-5 h-5" />
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 mb-2 block">{{ __('Confirm New Password') }}</label>
                                <div class="relative">
                                    <input v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" class="w-full px-5 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl font-medium outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 pr-10">
                                    <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                        <Eye v-if="!showConfirmPassword" class="w-5 h-5" />
                                        <EyeOff v-else class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Link :href="admin.customers.index.url()" class="px-6 py-3 rounded-xl border border-slate-200 dark:border-slate-800 font-bold text-sm text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all text-center">
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
