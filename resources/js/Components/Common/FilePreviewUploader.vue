<script setup lang="ts">
import { ref } from 'vue';
import { 
    Upload, X, File as FileIcon, ImageIcon, Trash2, 
    FileText, FileArchive, FileCode 
} from 'lucide-vue-next';
import { trans as __ } from '@/Core/i18n';

const props = defineProps({
    modelValue: {
        type: Array as () => File[],
        default: () => [],
    }
});

const emit = defineEmits(['update:modelValue']);

const isDragging = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const triggerFileInput = () => {
    fileInput.value?.click();
};

const handleFiles = (files: FileList | null) => {
    if (!files) return;
    const newFiles = [...props.modelValue, ...Array.from(files)];
    emit('update:modelValue', newFiles);
};

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    handleFiles(target.files);
    target.value = '';
};

const onDrop = (e: DragEvent) => {
    isDragging.value = false;
    handleFiles(e.dataTransfer?.files || null);
};

const removeFile = (index: number) => {
    const newFiles = [...props.modelValue];
    newFiles.splice(index, 1);
    emit('update:modelValue', newFiles);
};

const clearAll = () => {
    emit('update:modelValue', []);
};

const getFilePreview = (file: File) => {
    if (file.type.startsWith('image/')) {
        return URL.createObjectURL(file);
    }
    return null;
};

const getFileIcon = (file: File) => {
    const type = file.type;
    if (type.startsWith('image/')) return ImageIcon;
    if (type.includes('pdf')) return FileText;
    if (type.includes('zip') || type.includes('rar')) return FileArchive;
    if (type.includes('json') || type.includes('javascript') || type.includes('html')) return FileCode;
    return FileIcon;
};

const getIconColor = (file: File) => {
    const type = file.type;
    if (type.startsWith('image/')) return 'text-blue-500 bg-blue-50 dark:bg-blue-900/20';
    if (type.includes('pdf')) return 'text-red-500 bg-red-50 dark:bg-red-900/20';
    if (type.includes('zip') || type.includes('rar')) return 'text-amber-500 bg-amber-50 dark:bg-amber-900/20';
    return 'text-slate-500 bg-slate-50 dark:bg-slate-900/20';
};

const formatSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <div class="space-y-4 font-sans">
        <!-- Header with Count & Clear -->
        <div v-if="modelValue.length > 0" class="flex items-center justify-between px-2">
            <span class="text-xs font-extrabold text-slate-500 uppercase tracking-widest">
                {{ modelValue.length }} {{ modelValue.length === 1 ? __('File') : __('Files') }} {{ __('Selected') }}
            </span>
            <button 
                type="button"
                @click="clearAll" 
                class="text-xs font-bold text-red-500 hover:text-red-600 transition-colors flex items-center gap-1.5"
            >
                <Trash2 :size="14" />
                {{ __('Clear All') }}
            </button>
        </div>

        <!-- Dropzone -->
        <div 
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
            @click="triggerFileInput"
            class="relative border-2 border-dashed rounded-xl p-12 text-center transition-all duration-300 cursor-pointer group overflow-hidden"
            :class="[
                isDragging 
                    ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-500/10 scale-[0.99] shadow-inner' 
                    : 'border-slate-200 dark:border-slate-700 hover:border-brand-400 dark:hover:border-slate-600 bg-slate-50/30 dark:bg-slate-900/20'
            ]"
        >
            <input 
                ref="fileInput"
                type="file" 
                multiple 
                class="hidden" 
                @change="onFileChange"
            />
            
            <div class="flex flex-col items-center justify-center space-y-5 relative z-10">
                <div class="w-18 h-18 bg-white dark:bg-slate-800 rounded-xl shadow-xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 border border-slate-100 dark:border-slate-700">
                    <Upload class="w-9 h-9 text-brand-500" />
                </div>
                <div class="space-y-1">
                    <p class="text-base font-extrabold text-slate-900 dark:text-white">{{ __('Click or drag files here to upload') }}</p>
                    <p class="text-sm text-slate-400 dark:text-slate-500 font-bold uppercase tracking-tighter">{{ __('Support for images, documents, and more (Max 10MB per file)') }}</p>
                </div>
            </div>

            <!-- Decorative background elements -->
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-brand-500/5 rounded-full blur-3xl group-hover:bg-brand-500/10 transition-colors"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-blue-500/5 rounded-full blur-3xl group-hover:bg-blue-500/10 transition-colors"></div>
        </div>

        <!-- Preview Grid -->
        <transition-group 
            name="list" 
            tag="div" 
            v-if="modelValue.length > 0" 
            class="grid grid-cols-1 md:grid-cols-2 gap-4"
        >
            <div 
                v-for="(file, index) in modelValue" 
                :key="file.name + file.size"
                class="group flex items-center p-4 bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/50 rounded-xl shadow-sm hover:shadow-xl hover:border-brand-300 dark:hover:border-brand-900/50 transition-all duration-300 relative overflow-hidden backdrop-blur-sm"
            >
                <!-- Image Preview (if image) -->
                <div 
                    v-if="file.type.startsWith('image/')" 
                    class="w-16 h-16 flex-shrink-0 rounded-xl overflow-hidden border border-slate-100 dark:border-slate-700 shadow-sm"
                >
                    <img 
                        :src="getFilePreview(file)" 
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    />
                </div>
                
                <!-- Icon (if not image) -->
                <div 
                    v-else 
                    class="w-16 h-16 flex-shrink-0 rounded-xl flex items-center justify-center border border-transparent shadow-sm transition-colors duration-300"
                    :class="getIconColor(file)"
                >
                    <component :is="getFileIcon(file)" class="w-7 h-7" />
                </div>

                <!-- File Info -->
                <div class="flex-1 min-w-0 px-4">
                    <p class="text-sm font-extrabold text-slate-900 dark:text-white truncate" :title="file.name">
                        {{ file.name }}
                    </p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50 dark:bg-slate-900/50 px-2 py-0.5 rounded-md border border-slate-100 dark:border-slate-800">
                            {{ formatSize(file.size) }}
                        </span>
                        <span v-if="file.type" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate max-w-[100px]">
                            {{ file.type.split('/')[1] || file.type }}
                        </span>
                    </div>
                </div>

                <!-- Remove Button -->
                <button 
                    type="button"
                    @click.stop="removeFile(index)"
                    class="p-2 rounded-xl text-slate-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all active:scale-95"
                    :title="__('Remove')"
                >
                    <X :size="18" />
                </button>
                
                <!-- Hover Progress Overlay (Simulated) -->
                <div class="absolute bottom-0 left-0 h-1 bg-brand-500 transition-all duration-300" style="width: 100%"></div>
            </div>
        </transition-group>
    </div>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}
</style>
