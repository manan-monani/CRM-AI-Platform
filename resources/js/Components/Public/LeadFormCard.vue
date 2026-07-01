<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { PropType, computed } from 'vue';

const props = defineProps({
    form: {
        type: Object as PropType<any>,
        required: true
    },
    branding: {
        type: Object as PropType<any>,
        default: () => ({})
    },
    titleOverride: {
        type: String,
        default: null
    },
    descriptionOverride: {
        type: String,
        default: null
    }
});

// Build initial form data
const initialData: Record<string, any> = {
    utm_source: new URLSearchParams(window.location.search).get('utm_source'),
    utm_medium: new URLSearchParams(window.location.search).get('utm_medium'),
    utm_campaign: new URLSearchParams(window.location.search).get('utm_campaign'),
};

if (props.form && props.form.fields) {
    props.form.fields.forEach((field: any) => {
        initialData[field.name] = '';
        if (field.type === 'checkbox') initialData[field.name] = [];
    });
}

const form = useForm(initialData);

// Check if all required fields are filled
const isFormIncomplete = computed(() => {
    if (!props.form || !props.form.fields) return false;
    
    return props.form.fields.some((field: any) => {
        if (!field.is_required) return false;
        
        const value = form[field.name];
        if (field.type === 'checkbox') {
            return !Array.isArray(value) || value.length === 0;
        }
        return value === null || value === undefined || value.toString().trim() === '';
    });
});

const submit = () => {
    // Always post to the dedicated form handler, ensuring we hit the correct controller
    form.post(`/form/${props.form.slug}`, {
        preserveScroll: true,
        onSuccess: () => {
             form.reset();
        }
    });
};

// Branding helper - use branding prop or fallback
const primaryColor = computed(() => props.branding?.primary || 'var(--brand-primary, #179753)');
</script>

