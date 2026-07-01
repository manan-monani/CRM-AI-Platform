<script setup lang="ts">
import { ref, PropType, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Calendar, User, Flag, CheckCircle, Clock, ArrowLeft, Edit, Trash2, Users, Plus } from 'lucide-vue-next';
import { wTrans as __ } from '@/Core/i18n';
import admin from '@/routes/admin';
import TaskStatusModal from '@/Components/CRM/TaskStatusModal.vue';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import Modal from '@/Components/Common/Modal.vue';
import InputLabel from '@/Components/Core/InputLabel.vue';
import SelectInput from '@/Components/Core/SelectInput.vue';
import InputError from '@/Components/Core/InputError.vue';
import SearchableSelect from '@/Components/Core/SearchableSelect.vue';
import PrimaryButton from '@/Components/Core/PrimaryButton.vue';
import SecondaryButton from '@/Components/Core/SecondaryButton.vue';
import ActivityItem from '@/Components/CRM/ActivityItem.vue';
import ActivityLogModal from '@/Components/CRM/ActivityLogModal.vue';
import FileUploader from '@/Components/Common/FileUploader.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    task: Object as PropType<any>,
    users: Array as PropType<any[]>,
});

const showStatusModal = ref(false);
const showPriorityModal = ref(false);
const showAssignModal = ref(false);
const showActivityModal = ref(false);

const priorityForm = useForm({
    priority: props.task?.priority || 'medium',
});

const assignForm = useForm({
    assigned_to: props.task?.assigned_to || '',
});

const updatePriority = () => {
    priorityForm.patch(admin.tasks.update.url(props.task.id), {
        preserveScroll: true,
        onSuccess: () => {
            showPriorityModal.value = false;
        },
    });
};

const updateAssignment = () => {
    assignForm.patch(admin.tasks.update.url(props.task.id), {
        preserveScroll: true,
        onSuccess: () => {
            showAssignModal.value = false;
        },
    });
};


const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
        case 'in_progress': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300';
        case 'cancelled': return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
        default: return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
    }
};

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'high': return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
        case 'medium': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300';
        default: return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const userOptions = computed(() => {
    return props.users?.map(user => ({
        value: user.id,
        label: user.name,
        image: user.profile_image || `https://ui-avatars.com/api/?name=${user.name}`
    })) || [];
});

const isOverdue = computed(() => {
    if (!props.task?.due_date) return false;
    return new Date(props.task.due_date) < new Date() && props.task.status !== 'completed';
});

</script>

