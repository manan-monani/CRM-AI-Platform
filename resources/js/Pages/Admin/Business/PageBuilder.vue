<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import admin from '@/routes/admin';
import business from '@/routes/admin/business';
import { 
    LayoutTemplate, Save, Camera, Type, Image as ImageIcon, 
    CheckCircle, List, ArrowRight, ArrowLeftRight
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { wTrans as __ } from '@/Core/i18n';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({}),
    },
    default_form: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    // Hero Section
    landing_title: props.settings.landing_title || 'Transform Your Business',
    landing_subtitle: props.settings.landing_subtitle || 'Join thousands of businesses already growing with our platform. Get started in minutes.',
    landing_hero_image: props.settings.landing_hero_image || '',

    // Feature 1
    landing_feature_1_title: props.settings.landing_feature_1_title || 'Fast Setup',
    landing_feature_1_description: props.settings.landing_feature_1_description || 'Get started in under 5 minutes',
    landing_feature_1_icon: props.settings.landing_feature_1_icon || '',

    // Feature 2
    landing_feature_2_title: props.settings.landing_feature_2_title || '24/7 Support',
    landing_feature_2_description: props.settings.landing_feature_2_description || 'Our team is always here to help',
    landing_feature_2_icon: props.settings.landing_feature_2_icon || '',

    // Feature 3
    landing_feature_3_title: props.settings.landing_feature_3_title || 'Secure & Reliable',
    landing_feature_3_description: props.settings.landing_feature_3_description || 'Enterprise-grade security',
    landing_feature_3_icon: props.settings.landing_feature_3_icon || '',

    // Feature 4
    landing_feature_4_title: props.settings.landing_feature_4_title || 'No Credit Card Required',
    landing_feature_4_description: props.settings.landing_feature_4_description || 'Start your free trial today',
    landing_feature_4_icon: props.settings.landing_feature_4_icon || '',
});

// Use brand primary color from branding settings
const brandColor = props.settings.primary || '#179753';

const previews = ref<Record<string, string>>({
    landing_hero_image: props.settings.landing_hero_image || '',
    landing_feature_1_icon: props.settings.landing_feature_1_icon || '',
    landing_feature_2_icon: props.settings.landing_feature_2_icon || '',
    landing_feature_3_icon: props.settings.landing_feature_3_icon || '',
    landing_feature_4_icon: props.settings.landing_feature_4_icon || '',
});

const handleFileChange = (event: Event, key: keyof typeof form) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        const url = URL.createObjectURL(file);
        
        form[key] = file as any;
        previews.value[key] = url;
    }
};

const submit = () => {
    // business.leadGenerationPage corresponds to the GET request route, 
    // but the URL is the same for the POST request.
    form.post(business.leadGenerationPage.url(), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handling if needed
        },
    });
};

const triggerFileInput = (id: string) => {
    document.getElementById(id)?.click();
};

const getImageUrl = (url: string) => {
    if (!url) return null;
    return url.startsWith('http') || url.startsWith('blob:') ? url : `/storage/${url}`;
};
</script>

