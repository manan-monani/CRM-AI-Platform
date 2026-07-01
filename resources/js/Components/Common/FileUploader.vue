<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Upload, X, File as FileIcon, Trash2, Image as ImageIcon, Download, Share2 } from 'lucide-vue-next';
import { wTrans as __ } from '@/Core/i18n';
import admin from '@/routes/admin';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';

const props = defineProps({
    modelType: {
        type: String,
        required: true,
    },
    modelId: {
        type: Number,
        required: true,
    },
    media: {
        type: Array,
        default: () => [],
    },
    collection: {
        type: String,
        default: 'default',
    },
});

const fileInput = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const showDeleteModal = ref(false);
const mediaToDeleteId = ref<number | null>(null);

const form = useForm({
    file: null as File | null,
    model_type: props.modelType,
    model_id: props.modelId,
    collection: props.collection,
});

const triggerUpload = () => {
    fileInput.value?.click();
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        uploadFile(target.files[0]);
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files.length > 0) {
        uploadFile(event.dataTransfer.files[0]);
    }
};

const uploadFile = (file: File) => {
    form.file = file;
    form.post(admin.media.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};

const deleteMedia = (mediaId: number) => {
    mediaToDeleteId.value = mediaId;
    showDeleteModal.value = true;
};

const handleDeleteMedia = () => {
    if (!mediaToDeleteId.value) {
        return;
    }

    router.delete(admin.media.destroy.url(mediaToDeleteId.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            mediaToDeleteId.value = null;
        },
    });
};

const isImage = (mimeType: string) => {
    return mimeType?.startsWith('image/');
};

const formatSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <div class="space-y-6">
        <!-- Upload Area -->
        <div
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            class="relative border-2 border-dashed rounded-xl p-8 text-center transition-all cursor-pointer group overflow-hidden"
            :class="[
                isDragging
                    ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-500/10'
                    : 'border-slate-200 dark:border-slate-700 hover:border-brand-400 dark:hover:border-slate-600 bg-slate-50/50 dark:bg-slate-900/30 shadow-sm'
            ]"
            @click="triggerUpload"
        >
            <input
                ref="fileInput"
                type="file"
                class="hidden"
                @change="handleFileSelect"
            />
            
            <div class="flex flex-col items-center justify-center space-y-4 relative z-10">
                <div class="w-14 h-14 bg-white dark:bg-slate-800 rounded-xl shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <Upload class="w-7 h-7 text-brand-500" />
                </div>
                <div>
                    <h5 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('Media Upload') }}</h5>
                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Click or drag files here') }}</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 font-bold">{{ __('Maximum 10MB per file') }}</p>
                </div>
            </div>

            <!-- Progress Bar Overlay -->
            <div v-if="form.progress" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm animate-fade-in">
                <div class="w-48 text-center space-y-3">
                    <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-slate-500">
                        <span>{{ __('Uploading...') }}</span>
                        <span>{{ form.progress.percentage }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div 
                            class="bg-brand-500 h-full rounded-full transition-all duration-300 shadow-[0_0_10px_rgba(var(--brand-primary-rgb),0.3)]" 
                            :style="{ width: form.progress.percentage + '%' }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Decorative background elements -->
            <div class="absolute -top-10 -right-10 w-24 h-24 bg-brand-500/5 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-10 -left-10 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl"></div>
        </div>

        <!-- File List -->
        <div class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.15em]">{{ __('Attached Assets') }}</h4>
                <span class="text-[10px] font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-full">{{ media.length }} {{ __('Files') }}</span>
            </div>

            <div v-if="media.length > 0" class="grid grid-cols-1 gap-3">
                <div
                    v-for="item in (media as any[])"
                    :key="item.id"
                    class="group flex items-center gap-4 p-4 bg-white dark:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 rounded-xl shadow-sm hover:shadow-md hover:border-brand-300 dark:hover:border-brand-800 transition-all duration-300 overflow-hidden relative"
                >
                    <!-- Icon/Image Preview with Glow -->
                    <div class="w-12 h-12 flex-shrink-0 rounded-xl overflow-hidden bg-slate-50 dark:bg-slate-900/50 flex items-center justify-center border border-slate-100 dark:border-slate-700 relative z-10 shadow-inner">
                        <img
                            v-if="isImage(item.mime_type)"
                            :src="item.original_url"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                            alt=""
                        />
                        <FileIcon v-else :size="20" class="text-slate-400 transition-colors group-hover:text-brand-500" />
                    </div>

                    <!-- Info -->
                    <div class="flex-grow min-w-0 relative z-10">
                        <a 
                            :href="item.original_url" 
                            target="_blank" 
                            class="block text-sm font-bold text-slate-900 dark:text-white truncate hover:text-brand-500 transition-colors"
                        >
                            {{ item.file_name }}
                        </a>
                        <div class="text-[10px] font-bold text-slate-400 flex items-center gap-2 mt-0.5 uppercase tracking-tighter">
                            <span class="text-slate-500 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/50 px-1.5 py-0.5 rounded">{{ formatSize(item.size) }}</span>
                            <span>&bull;</span>
                            <span>{{ new Date(item.created_at).toLocaleDateString() }}</span>
                        </div>
                    </div>

                    <!-- Actions Panel -->
                    <div class="flex items-center gap-1.5 relative z-10">
                        <a 
                            :href="item.original_url" 
                            download 
                            class="p-2 text-slate-400 hover:text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-500/10 rounded-xl transition-all"
                            :title="__('Download')"
                        >
                            <Download :size="16" />
                        </a>
                        <button
                            @click="deleteMedia(item.id)"
                            class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all"
                            :title="__('Delete file')"
                        >
                            <Trash2 :size="16" />
                        </button>
                    </div>

                    <!-- Hover Accent -->
                    <div class="absolute inset-y-0 left-0 w-1 bg-brand-500 transform -translate-x-full group-hover:translate-x-0 transition-transform"></div>
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-else class="text-center py-12 px-6 bg-slate-50/50 dark:bg-slate-900/30 border-2 border-dotted border-slate-200 dark:border-slate-800 rounded-xl space-y-3">
                <div class="w-12 h-12 bg-white dark:bg-slate-800 rounded-xl shadow-sm flex items-center justify-center mx-auto opacity-50">
                    <FileIcon class="w-6 h-6 text-slate-300" />
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ __('No Files Yet') }}</p>
                    <p class="text-[10px] text-slate-400 italic">{{ __('Attach documents, images or references above') }}</p>
                </div>
            </div>
        </div>
    </div>

    <ConfirmationModal
        :show="showDeleteModal"
        :title="__('Delete File?')"
        :message="__('Are you sure you want to delete this file? This action cannot be undone.')"
        :confirmText="__('Yes, Delete File')"
        :cancelText="__('No, Cancel')"
        type="danger"
        @close="showDeleteModal = false"
        @confirm="handleDeleteMedia"
    />
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
