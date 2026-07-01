<script setup lang="ts">
import { PropType, watch, computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import InputLabel from '@/Components/Core/InputLabel.vue';
import TextInput from '@/Components/Core/TextInput.vue';
import SelectInput from '@/Components/Core/SelectInput.vue';
import SearchableSelect from '@/Components/Core/SearchableSelect.vue';
import TextArea from '@/Components/Core/TextArea.vue';
import PrimaryButton from '@/Components/Core/PrimaryButton.vue';
import SecondaryButton from '@/Components/Core/SecondaryButton.vue';
import InputError from '@/Components/Core/InputError.vue';
import FilePreviewUploader from '@/Components/Common/FilePreviewUploader.vue';
import { 
    Calendar, 
    Type, 
    User,
    Link,
    FileText,
    CheckCircle2,
    Clock,
    AlertCircle,
    Flag,
    Trash2,
    Image as ImageIcon,
    FileSpreadsheet,
    FileCode,
    FileArchive,
    File as FileGeneric
} from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { wTrans as __ } from '@/Core/i18n';
import admin from '@/routes/admin';

const props = defineProps({
    show: Boolean,
    taskableType: String,
    taskableId: Number,
    users: {
        type: Array as PropType<any[]>,
        default: () => [],
    },
    deals: {
        type: Array as PropType<any[]>,
        default: () => [],
    },
    task: {
        type: Object as PropType<any>,
        default: null,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    taskable_type: props.taskableType,
    taskable_id: props.taskableId ?? null,
    title: '',
    description: '',
    priority: 'medium',
    due_date: null as string | null,
    assigned_to: null,
    attachments: [] as File[],
});


const dealOptions = computed(() => {
    return props.deals.map(deal => ({
        value: deal.id,
        label: deal.title
    }));
});

const userOptions = computed(() => {
    return props.users.map(user => ({
        value: user.id,
        label: user.name,
        image: user.profile_image ? `/storage/${user.profile_image}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random&color=fff`
    }));
});

const formatDateForInput = (dateString: string | null) => {
    if (!dateString) return null;
    const date = new Date(dateString);
    // Adjust for timezone offset to keep local time
    const offset = date.getTimezoneOffset() * 60000;
    const localISOTime = (new Date(date.getTime() - offset)).toISOString().slice(0, 16);
    return localISOTime;
};

const existingAttachments = ref<any[]>([]);

watch(() => props.show, (isVisible) => {
    if (isVisible) {
        if (props.task) {
            form.taskable_type = props.task.taskable_type;
            form.taskable_id = props.task.taskable_id;
            form.title = props.task.title;
            form.description = props.task.description;
            form.priority = props.task.priority;
            form.due_date = formatDateForInput(props.task.due_date);
            form.assigned_to = props.task.assigned_to;
            // Initialize existing attachments
            existingAttachments.value = props.task.media ? [...props.task.media] : [];
        } else {
            form.taskable_id = props.taskableId ?? null;
            form.taskable_type = props.taskableType;
            form.reset('title', 'description', 'priority', 'due_date', 'assigned_to', 'attachments');
            existingAttachments.value = [];
        }
    }
});

const getFileIcon = (file: any) => {
    const mime = file.mime_type || '';
    const name = file.file_name || '';
    
    if (mime.includes('image')) return ImageIcon;
    if (mime.includes('pdf')) return FileText;
    if (mime.includes('spreadsheet') || mime.includes('excel') || mime.includes('csv') || name.endsWith('.csv') || name.endsWith('.xlsx')) return FileSpreadsheet;
    if (mime.includes('word') || mime.includes('document')) return FileText;
    if (mime.includes('zip') || mime.includes('compressed') || mime.includes('tar')) return FileArchive;
    if (mime.includes('text/html') || mime.includes('json') || mime.includes('javascript') || name.endsWith('.js') || name.endsWith('.php')) return FileCode;
    
    return FileGeneric;
};

const isImage = (file: any) => {
    return file.mime_type?.startsWith('image/') || 
           /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(file.file_name);
};

const showConfirmModal = ref(false);
const mediaToDelete = ref<number | null>(null);

const deleteMedia = (fileId: number) => {
    mediaToDelete.value = fileId;
    showConfirmModal.value = true;
};

const confirmDeleteMedia = () => {
    if (mediaToDelete.value) {
        router.delete(admin.media.destroy.url(mediaToDelete.value), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                existingAttachments.value = existingAttachments.value.filter(file => file.id !== mediaToDelete.value);
                closeConfirmModal();
            },
        });
    }
};

