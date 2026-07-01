<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { PropType, ref, watch, computed } from 'vue';
import admin from '@/routes/admin';
import { 
    Settings, LayoutTemplate, Palette, Bell, Plus, Trash2, GripVertical, 
    Type, Hash, Mail, Phone, CheckSquare, List, AlignLeft, 
    ChevronUp, ChevronDown, Save, ArrowLeft, Eye, Lock, AlertCircle
} from 'lucide-vue-next';
import FileUploader from '@/Components/Common/FileUploader.vue';

const props = defineProps({
    form: Object as PropType<any>, // If editing
});

const form = useForm({
    name: props.form?.name || '',
    title: props.form?.title || '',
    description: props.form?.description || '',
    status: props.form?.status || 'active',
    is_default: props.form?.is_default || false,
    branding_settings: {
        background_color: props.form?.branding_settings?.background_color || '#F3F4F6',
        logo: props.form?.branding_settings?.logo || null,
        banner: props.form?.branding_settings?.banner || null,
        submit_button_text: props.form?.branding_settings?.submit_button_text || 'Submit Information',
    },
    notification_emails: props.form?.notification_emails || [],
    fields: (props.form?.fields || [
        { label: 'Full Name', type: 'text', is_required: true, placeholder: 'John Doe', options: null, order_index: 0, is_system: true },
        { label: 'Email Address', type: 'email', is_required: true, placeholder: 'john@example.com', options: null, order_index: 1, is_system: true },
    ]).map((f: any) => ({
        ...f,
        is_system: f.is_system || ['Full Name', 'Email Address'].includes(f.label)
    })),
});

const selectedField = ref<number | null>(null); // Index of selected field
const activeTab = ref('structure'); // structure, settings

const fieldTypes = [
    { type: 'text', label: 'Single Line Text', icon: Type },
    { type: 'textarea', label: 'Paragraph Text', icon: AlignLeft },
    { type: 'email', label: 'Email', icon: Mail },
    { type: 'phone', label: 'Phone Number', icon: Phone },
    { type: 'number', label: 'Number', icon: Hash },
    { type: 'select', label: 'Dropdown', icon: List },
    { type: 'checkbox', label: 'Checkbox', icon: CheckSquare },
    { type: 'radio', label: 'Radio Buttons', icon: CheckSquare },
];

const addField = (type: string) => {
    form.fields.push({
        label: 'New ' + type.charAt(0).toUpperCase() + type.slice(1),
        type: type,
        is_required: false,
        placeholder: '',
        options: type === 'select' || type === 'radio' || type === 'checkbox' ? ['Option 1', 'Option 2'] : null,
        order_index: form.fields.length,
    });
    selectedField.value = form.fields.length - 1;
};

const removeField = (index: number) => {
    if (form.fields[index].is_system) return;
    form.fields.splice(index, 1);
    if (selectedField.value === index) {
        selectedField.value = null;
    }
};

const moveField = (index: number, direction: 'up' | 'down') => {
    if (direction === 'up' && index > 0) {
        const temp = form.fields[index];
        form.fields[index] = form.fields[index - 1];
        form.fields[index - 1] = temp;
        if (selectedField.value === index) selectedField.value = index - 1;
        else if (selectedField.value === index - 1) selectedField.value = index;
    } else if (direction === 'down' && index < form.fields.length - 1) {
        const temp = form.fields[index];
        form.fields[index] = form.fields[index + 1];
        form.fields[index + 1] = temp;
        if (selectedField.value === index) selectedField.value = index + 1;
        else if (selectedField.value === index + 1) selectedField.value = index;
    }
};

const submit = () => {
    if (props.form) {
        form.put(admin.leadGenerationForm.update.url(props.form.id), {
            onSuccess: () => {
                // handle success
            }
        });
    } else {
        form.post(admin.leadGenerationForm.store.url(), {
            onSuccess: () => {
                // handle success
            }
        });
    }
};

const selectField = (index: number) => {
    selectedField.value = index;
    // ensure right sidebar shows field properties
};