<template>
    <Head :title="__('Lead Generation Page Builder')" />

    <AdminLayout>
        <div class="h-full">
            <div class="space-y-6 max-w-6xl mx-auto animate-fade-in">
                
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ __('Lead Gen Page Builder') }}</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('Customize the content and appearance of your lead generation page.') }}</p>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="business.branding.url()" class="px-4 py-2 rounded-xl font-bold text-sm bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center gap-2">
                             <ArrowLeftRight :size="18" /> {{ __('Branding Settings') }}
                        </Link>
                        <button @click="submit" :disabled="form.processing" class="text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg transition-all flex items-center gap-2 hover:opacity-90 disabled:opacity-70" :style="{ backgroundColor: brandColor, boxShadow: `0 10px 15px -3px ${brandColor}4D` }">
                            <Save v-if="!form.processing" :size="18" />
                            <span v-if="form.processing">{{ __('Saving...') }}</span>
                            <span v-else>{{ __('Save Changes') }}</span>
                        </button>
                    </div>
                </div>

                <!-- 1. Hero Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Text Controls -->
                    <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6 transition-all duration-300">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-base font-black text-slate-800 dark:text-white text-start flex items-center">
                                <LayoutTemplate class="me-4" :size="20" :style="{ color: brandColor }" />
                                <span>{{ __('Hero Section Content') }}</span>
                            </h4>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-2 block text-start">{{ __('Main Headline') }}</label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 flex-shrink-0"><Type :size="16" /></div>
                                        <input v-model="form.landing_title" type="text" class="w-full px-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all font-serif text-lg" :style="{ '--tw-ring-color': brandColor }">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-2 block text-start">{{ __('Subtitle / Description') }}</label>
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 flex-shrink-0 mt-1"><List :size="16" /></div>
                                        <textarea v-model="form.landing_subtitle" rows="3" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-medium outline-none text-slate-900 dark:text-white focus:ring-2 focus:opacity-100 transition-all resize-none" :style="{ '--tw-ring-color': brandColor }"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Hero Image Preview -->
                            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 p-6 flex flex-col items-center justify-center text-center relative group overflow-hidden h-full min-h-[200px]">
                                <div class="absolute inset-0 bg-slate-100 dark:bg-slate-800" v-if="!previews.landing_hero_image"></div>
                                <img v-else :src="getImageUrl(previews.landing_hero_image)" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-30 transition-all" />
                                
                                <div class="relative z-10 space-y-3">
                                    <div class="w-16 h-16 rounded-full bg-white dark:bg-slate-900 shadow-lg flex items-center justify-center mx-auto text-slate-400">
                                        <ImageIcon :size="32" />
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Hero Background') }}</h5>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('1920x1080px recommended') }}</p>
                                    </div>
                                    <button @click="triggerFileInput('hero_image_input')" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold shadow-sm hover:shadow-md transition-all">
                                        {{ __('Choose File') }}
                                    </button>
                                    <input type="file" id="hero_image_input" class="hidden" accept="image/*" @change="(e) => handleFileChange(e, 'landing_hero_image')">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Lead Form Preview -->
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6 transition-all duration-300">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-base font-black text-slate-800 dark:text-white text-start flex items-center">
                                <ArrowRight class="me-4" :size="20" :style="{ color: brandColor }" />
                                <span>{{ __('Lead Form Preview') }}</span>
                            </h4>
                                <Link v-if="default_form" :href="admin.leadGenerationForm.edit.url(default_form.id)" class="text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-brand-500 transition-colors">
                                    {{ __('Customize Form') }}
                                </Link>
                            </div>

                        <div v-if="default_form" class="space-y-6">
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700">
                                <h5 class="text-sm font-bold text-slate-900 dark:text-white mb-1">{{ default_form.title || default_form.name }}</h5>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ default_form.description || __('No description provided.') }}</p>
                            </div>

                            <!-- Mock Form Skeleton -->
                            <div class="space-y-4 px-2">
                                <div v-for="field in default_form.fields.slice(0, 3)" :key="field.id" class="space-y-1.5">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ field.label }} <span v-if="field.is_required" class="text-rose-500">*</span></label>
                                    <div class="h-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 opacity-50 flex items-center px-4 font-medium text-xs text-slate-400 italic">
                                        {{ field.placeholder || __('Enter value...') }}
                                    </div>
                                </div>
                                
                                <button disabled class="w-full py-3 rounded-xl text-white font-bold text-sm shadow-xl mt-2 opacity-90 relative overflow-hidden flex items-center justify-center gap-2" :style="{ backgroundColor: brandColor }">
                                    <span>{{ default_form.branding_settings?.submit_button_text || __('Submit Response') }}</span>
                                    <ArrowRight :size="16" />
                                </button>
                                
                                <p v-if="default_form.fields.length > 3" class="text-[10px] text-center text-slate-400 italic">
                                    + {{ default_form.fields.length - 3 }} {{ __('more fields...') }}
                                </p>
                            </div>
                        </div>

                        <div v-else class="h-full min-h-[300px] flex flex-col items-center justify-center text-center p-8 space-y-4">
                            <div class="w-16 h-16 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300">
                                <LayoutTemplate :size="32" />
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('No Default Form Set') }}</h5>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-[200px] mx-auto">{{ __('Please set a default lead form in the Form Builder to show it here.') }}</p>
                            </div>
                            <Link :href="admin.leadGenerationForm.index.url()" class="text-[10px] font-bold uppercase tracking-widest text-brand-500 border border-brand-200 dark:border-brand-900/50 px-4 py-2 rounded-xl hover:bg-brand-50 dark:hover:bg-brand-900/10 transition-all">
                                {{ __('Go to Form Builder') }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- 2. Features Section -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6 transition-all duration-300">
                    <h4 class="text-base font-black text-slate-800 dark:text-white mb-6 text-start flex items-center">
                        <CheckCircle class="me-4" :size="20" :style="{ color: brandColor }" />
                        <span>{{ __('Key Features / Checkpoints') }}</span>
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-for="i in 4" :key="i" class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700/50 space-y-4">
                            <h5 class="text-xs font-black uppercase tracking-widest text-slate-400">Feature {{ i }}</h5>
                            
                            <div class="space-y-3">
                                <input v-model="form[`landing_feature_${i}_title`]" type="text" :placeholder="__('Feature Title')" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-sm outline-none focus:border-brand-500 transition-all text-slate-900 dark:text-white" :style="{ '--tw-ring-color': brandColor }">
                                <input v-model="form[`landing_feature_${i}_description`]" type="text" :placeholder="__('Short description')" class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs outline-none focus:border-brand-500 transition-all text-slate-600 dark:text-slate-300" :style="{ '--tw-ring-color': brandColor }">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