<template>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 relative">
        <div class="px-8 py-10 sm:px-12 sm:py-12">
            <!-- Header -->
            <div class="text-center mb-10">
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-3">{{ props.titleOverride || props.form?.title }}</h1>
                <p v-if="props.descriptionOverride || props.form?.description" class="text-lg text-slate-600 dark:text-slate-300 leading-relaxed">{{ props.descriptionOverride || props.form.description }}</p>
            </div>

            <!-- Success Message -->
            <div v-if="form.wasSuccessful" class="bg-green-50 border border-green-200 rounded-xl p-8 text-center mb-6 animate-fade-in">
                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-green-800 mb-2">Message Sent!</h3>
                <p class="text-green-700">Thank you for reaching out. We'll be in touch shortly.</p>
                <button @click="form.wasSuccessful = false" class="mt-6 text-sm font-bold text-green-800 hover:underline">Send another message</button>
            </div>

            <!-- Form -->
            <form v-else @submit.prevent="submit" class="flex flex-col">
                <!-- Fields Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5 mb-10">
                    <div 
                        v-for="(field, index) in props.form?.fields" 
                        :key="field.id || index" 
                        :class="[
                            'space-y-1.5 transition-all duration-300',
                            ['textarea', 'radio', 'checkbox'].includes(field.type) ? 'md:col-span-2' : 'md:col-span-1'
                        ]"
                    >
                        <label :for="field.name" class="block text-sm font-bold text-slate-700 dark:text-slate-300 ml-1">
                            {{ field.label }} <span v-if="field.is_required" class="text-rose-500">*</span>
                        </label>

                        <!-- Text / Email / Phone / Number -->
                        <input 
                            v-if="['text', 'email', 'phone', 'tel', 'number'].includes(field.type)" 
                            :id="field.name" 
                            :type="field.type" 
                            v-model="form[field.name]" 
                            :placeholder="field.placeholder"
                            :required="field.is_required"
                            class="w-full bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:border-brand-500 transition-all shadow-sm shadow-slate-100/50 dark:shadow-none"
                            :style="`--tw-ring-color: ${primaryColor}1a`"
                        >

                        <!-- Textarea -->
                        <textarea 
                            v-else-if="field.type === 'textarea'" 
                            :id="field.name" 
                            v-model="form[field.name]" 
                            :placeholder="field.placeholder"
                            :required="field.is_required"
                            rows="4"
                            class="w-full bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:border-brand-500 transition-all shadow-sm shadow-slate-100/50 dark:shadow-none"
                            :style="`--tw-ring-color: ${primaryColor}1a`"
                        ></textarea>

                        <!-- Select -->
                        <div v-else-if="field.type === 'select'" class="relative group">
                            <select 
                                :id="field.name" 
                                v-model="form[field.name]" 
                                :required="field.is_required"
                                class="w-full bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white focus:outline-none focus:ring-4 focus:border-brand-500 transition-all appearance-none cursor-pointer shadow-sm shadow-slate-100/50 dark:shadow-none font-medium"
                                :style="`--tw-ring-color: ${primaryColor}1a`"
                            >
                                <option value="" disabled selected>{{ field.placeholder || 'Select an option...' }}</option>
                                <option v-for="opt in (field.options || [])" :key="opt" :value="opt">{{ opt }}</option>
                                <option v-if="!field.options || field.options.length === 0" disabled>No options available</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400 group-hover:text-slate-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>

                        <!-- Radio -->
                        <div v-else-if="field.type === 'radio'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div v-for="opt in (field.options || [])" :key="opt" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700 cursor-pointer hover:bg-slate-100 transition-colors">
                                <input 
                                    type="radio" 
                                    :id="`${field.name}-${opt}`" 
                                    :name="field.name" 
                                    :value="opt" 
                                    v-model="form[field.name]"
                                    :required="field.is_required"
                                    class="w-5 h-5 border-slate-300 text-brand-600 focus:ring-brand-500"
                                    :style="`color: ${primaryColor}; --tw-ring-color: ${primaryColor}`"
                                >
                                <label :for="`${field.name}-${opt}`" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer w-full">{{ opt }}</label>
                            </div>
                        </div>

                        <!-- Checkbox -->
                        <div v-else-if="field.type === 'checkbox'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div v-for="opt in (field.options || [])" :key="opt" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-100 dark:border-slate-700 cursor-pointer hover:bg-slate-100 transition-colors">
                                <input 
                                    type="checkbox" 
                                    :id="`${field.name}-${opt}`" 
                                    :value="opt" 
                                    v-model="form[field.name]"
                                    class="w-5 h-5 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                    :style="`color: ${primaryColor}; --tw-ring-color: ${primaryColor}`"
                                >
                                <label :for="`${field.name}-${opt}`" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer w-full">{{ opt }}</label>
                            </div>
                        </div>

                        <p v-if="form.errors[field.name]" class="text-xs text-rose-500 mt-1 font-bold">{{ form.errors[field.name] }}</p>
                    </div>
                </div>

                <!-- Sticky Footer -->
                <div class="sticky bottom-0 -mx-8 -mb-10 sm:-mx-12 sm:-mb-12 px-8 py-6 sm:px-12 sm:py-8 bg-white/90 dark:bg-slate-800/90 backdrop-blur-md border-t border-slate-100 dark:border-slate-700 z-20">
                    <button 
                        type="submit" 
                        :disabled="form.processing || isFormIncomplete"
                        class="w-full py-4 px-6 rounded-xl text-white font-bold text-lg shadow-lg transition-all hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 disabled:grayscale disabled:cursor-not-allowed flex items-center justify-center gap-3"
                        :style="`background-color: ${primaryColor}`"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-if="!form.processing && isFormIncomplete" class="text-sm opacity-80">(Please fill all required fields)</span>
                        <span>{{ form.processing ? 'Sending...' : (props.form?.branding_settings?.submit_button_text || 'Submit Form') }}</span>
                    </button>

                    <div class="mt-4 text-center" v-if="props.branding?.business_settings?.business_name">
                        <p class="text-[10px] uppercase tracking-widest text-slate-400">
                            Powered by <strong class="text-slate-500">{{ props.branding.business_settings.business_name }}</strong>
                        </p>
                    </div>
                    <div class="mt-4 text-center" v-else>
                        <p class="text-[10px] uppercase tracking-widest text-slate-400">
                            &copy; {{ new Date().getFullYear() }} All rights reserved.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