// Computed for options string handling (comma separated for simple editing)
const selectedFieldOptions = computed({
    get: () => {
        if (selectedField.value === null) return '';
        const options = form.fields[selectedField.value].options;
        return Array.isArray(options) ? options.join('\n') : '';
    },
    set: (val) => {
        if (selectedField.value === null) return;
        // Don't filter out empty strings here, otherwise Enter key won't work in the textarea
        form.fields[selectedField.value].options = val.split('\n');
    }
});
</script>

<template>
    <Head :title="props.form ? __('Edit Lead Form') : __('Create Lead Form')" />

    <AdminLayout :initialCollapsed="true">
        <div class="h-[calc(100vh-4rem)] flex flex-col bg-slate-50 dark:bg-slate-900 overflow-hidden">
            <!-- Top Bar -->
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-20">
                <div class="flex items-center gap-4">
                    <Link :href="admin.leadGenerationForm.index.url()" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                        <ArrowLeft :size="20" />
                    </Link>
                    <div>
                        <h1 class="font-bold text-slate-900 dark:text-white text-lg leading-tight">
                            {{ form.name || 'Untitled Form' }}
                        </h1>
                        <span class="text-xs text-slate-500 block" v-if="props.form">
                            {{ props.form.status === 'active' ? 'Live' : 'Draft' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                     <a v-if="props.form" :href="`/form/${props.form.slug}`" target="_blank" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <Eye :size="16" /> {{ __('Preview') }}
                    </a>
                    <button @click="submit" :disabled="form.processing" class="flex items-center gap-2 px-6 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold shadow-lg shadow-brand-500/20 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                        <Save :size="16" /> {{ __('Save Form') }}
                    </button>
                </div>
            </header>

            <div class="flex-1 flex overflow-hidden">
                <!-- Left Sidebar: Form Structure & Elements -->
                <aside class="w-80 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col shrink-0 z-10 transition-all">
                    <!-- Sidebar content remains same but ensured proper scrolling -->
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex bg-slate-100 dark:bg-slate-900 rounded-lg p-1">
                            <button 
                                @click="activeTab = 'structure'"
                                :class="{'bg-white dark:bg-slate-700 shadow-sm text-brand-600 dark:text-brand-400': activeTab === 'structure', 'text-slate-500': activeTab !== 'structure'}"
                                class="flex-1 py-1.5 text-xs font-bold rounded-md transition-all flex items-center justify-center gap-2"
                            >
                                <LayoutTemplate :size="14" /> {{ __('Structure') }}
                            </button>
                            <button 
                                @click="activeTab = 'settings'"
                                :class="{'bg-white dark:bg-slate-700 shadow-sm text-brand-600 dark:text-brand-400': activeTab === 'settings', 'text-slate-500': activeTab !== 'settings'}"
                                class="flex-1 py-1.5 text-xs font-bold rounded-md transition-all flex items-center justify-center gap-2"
                            >
                                <Settings :size="14" /> {{ __('Settings') }}
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-6">
                        
                        <div v-if="activeTab === 'structure'">
                            <!-- Add Fields Grid -->
                            <div class="grid grid-cols-2 gap-2 mb-6">
                                <button v-for="field in fieldTypes" :key="field.type" @click="addField(field.type)" class="flex flex-col items-center justify-center p-3 rounded-xl bg-slate-50 dark:bg-slate-900 hover:bg-white hover:shadow-md border border-slate-200 dark:border-slate-700 transition-all hover:border-brand-300 dark:hover:border-brand-700 group">
                                    <component :is="field.icon" :size="20" class="text-slate-400 group-hover:text-brand-500 mb-2 transition-colors" />
                                    <span class="text-[10px] font-medium text-slate-600 dark:text-slate-400">{{ field.label }}</span>
                                </button>
                            </div>

                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Form Fields') }}</h3>
                                <span class="text-xs text-slate-400">{{ form.fields.length }} fields</span>
                            </div>

                            <!-- Fields List -->
                            <div class="space-y-2">
                                <div 
                                    v-for="(field, index) in form.fields" 
                                    :key="index"
                                    @click="selectField(Number(index))"
                                    :class="{'border-brand-500 ring-1 ring-brand-500/20 bg-brand-50/50 dark:bg-brand-900/10': selectedField === index, 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-brand-300': selectedField !== index}"
                                    class="p-3 rounded-xl border transition-all cursor-pointer group relative"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="cursor-move text-slate-300 hover:text-slate-500">
                                            <GripVertical :size="16" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200 truncate">{{ field.label }}</p>
                                            <p class="text-[10px] text-slate-400 uppercase">{{ field.type }}</p>
                                        </div>
                                        
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click.stop="moveField(Number(index), 'up')" :disabled="Number(index) === 0" class="p-1 hover:bg-slate-100 rounded disabled:opacity-30">
                                                <ChevronUp :size="14" />
                                            </button>
                                            <button @click.stop="moveField(Number(index), 'down')" :disabled="Number(index) === form.fields.length -1" class="p-1 hover:bg-slate-100 rounded disabled:opacity-30">
                                                <ChevronDown :size="14" />
                                            </button>
                                            <button @click.stop="removeField(Number(index))" :disabled="field.is_system" :class="{'opacity-30 cursor-not-allowed': field.is_system, 'hover:bg-rose-100 text-rose-500': !field.is_system}" class="p-1 rounded transition-colors">
                                                <Trash2 :size="14" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                 <div v-if="form.fields.length === 0" class="text-center py-8 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                                    <p class="text-xs text-slate-400">No fields added yet.</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'settings'" class="space-y-4">
                            <!-- Global Error Alert -->
                            <div v-if="Object.keys(form.errors).length > 0 && !form.errors.name" class="mb-2 p-3 bg-rose-50 dark:bg-rose-900/20 rounded-xl border border-rose-100 dark:border-rose-900/30 flex items-start gap-3">
                                <AlertCircle :size="16" class="text-rose-500 mt-0.5 shrink-0" />
                                <div>
                                    <p class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ __('Please fix the following validation errors:') }}</p>
                                    <ul class="mt-1 list-disc list-inside text-[10px] text-rose-500/80 space-y-0.5">
                                        <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
                                    </ul>
                                </div>
                            </div>
                            <!-- Branding -->
                            <div class="space-y-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                    <Palette :size="14" /> {{ __('Branding') }}
                                </h3>
                                <div>
                                    <p class="text-[10px] text-slate-500 italic bg-slate-50 dark:bg-slate-900/50 p-2 rounded-lg border border-slate-100 dark:border-slate-700">
                                        {{ __('This form uses the global system brand color.') }}
                                    </p>
                                </div>
                            </div>

                             <div class="h-px bg-slate-100 dark:bg-slate-700 my-4"></div>

                            <!-- General -->
                             <div class="space-y-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                    <Settings :size="14" /> {{ __('General') }}
                                </h3>
                                  <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-1.5">{{ __('Form Name') }}</label>
                                    <input v-model="form.name" type="text" placeholder="e.g. Q1 Marketing Campaign" class="w-full text-sm bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 px-4 py-2.5 transition-all outline-none shadow-sm shadow-slate-100/50 dark:shadow-none">
                                 </div>
                                 <div class="pt-2">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-1.5">{{ __('Submit Button Text') }}</label>
                                    <input v-model="form.branding_settings.submit_button_text" type="text" placeholder="Submit" class="w-full text-sm bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 px-4 py-2.5 transition-all outline-none shadow-sm shadow-slate-100/50 dark:shadow-none">
                                 </div>
                                 <div class="h-px bg-slate-100 dark:bg-slate-700 my-4"></div>
                                 <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-1.5">{{ __('Form Status') }}</label>
                                    <div class="relative group">
                                        <select v-model="form.status" :disabled="form.is_default" class="w-full text-sm bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 px-4 pr-10 py-2.5 transition-all outline-none appearance-none disabled:opacity-50 disabled:bg-slate-50 dark:disabled:bg-slate-800 cursor-pointer shadow-sm shadow-slate-100/50 dark:shadow-none font-medium">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 group-hover:text-slate-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                    <p v-if="form.is_default" class="text-[10px] text-amber-600 dark:text-amber-500 mt-1 flex items-center gap-1">
                                        <Lock :size="10" /> {{ __('The default form must remain active.') }}
                                    </p>
                                 </div>
                                 <div>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="form.is_default" :disabled="props.form?.is_default" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 disabled:opacity-50">
                                        <span class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ __('Set as Default Form') }}</span>
                                    </label>
                                    <p class="text-[10px] text-slate-400 mt-1 ml-5">{{ __('This form will be displayed on the landing page.') }}</p>
                                 </div>
                            </div>
                        </div>

                    </div>
                </aside>

                <!-- Center: Canvas / Preview -->
                <main class="flex-1 bg-slate-100 dark:bg-slate-900/50 flex flex-col relative overflow-hidden">
                    <div class="absolute inset-0 pattern-grid opacity-5 pointer-events-none"></div>
                    
                    <div class="flex-1 overflow-y-auto p-8 flex justify-center">
                        <div class="w-full max-w-2xl animate-fade-in-up">
                            <!-- Form Preview Card -->
                            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                                
                                <div class="p-8">
                                    <!-- Logo -->


                                    <div class="text-center mb-8">
                                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">{{ form.title || 'Your Headline Here' }}</h1>
                                        <p class="text-slate-500 dark:text-slate-400">{{ form.description || 'Add a descriptive text to encourage users to sign up.' }}</p>
                                    </div>

                                    <form @submit.prevent class="space-y-5">
                                        <div 
                                            v-for="(field, index) in form.fields" 
                                            :key="Number(index)"
                                            @click="selectField(Number(index))"
                                            class="group relative p-4 rounded-xl transition-all border border-transparent hover:border-brand-300 hover:bg-brand-50/30 cursor-pointer"
                                            :class="{'ring-2 ring-brand-500 ring-offset-2 ring-offset-white dark:ring-offset-slate-800': selectedField === index}"
                                        >
                                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1.5 is-required">
                                                {{ field.label }} 
                                                <span v-if="field.is_required" class="text-rose-500">*</span>
                                            </label>

                                            <!-- Enhanced Input Styles -->
                                            <input v-if="['text', 'email', 'phone', 'tel', 'number'].includes(field.type)" 
                                                :type="field.type" 
                                                :placeholder="field.placeholder" 
                                                disabled 
                                                class="w-full bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm cursor-not-allowed opacity-75 shadow-sm shadow-slate-100/50 dark:shadow-none"
                                            >
                                            
                                            <textarea v-else-if="field.type === 'textarea'" 
                                                :placeholder="field.placeholder" 
                                                disabled 
                                                class="w-full bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm h-24 cursor-not-allowed opacity-75 shadow-sm shadow-slate-100/50 dark:shadow-none"
                                            ></textarea>
                                            
                                            <div v-else-if="field.type === 'select'" class="relative">
                                                <select 
                                                    disabled 
                                                    class="w-full bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm cursor-not-allowed opacity-75 shadow-sm shadow-slate-100/50 dark:shadow-none appearance-none font-medium text-slate-500"
                                                >
                                                    <option>{{ field.placeholder || 'Select an option...' }}</option>
                                                    <option v-for="opt in (field.options || [])" :key="opt">{{ opt }}</option>
                                                    <option v-if="!field.options || field.options.length === 0">Option 1</option>
                                                    <option v-if="!field.options || field.options.length === 0">Option 2</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-300">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                </div>
                                            </div>

                                            <div v-else-if="['checkbox', 'radio'].includes(field.type)" class="space-y-3 pt-1">
                                                <div v-for="(opt, oIndex) in (field.options && field.options.length > 0 ? field.options : ['Option 1', 'Option 2'])" :key="oIndex" class="flex items-center gap-3 opacity-75">
                                                    <div 
                                                        :class="field.type === 'radio' ? 'rounded-full' : 'rounded'"
                                                        class="w-5 h-5 border border-slate-300 dark:border-slate-600 bg-slate-50 flex items-center justify-center transition-colors"
                                                    >
                                                        <div v-if="oIndex === 0" :class="field.type === 'radio' ? 'w-2.5 h-2.5 rounded-full' : 'w-3 h-3 rounded-sm'" class="bg-brand-500"></div>
                                                    </div>
                                                    <span class="text-sm font-medium text-slate-600 dark:text-slate-400">{{ opt }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pt-4">
                                            <button disabled type="button" class="w-full py-3.5 px-4 text-white rounded-xl font-bold opacity-50 cursor-not-allowed shadow-lg transition-transform active:scale-95" style="background-color: var(--brand-primary, #3B82F6)">
                                                {{ form.branding_settings.submit_button_text || 'Submit' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>

                <!-- Right Sidebar: Properties -->
                <aside class="w-80 bg-white dark:bg-slate-800 border-l border-slate-200 dark:border-slate-700 flex flex-col shrink-0 z-10 transition-all">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-700">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white text-center">
                            {{ selectedField !== null ? 'Field Properties' : 'Form Properties' }}
                        </h3>
                    </div>

                    <div class="flex-1 overflow-y-auto custom-scrollbar p-5 space-y-6">
                        <div v-if="selectedField !== null">
                            <!-- Field Properties -->
                             <div class="space-y-5">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-1.5">{{ __('Field Label') }}</label>
                                    <input v-model="form.fields[selectedField].label" type="text" class="w-full text-sm bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 px-4 py-2.5 transition-all outline-none shadow-sm shadow-slate-100/50 dark:shadow-none">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-1.5">{{ __('Placeholder') }}</label>
                                    <input v-model="form.fields[selectedField].placeholder" type="text" class="w-full text-sm bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 px-4 py-2.5 transition-all outline-none shadow-sm shadow-slate-100/50 dark:shadow-none">
                                </div>

                                <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-900 rounded-xl border-[1.5px] border-slate-100 dark:border-slate-700 mt-2 shadow-sm shadow-slate-100/50 dark:shadow-none">
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wide ps-1">{{ __('Required Field') }}</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" v-model="form.fields[selectedField].is_required" :disabled="form.fields[selectedField].is_system" class="sr-only peer">
                                        <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-800 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-500" :class="{'opacity-50 cursor-not-allowed': form.fields[selectedField].is_system}"></div>
                                    </label>
                                </div>

                                <div v-if="['select', 'radio', 'checkbox'].includes(form.fields[selectedField].type)" class="pt-2">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-1.5">{{ __('Options (One per line)') }}</label>
                                    <textarea v-model="selectedFieldOptions" rows="5" class="w-full text-sm bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 px-4 py-2.5 transition-all outline-none custom-scrollbar shadow-sm shadow-slate-100/50 dark:shadow-none"></textarea>
                                </div>
                            </div>

                             <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-700">
                                <button v-if="!form.fields[selectedField].is_system" @click="removeField(selectedField)" class="w-full flex items-center justify-center gap-2 py-2 text-rose-500 bg-rose-50 hover:bg-rose-100 rounded-lg text-sm font-bold transition-colors">
                                    <Trash2 :size="16" /> Delete Field
                                </button>
                                <div v-else class="text-center p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-100 dark:border-slate-700">
                                    <p class="text-xs text-slate-500 flex items-center justify-center gap-1">
                                        <Lock :size="12" /> System field cannot be deleted
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-12">
                            <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                                <LayoutTemplate :size="24" />
                            </div>
                            <p class="text-sm text-slate-500">Select a field to edit its properties, or configure form settings in the left panel.</p>
                            
                            <div class="mt-8 space-y-5 text-start">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-1.5">{{ __('Form Headline') }} <span class="text-rose-500">*</span></label>
                                    <input v-model="form.title" type="text" placeholder="e.g. Get a Free Usage Quote" class="w-full text-sm bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 px-4 py-2.5 transition-all outline-none shadow-sm shadow-slate-100/50 dark:shadow-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest ps-1 mb-1.5">{{ __('Description') }}</label>
                                    <textarea v-model="form.description" rows="3" placeholder="Explain the value proposition" class="w-full text-sm bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 px-4 py-2.5 transition-all outline-none custom-scrollbar shadow-sm shadow-slate-100/50 dark:shadow-none"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.pattern-grid {
    background-image: linear-gradient(rgba(0,0,0,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(0,0,0,0.1) 1px, transparent 1px);
    background-size: 20px 20px;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
</style>