const closeConfirmModal = () => {
    showConfirmModal.value = false;
    mediaToDelete.value = null;
};

const submit = () => {
    if (props.task) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(admin.tasks.update.url(props.task.id), {
            forceFormData: true,
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    } else {
        form.post(admin.tasks.store.url(), {
            forceFormData: true,
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    }
};
</script>

<template>
    <Modal :show="show" @close="$emit('close')" maxWidth="3xl">
        <div class="relative overflow-hidden">
            <!-- Header Section -->
            <div class="p-8 pb-0">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 class="w-6 h-6" />
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-none">
                                {{ task ? __('Edit Task') : __('Create New Task') }}
                            </h2>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                {{ task ? __('Update mission details') : __('Define mission and set deadlines') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="p-8 pt-6 space-y-8">
                <!-- Section 1: Task Core Information -->
                <div class="bg-slate-50/50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 rounded-xl p-6 space-y-6">
                    <div class="flex items-center space-x-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                        <Type class="w-4 h-4 text-emerald-500" />
                        <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Primary Details') }}</span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <InputLabel for="title" :value="__('Task Title')" class="text-[10px] font-black uppercase text-slate-400 mb-1.5 ml-1" />
                            <TextInput
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="mt-0 block w-full bg-white dark:bg-slate-800 border-none shadow-sm focus:ring-emerald-500 rounded-xl h-12"
                                :placeholder="__('e.g. Follow up on proposal revision')"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.title" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="priority" :value="__('Priority')" class="text-[10px] font-black uppercase text-slate-400 mb-1.5 ml-1" />
                                <div class="relative">
                                    <SelectInput
                                        id="priority"
                                        v-model="form.priority"
                                        class="mt-0 block w-full bg-white dark:bg-slate-800 border-none shadow-sm focus:ring-emerald-500 rounded-xl h-12 pl-10"
                                    >
                                        <option value="low">{{ __('Low') }}</option>
                                        <option value="medium">{{ __('Medium') }}</option>
                                        <option value="high">{{ __('High') }}</option>
                                        <option value="urgent">{{ __('Urgent') }}</option>
                                    </SelectInput>
                                    <Flag class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                </div>
                                <InputError :message="form.errors.priority" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="due_date" :value="__('Due Date')" class="text-[10px] font-black uppercase text-slate-400 mb-1.5 ml-1" />
                                <div class="relative">
                                    <TextInput
                                        id="due_date"
                                        v-model="form.due_date"
                                        type="datetime-local"
                                        class="mt-0 block w-full bg-white dark:bg-slate-800 border-none shadow-sm focus:ring-emerald-500 rounded-xl h-12 pl-10"
                                        required
                                    />
                                    <Clock class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                                </div>
                                <InputError :message="form.errors.due_date" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Context & Assets -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Assignment & Relation -->
                    <div class="bg-slate-50/50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 rounded-xl p-6 space-y-6">
                        <div class="flex items-center space-x-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                            <User class="w-4 h-4 text-emerald-500" />
                            <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Assignment') }}</span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <InputLabel for="assigned_to" :value="__('Assign To')" class="text-[10px] font-black uppercase text-slate-400 mb-1.5 ml-1" />
                                <SearchableSelect
                                    v-model="form.assigned_to"
                                    :options="userOptions"
                                    :placeholder="__('Unassigned')"
                                    :error="form.errors.assigned_to"
                                    class="rounded-xl"
                                />
                            </div>

                            <div v-if="!taskableType">
                                <InputLabel for="taskable_type" :value="__('Related To')" class="text-[10px] font-black uppercase text-slate-400 mb-1.5 ml-1" />
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="relative">
                                        <SelectInput
                                            id="taskable_type"
                                            v-model="form.taskable_type"
                                            class="mt-0 block w-full bg-white dark:bg-slate-800 border-none shadow-sm focus:ring-emerald-500 rounded-xl h-12 pl-10"
                                        >
                                            <option value="">{{ __('None') }}</option>
                                            <option value="App\Models\Deal">{{ __('Deal') }}</option>
                                        </SelectInput>
                                        <Link class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                    </div>

                                    <div v-if="form.taskable_type === 'App\\Models\\Deal'">
                                        <SearchableSelect
                                            v-model="form.taskable_id"
                                            :options="dealOptions"
                                            :placeholder="__('Select a deal...')"
                                            :error="form.errors.taskable_id"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="bg-slate-50/50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 rounded-xl p-6 space-y-6">
                        <div class="flex items-center space-x-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                            <FileText class="w-4 h-4 text-emerald-500" />
                            <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Context') }}</span>
                        </div>

                        <div>
                            <InputLabel for="description" :value="__('Description')" class="text-[10px] font-black uppercase text-slate-400 mb-1.5 ml-1" />
                            <TextArea
                                id="description"
                                v-model="form.description"
                                class="mt-0 block w-full"
                                rows="6"
                                :placeholder="__('Add strategic notes or requirements around this task...')"
                            />
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Section 3: Media Assets -->
                <div class="bg-slate-50/50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 rounded-xl p-6">
                    <div class="flex items-center space-x-2 pb-4 border-b border-slate-100 dark:border-slate-800 mb-6">
                        <Link class="w-4 h-4 text-emerald-500" />
                        <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Media Assets') }}</span>
                    </div>
                    
                    <FilePreviewUploader v-model="form.attachments" />

                    <!-- Existing Attachments -->
                    <div v-if="existingAttachments.length > 0" class="mt-4 space-y-2">
                        <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-wider">{{ __('Existing Attachments') }}</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div v-for="file in existingAttachments" :key="file.id" class="flex items-center justify-between p-3 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 group">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0 overflow-hidden relative">
                                        <img 
                                            v-if="isImage(file)" 
                                            :src="file.original_url" 
                                            :alt="file.file_name"
                                            class="w-full h-full object-cover"
                                        />
                                        <component 
                                            v-else 
                                            :is="getFileIcon(file)" 
                                            class="w-5 h-5 text-slate-500" 
                                        />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate">{{ file.file_name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ (file.size / 1024).toFixed(1) }} KB</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <a :href="file.original_url" target="_blank" class="p-1.5 text-slate-400 hover:text-emerald-500 transition-colors">
                                        <Link class="w-4 h-4" />
                                    </a>
                                    <button 
                                        @click.prevent="deleteMedia(file.id)" 
                                        type="button"
                                        class="p-1.5 text-slate-400 hover:text-red-500 transition-colors"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Section -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4">
                    <div class="flex items-center text-slate-400">
                        <AlertCircle class="w-3.5 h-3.5 mr-1.5" />
                        <span class="text-[10px] font-bold uppercase tracking-wider">{{ __('Double check all fields before launch') }}</span>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <SecondaryButton @click="$emit('close')" class="flex-1 sm:flex-none justify-center h-12 rounded-xl border-none bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold uppercase tracking-widest text-[10px]">
                            {{ __('Cancel') }}
                        </SecondaryButton>

                        <PrimaryButton 
                            type="submit" 
                            :class="{ 'opacity-25': form.processing }" 
                            :disabled="form.processing"
                            class="flex-1 sm:flex-none justify-center h-12 px-8 rounded-xl bg-emerald-500 hover:bg-emerald-600 border-none shadow-lg shadow-emerald-500/20 text-white font-black uppercase tracking-widest text-[10px]"
                        >
                            {{ task ? __('Update Task') : __('Register Task') }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
        :show="showConfirmModal"
        :title="__('Delete Attachment')"
        :content="__('Are you sure you want to delete this attachment? This action cannot be undone.')"
        :confirmText="__('Delete')"
        confirmType="danger"
        @close="closeConfirmModal"
        @confirm="confirmDeleteMedia"
    />
</template>

<style scoped>
@reference "tailwindcss";

:deep(.searchable-select-input) {
    @apply bg-white dark:bg-slate-800 border-none shadow-sm rounded-xl h-12;
}
</style>
