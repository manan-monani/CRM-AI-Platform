<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import business from '@/routes/admin/business';
import { 
    LayoutTemplate, Save, Plus, Trash2, GripVertical,
    Type, Image as ImageIcon, CheckCircle, BarChart3, Zap, Clock, Layout as LayoutIcon
} from 'lucide-vue-next';
import { wTrans as __ } from '@/Core/i18n';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({}),
    },
});

// Available Section Types
const availableSections = [
    { type: 'hero', name: 'Hero (Nexus Style)', icon: LayoutTemplate, description: 'Main banner with gradient title and CTAs.' },
    { type: 'features', name: 'Features (Bento)', icon: CheckCircle, description: 'Bento grid of features with Kanban mockup.' },
    { type: 'stats', name: 'Stats (Modern)', icon: BarChart3, description: 'High-contrast stats bar with radial separators.' },
    { type: 'cta', name: 'CTA (Impact)', icon: Zap, description: 'Impactful CTA with dotted pattern overlay.' },
];

const featureIcons = [
    'LayoutDashboard', 'Users', 'CheckCircle2', 'BarChart3', 'ShieldCheck', 'Zap', 'Clock', 'Layout'
];

// Parse existing sections or default to empty array
const initialSections = props.settings.home_page_sections 
    ? JSON.parse(props.settings.home_page_sections) 
    : [];

const sections = ref(initialSections);

// Drag and Drop State
const draggedItemIndex = ref<number | null>(null);

const onDragStart = (index: number) => {
    draggedItemIndex.value = index;
};

const onDragOver = (event: DragEvent) => {
    event.preventDefault();
};

const onDrop = (index: number) => {
    if (draggedItemIndex.value === null) return;
    
    const itemToMove = sections.value[draggedItemIndex.value];
    sections.value.splice(draggedItemIndex.value, 1);
    sections.value.splice(index, 0, itemToMove);
    
    draggedItemIndex.value = null;
};

// Actions
const addSection = (type: string) => {
    const newSection = {
        id: Date.now().toString(),
        type,
        data: getDefaultDataForType(type),
        isOpen: true,
    };
    sections.value.push(newSection);
};

const addFeatureItem = (sectionIndex: number) => {
    sections.value[sectionIndex].data.items.push({
        title: 'New Feature',
        description: 'New feature description',
        icon: 'Zap'
    });
};

const removeFeatureItem = (sectionIndex: number, itemIndex: number) => {
    sections.value[sectionIndex].data.items.splice(itemIndex, 1);
};

const sectionToDelete = ref<number | null>(null);

const removeSection = (index: number) => {
    sections.value.splice(index, 1);
    sectionToDelete.value = null;
};

const getDefaultDataForType = (type: string) => {
    switch (type) {
        case 'hero':
            return {
                title_main: 'Manage Your Sales',
                title_gradient: 'With Zero Gravity.',
                subtitle: 'Nexus CRM is the first predictive sales platform that automates your follow-ups.',
                cta_text: 'Start Your Free Trial',
                cta_link: '/register',
                image: '',
            };
        case 'features':
            return {
                title: 'Everything you need to scale.',
                description: 'Stop juggling tabs and spreadsheet hacks.',
                items: [
                    { title: 'Smart Lead Capture', description: 'Automatically capture leads.', icon: 'Zap' },
                    { title: 'Interactive Deal Pipeline', description: 'Visualize your sales process.', icon: 'Layout' },
                    { title: 'Instant Reminders', description: 'Never miss a follow-up.', icon: 'Clock' },
                ]
            };
        case 'stats':
            return {
                title: 'Growth by the numbers',
                items: [
                    { label: 'Active Users', value: '25,000', suffix: '+' },
                    { label: 'Deals Closed', value: '$1.2B', suffix: '+' },
                    { label: 'Global Uptime', value: '99.99', suffix: '%' },
                ]
            };
        case 'cta':
            return {
                title: 'Ready to join?',
                subtitle: 'Start your free trial today.',
                button_text: 'Sign Up Now',
                button_link: '/register',
                secondary_button_text: 'Contact Sales',
                show_trust_badges: true,
                trust_text: 'Trusted by top-tier SDRs'
            };
        default:
            return {};
    }
};

const form = useForm({
    home_page_sections: '',
});

const submit = () => {
    form.home_page_sections = JSON.stringify(sections.value);
    form.post(business.homePageBuilder.update.url(), {
        preserveScroll: true,
    });
};

const toggleSection = (index: number) => {
    sections.value[index].isOpen = !sections.value[index].isOpen;
};
</script>