<template>
    <Head :title="`Task: ${task.title}`" />

    <AdminLayout>
        <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Back Button -->
            <Link :href="admin.tasks.index.url()" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 transition-colors mb-6">
                <ArrowLeft :size="16" />
                {{ __('Back to Tasks') }}
            </Link>

            <!-- Task Header -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-200 dark:border-slate-800 p-8 mb-6">
                <div class="flex justify-between items-start gap-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900 dark:text-white">{{ task.title }}</h1>
                        <div v-if="task.taskable" class="mt-1 flex items-center gap-2 text-sm text-slate-500">
                            <span>{{ __('Related to') }}:</span>
                            <Link 
                                v-if="task.taskable_type === 'App\\Models\\Deal'"
                                :href="admin.deals.show.url(task.taskable_id)"
                                class="font-semibold text-brand-600 dark:text-brand-400 hover:underline"
                            >
                                {{ task.taskable.name || task.taskable.title }}
                            </Link>
                            <span v-else class="font-semibold">{{ task.taskable.name || task.taskable.title }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <!-- Actions can be added here -->
                    </div>
                </div>

                <!-- Key Info Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Status Card -->
                    <button
                        @click="showStatusModal = true"
                        class="group p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:border-brand-400 dark:hover:border-brand-500 transition-all text-left"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Status') }}</span>
                            <Edit :size="14" class="text-slate-400 group-hover:text-brand-500 transition-colors" />
                        </div>
                        <span class="inline-flex px-3 py-1.5 rounded-xl text-sm font-bold capitalize" :class="getStatusColor(task.status)">
                            {{ task.status.replace('_', ' ') }}
                        </span>
                    </button>

                    <!-- Priority Card -->
                    <button
                        @click="showPriorityModal = true"
                        class="group p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:border-brand-400 dark:hover:border-brand-500 transition-all text-left"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Priority') }}</span>
                            <Edit :size="14" class="text-slate-400 group-hover:text-brand-500 transition-colors" />
                        </div>
                        <span class="inline-flex px-3 py-1.5 rounded-xl text-sm font-bold capitalize" :class="getPriorityColor(task.priority)">
                            {{ task.priority }}
                        </span>
                    </button>

                    <!-- Assigned Card -->
                    <button
                        @click="showAssignModal = true"
                        class="group p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:border-brand-400 dark:hover:border-brand-500 transition-all text-left"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Assigned To') }}</span>
                            <Edit :size="14" class="text-slate-400 group-hover:text-brand-500 transition-colors" />
                        </div>
                        <div v-if="task.assigned_to_user" class="flex items-center gap-2">
                            <img :src="task.assigned_to_user.profile_photo_url || `https://ui-avatars.com/api/?name=${task.assigned_to_user.name}`" class="w-6 h-6 rounded-full border border-slate-200 dark:border-slate-600 object-cover" alt="">
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ task.assigned_to_user.name }}</span>
                        </div>
                        <span v-else class="text-sm text-slate-500 dark:text-slate-400 italic">{{ __('Unassigned') }}</span>
                    </button>
                </div>

                <!-- Due Date -->
                <div class="mt-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <Calendar :size="20" :class="isOverdue ? 'text-red-500' : 'text-slate-400'" />
                        <div>
                            <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Due Date') }}</div>
                            <div class="text-base font-semibold" :class="isOverdue ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white'">
                                {{ formatDate(task.due_date) }}
                                <span v-if="isOverdue" class="text-xs font-bold ml-2 px-2 py-0.5 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                    {{ __('Overdue') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div v-if="task.description" class="bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-200 dark:border-slate-800 p-8 mb-6">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">{{ __('Description') }}</h2>
                <p class="text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">{{ task.description }}</p>
            </div>

            <!-- Related To -->
            <div v-if="task.taskable" class="bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-200 dark:border-slate-800 p-8 mb-6">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">{{ __('Related To') }}</h2>
                <Link 
                    :href="task.taskable_type === 'App\\Models\\Deal' ? admin.deals.show.url(task.taskable_id) : '#'"
                    class="flex items-center gap-3 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:border-brand-400 dark:hover:border-brand-500 transition-all group"
                >
                    <div class="w-10 h-10 rounded-xl bg-brand-100 dark:bg-brand-900/20 flex items-center justify-center group-hover:bg-brand-200 dark:group-hover:bg-brand-900/40 transition-colors">
                        <Users :size="20" class="text-brand-600 dark:text-brand-400" />
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ task.taskable_type.split('\\').pop() }}</div>
                        <div class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ task.taskable.name || task.taskable.title }}</div>
                    </div>
                </Link>
            </div>

            <!-- Activity Timeline -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-200 dark:border-slate-800 p-8 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Activity Timeline') }}</h2>
                    <SecondaryButton @click="showActivityModal = true" size="sm">
                        <Plus class="w-4 h-4 mr-2" />
                        {{ __('Log Activity') }}
                    </SecondaryButton>
                </div>

                <div class="space-y-0 relative">
                    <div v-if="task.activities && task.activities.length > 0">
                        <ActivityItem 
                            v-for="activity in task.activities" 
                            :key="activity.id" 
                            :activity="activity" 
                        />
                    </div>
                    <div v-else class="text-center py-12 text-slate-400 dark:text-slate-500">
                        <Clock :size="48" class="mx-auto mb-4 opacity-20" />
                        <p>{{ __('No activities logged yet.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Files -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-200 dark:border-slate-800 p-8 mb-6">
                 <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Files') }}</h2>
                </div>
                
                <FileUploader
                    :model-type="'App\\Models\\Task'"
                    :model-id="task.id"
                    :media="task.media"
                />
            </div>

            <!-- Meta Info -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-slate-200 dark:border-slate-800 p-8">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">{{ __('Task Information') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('Created By') }}</div>
                        <div v-if="task.created_by_user" class="flex items-center gap-2">
                            <img :src="task.created_by_user.profile_photo_url || `https://ui-avatars.com/api/?name=${task.created_by_user.name}`" class="w-6 h-6 rounded-full border border-slate-200 dark:border-slate-600 object-cover" alt="">
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ task.created_by_user.name }}</span>
                        </div>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">{{ __('Created At') }}</div>
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ formatDateTime(task.created_at) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Modal -->
        <TaskStatusModal
            :show="showStatusModal"
            :task="task"
            @close="showStatusModal = false"
        />

        <!-- Priority Modal -->
        <Modal :show="showPriorityModal" @close="showPriorityModal = false" maxWidth="md" :title="__('Change Priority')">
            <form @submit.prevent="updatePriority" class="space-y-6">
                <div>
                    <InputLabel :value="__('Priority')" class="mb-2" />
                    <SelectInput v-model="priorityForm.priority" class="w-full">
                        <option value="low">{{ __('Low') }}</option>
                        <option value="medium">{{ __('Medium') }}</option>
                        <option value="high">{{ __('High') }}</option>
                    </SelectInput>
                    <InputError :message="priorityForm.errors.priority" class="mt-2" />
                </div>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="showPriorityModal = false">{{ __('Cancel') }}</SecondaryButton>
                    <PrimaryButton type="submit" :disabled="priorityForm.processing">{{ __('Update Priority') }}</PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- Assignment Modal -->
        <Modal :show="showAssignModal" @close="showAssignModal = false" maxWidth="md" :title="__('Assign Task')">
            <form @submit.prevent="updateAssignment" class="space-y-6">
                <div>
                    <InputLabel :value="__('Assigned To')" class="mb-2" />
                    <SearchableSelect
                        v-model="assignForm.assigned_to"
                        :options="userOptions"
                        :placeholder="__('Unassigned')"
                        :error="assignForm.errors.assigned_to"
                    />
                    <InputError :message="assignForm.errors.assigned_to" class="mt-2" />
                </div>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="showAssignModal = false">{{ __('Cancel') }}</SecondaryButton>
                    <PrimaryButton type="submit" :disabled="assignForm.processing">{{ __('Update Assignment') }}</PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- Activity Log Modal -->
        <ActivityLogModal 
            :show="showActivityModal" 
            :activityable-type="'App\\Models\\Task'"
            :activityable-id="task.id"
            @close="showActivityModal = false" 
        />

    </AdminLayout>
</template>