<template>
    <Head :title="__('Home Page Builder')" />

    <AdminLayout>
        <div class="py-6">
            <div class="space-y-6 max-w-7xl mx-auto animate-fade-in">
                
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 dark:text-white">{{ __('Home Page Builder') }}</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('Drag and drop sections to build your home page.') }}</p>
                    </div>
                    <button @click="submit" :disabled="form.processing" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg transition-all flex items-center gap-2 disabled:opacity-70">
                        <Save v-if="!form.processing" :size="18" />
                        <span v-if="form.processing">{{ __('Saving...') }}</span>
                        <span v-else>{{ __('Save Changes') }}</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Available Sections -->
                    <div class="lg:col-span-1 space-y-4 lg:sticky lg:top-20 self-start max-h-[calc(100vh-100px)] overflow-y-auto pr-2 custom-scrollbar">
                        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest">{{ __('Available Sections') }}</h3>
                        <div class="grid gap-4">
                            <button 
                                v-for="section in availableSections" 
                                :key="section.type"
                                @click="addSection(section.type)"
                                class="flex items-start gap-4 p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary-500 dark:hover:border-primary-500 hover:shadow-md transition-all text-left group"
                            >
                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-500 group-hover:text-primary-600 transition-colors">
                                    <component :is="section.icon" :size="20" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-white">{{ section.name }}</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ section.description }}</p>
                                </div>
                                <Plus :size="16" class="ml-auto text-slate-400 group-hover:text-primary-600" />
                            </button>
                        </div>
                    </div>

                    <!-- Builder Canvas -->
                    <div class="lg:col-span-2 space-y-4">
                        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest">{{ __('Page Structure') }}</h3>
                        <div v-if="sections.length === 0" class="p-12 text-center bg-slate-50 dark:bg-slate-800/50 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                             <LayoutTemplate class="mx-auto text-slate-300 mb-4" :size="48" />
                             <h4 class="text-lg font-bold text-slate-600 dark:text-slate-300">{{ __('Your page is empty') }}</h4>
                        </div>

                        <div class="space-y-4 min-h-[200px]">
                            <div 
                                v-for="(section, index) in sections" 
                                :key="section.id"
                                draggable="true"
                                @dragstart="onDragStart(index)"
                                @dragover="onDragOver"
                                @drop="onDrop(index)"
                                class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all"
                                :class="{ 'opacity-50 ring-2 ring-primary-500': draggedItemIndex === index }"
                            >
                                <div class="flex items-center gap-4 p-4 cursor-move bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700/50">
                                    <GripVertical class="text-slate-400" :size="20" />
                                    <span class="font-bold text-sm uppercase tracking-wide text-slate-500">{{ section.type }}</span>
                                    <div class="flex-1"></div>
                                    <button @click="toggleSection(index)" class="text-xs font-bold text-primary-600 hover:text-primary-700 px-3 py-1 rounded bg-primary-50 dark:bg-primary-900/20">
                                        {{ section.isOpen ? 'Collapse' : 'Edit' }}
                                    </button>
                                    <button @click="removeSection(index)" class="text-rose-500 hover:text-rose-600 p-2 rounded-xl">
                                        <Trash2 :size="18" />
                                    </button>
                                </div>

                                <div v-if="section.isOpen" class="p-6 space-y-4">
                                    <!-- Hero Editor -->
                                    <div v-if="section.type === 'hero'" class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Main Headline</label>
                                            <input v-model="section.data.title_main" type="text" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-bold">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Gradient Text</label>
                                            <input v-model="section.data.title_gradient" type="text" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-bold text-primary-600">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Subtitle</label>
                                            <textarea v-model="section.data.subtitle" rows="3" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm"></textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">CTA Text</label>
                                                <input v-model="section.data.cta_text" type="text" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">CTA Link</label>
                                                <input v-model="section.data.cta_link" type="text" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Features Editor -->
                                    <div v-if="section.type === 'features'" class="space-y-6">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Headline</label>
                                            <input v-model="section.data.title" type="text" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Description</label>
                                            <textarea v-model="section.data.description" rows="2" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm"></textarea>
                                        </div>
                                        <div class="space-y-4">
                                            <div v-for="(item, i) in section.data.items" :key="i" class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                                                <div class="flex justify-between items-center mb-3">
                                                    <span class="text-xs font-black text-slate-400">Card {{ i + 1 }} {{ i === 1 && section.data.items.length === 3 ? '(Wide Bento)' : '' }}</span>
                                                    <button @click="removeFeatureItem(index, i)" class="text-rose-500 hover:text-rose-600"><Trash2 :size="14" /></button>
                                                </div>
                                                <div class="grid grid-cols-2 gap-3 mb-3">
                                                    <input v-model="item.title" type="text" placeholder="Title" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm">
                                                    <select v-model="item.icon" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm">
                                                        <option v-for="icon in featureIcons" :key="icon" :value="icon">{{ icon }}</option>
                                                    </select>
                                                </div>
                                                <input v-model="item.description" type="text" placeholder="Description" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm">
                                            </div>
                                            <button @click="addFeatureItem(index)" class="w-full py-2 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl text-slate-400 hover:text-primary-600 flex items-center justify-center gap-2 font-bold text-sm">
                                                <Plus :size="16" /> Add Feature Card
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Stats Editor -->
                                    <div v-if="section.type === 'stats'" class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Section Header (Optional)</label>
                                            <input v-model="section.data.title" type="text" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm">
                                        </div>
                                        <div class="space-y-4">
                                            <div v-for="(item, i) in section.data.items" :key="i" class="flex gap-4">
                                                <input v-model="item.label" type="text" placeholder="Label" class="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm">
                                                <input v-model="item.value" type="text" placeholder="Value" class="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-bold">
                                                <input v-model="item.suffix" type="text" placeholder="Suffix" class="w-16 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm text-primary-600">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- CTA Editor -->
                                    <div v-if="section.type === 'cta'" class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Headline</label>
                                            <input v-model="section.data.title" type="text" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Subtitle</label>
                                            <input v-model="section.data.subtitle" type="text" class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm">
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Primary Button</label>
                                                <input v-model="section.data.button_text" type="text" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Link</label>
                                                <input v-model="section.data.button_link" type="text" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Secondary Button</label>
                                                <input v-model="section.data.secondary_button_text" type="text" placeholder="'none' to hide" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Trust Badges Text</label>
                                                <input v-model="section.data.trust_text" type="text" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
