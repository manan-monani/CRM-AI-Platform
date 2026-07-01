<script setup lang="ts">
import { ref, computed, PropType } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Pencil, Plus, Trash2, Calendar, DollarSign, User } from 'lucide-vue-next';
import PrimaryButton from '@/Components/Core/PrimaryButton.vue';
import SecondaryButton from '@/Components/Core/SecondaryButton.vue';
import ActivityItem from '@/Components/CRM/ActivityItem.vue';
import ActivityLogModal from '@/Components/CRM/ActivityLogModal.vue';
import DealAssignmentModal from '@/Components/CRM/DealAssignmentModal.vue';
import TaskCreateModal from '@/Components/CRM/TaskCreateModal.vue';
import TaskDetailsModal from '@/Components/CRM/TaskDetailsModal.vue';
import TaskItem from '@/Components/CRM/TaskItem.vue';
import FileUploader from '@/Components/Common/FileUploader.vue';
import { wTrans as __ } from '@/Core/i18n';
import { formatMoney as money } from '@/Core/money';
import admin from '@/routes/admin';

import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';

const props = defineProps({
    deal: {
        type: Object as PropType<any>,
        required: true,
    },
    users: {
        type: Array as PropType<any[]>,
        default: () => [],
    },
});

const showActivityModal = ref(false);
const showTaskModal = ref(false);
const showDetailsModal = ref(false);
const showAssignmentModal = ref(false);
const selectedTask = ref<any>(null);
const selectedActivity = ref<any>(null);

// Delete Modal State
const showDeleteModal = ref(false);
const deleteProcessing = ref(false);
const itemToDelete = ref<{ type: 'task' | 'activity' | 'deal', id: number, title?: string } | null>(null);

const editTask = (task: any) => {
    selectedTask.value = task;
    showTaskModal.value = true;
};

const deleteTask = (task: any) => {
    itemToDelete.value = {
        type: 'task',
        id: task.id,
        title: task.title
    };
    showDeleteModal.value = true;
};

const editActivity = (activity: any) => {
    selectedActivity.value = activity;
    showActivityModal.value = true;
};

const deleteActivity = (activity: any) => {
    itemToDelete.value = {
        type: 'activity',
        id: activity.id,
        title: activity.subject
    };
    showDeleteModal.value = true;
};

const deleteDeal = () => {
    itemToDelete.value = {
        type: 'deal',
        id: props.deal.id,
        title: props.deal.title
    };
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!itemToDelete.value) return;

    deleteProcessing.value = true;
    let url = '';

    switch (itemToDelete.value.type) {
        case 'task':
            url = admin.tasks.destroy.url(itemToDelete.value.id);
            break;
        case 'activity':
            url = admin.activities.destroy.url(itemToDelete.value.id);
            break;
        case 'deal':
            url = admin.deals.destroy.url(itemToDelete.value.id);
            break;
    }

    router.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            itemToDelete.value = null;
            deleteProcessing.value = false;
        },
        onError: () => {
            deleteProcessing.value = false;
        }
    });
};

const openDetails = (task: any) => {
    selectedTask.value = task;
    showDetailsModal.value = true;
};

const formatCurrency = (value: number) => {
    return money(value);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const updateStage = (stage: string) => {
    router.patch(admin.deals.stage.url(props.deal.id), { stage });
};

const formatLabel = (slug: string | number) => {
    return String(slug)
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

const customData = computed(() => {
    if (!props.deal?.dealable) return null;
    
    // If dealable is a Lead
    if (props.deal.dealable_type.includes('Lead')) {
        return props.deal.dealable.custom_data;
    }
    
    // If dealable is a User (Customer) with a lead relationship
    if (props.deal.dealable_type.includes('User') && props.deal.dealable.lead) {
        return props.deal.dealable.lead.custom_data;
    }
    
    return null;
});

const stages = ['lead', 'new', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
</script>

<template>
    <Head :title="deal.title" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ deal.title }}
                    </h2>
                    <span class="px-3 py-1 rounded-full text-sm font-bold capitalize bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                        {{ deal.stage }}
                    </span>
                    <span 
                        :class="deal.status === 'open' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300'"
                        class="px-3 py-1 rounded-full text-sm font-bold capitalize"
                    >
                        {{ deal.status }}
                    </span>
                </div>
                <div class="flex gap-2">
                    <Link :href="admin.deals.edit.url(deal.id)">
                        <SecondaryButton>
                            <Pencil class="w-4 h-4 mr-2" />
                            {{ __('Edit') }}
                        </SecondaryButton>
                    </Link>
                    <button 
                        @click="deleteDeal" 
                        class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                    >
                        <Trash2 class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 h-[calc(100vh-4rem)] overflow-hidden">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
                <!-- Left Column: Main Info & Timeline -->
                <div class="lg:col-span-2 space-y-6 h-full overflow-y-auto hide-scrollbar pr-2">
                    <!-- Workflow / Stage Progress -->
                     <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-x-auto">
                        <div class="flex justify-between items-center min-w-[500px]">
                            <button 
                                v-for="(stage, index) in stages" 
                                :key="stage"
                                @click="updateStage(stage)"
                                class="flex flex-col items-center gap-2 group w-full"
                                :disabled="deal.status === 'closed' && deal.stage !== stage"
                            >
                                <div 
                                    class="w-full h-2 rounded-full transition-all relative"
                                    :class="deal.stage === stage ? 'bg-brand-500' : (stages.indexOf(deal.stage) > index ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-700 hover:bg-slate-300')"
                                >
                                </div>
                                <span class="text-xs font-bold capitalize" :class="deal.stage === stage ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500'">{{ stage }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                        <h3 class="font-bold text-lg mb-4 text-slate-900 dark:text-white">{{ __('About Deal') }}</h3>
                        <div class="prose dark:prose-invert max-w-none text-sm text-slate-600 dark:text-slate-400">
                            {{ deal.description || __('No description provided.') }}
                        </div>
                    </div>

                    <!-- Tasks Widget -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                         <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ __('Tasks') }}</h3>
                            <button @click="() => { selectedTask = null; showTaskModal = true; }" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-700 rounded transition-colors">
                                <Plus class="w-4 h-4 text-slate-500" />
                            </button>
                        </div>
                        
                        <div class="space-y-0">
                             <div v-if="deal.tasks && deal.tasks.length > 0">
                                <TaskItem 
                                    v-for="task in deal.tasks" 
                                    :key="task.id" 
                                    :task="task" 
                                    :users="users"
                                    @show-details="openDetails"
                                    @edit="editTask"
                                    @delete="deleteTask"
                                />
                             </div>
                             <div v-else class="text-center py-8 text-slate-400">
                                 {{ __('No tasks created yet.') }}
                             </div>
                        </div>
                    </div>

                    <!-- Activity Timeline -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ __('Activity Timeline') }}</h3>
                            <div class="flex gap-2">
                                <SecondaryButton @click="() => { selectedActivity = null; showActivityModal = true; }" size="sm">
                                    <Plus class="w-4 h-4 mr-2" />
                                    {{ __('Log Activity') }}
                                </SecondaryButton>
                            </div>
                        </div>

                        <div class="space-y-0 relative">
                             <div v-if="deal.activities && deal.activities.length > 0">
                                <ActivityItem 
                                    v-for="activity in deal.activities" 
                                    :key="activity.id" 
                                    :activity="activity" 
                                    @edit="editActivity"
                                    @delete="deleteActivity"
                                />
                             </div>
                             <div v-else class="text-center py-8 text-slate-400">
                                 {{ __('No activities logged yet.') }}
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Details & Tasks -->
                <div class="space-y-6 h-full overflow-y-auto hide-scrollbar pl-2">
                    <!-- Deal Info Card -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                        <h3 class="font-bold text-md mb-4 text-slate-900 dark:text-white">{{ __('Deal Details') }}</h3>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-sm text-slate-500">{{ __('Status') }}</span>
                                <span 
                                    class="font-bold capitalize"
                                    :class="deal.status === 'open' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-400'"
                                >
                                    {{ deal.status }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-sm text-slate-500">{{ __('Value') }}</span>
                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ formatCurrency(deal.value) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-sm text-slate-500">{{ __('Probability') }}</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ deal.probability }}%</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-sm text-slate-500">{{ __('Close Date') }}</span>
                                <span class="font-bold text-slate-900 dark:text-white flex items-center gap-1">
                                    <Calendar :size="14" />
                                    {{ formatDate(deal.expected_close_date) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-sm text-slate-500">{{ __('Related To') }}</span>
                                <span class="font-bold text-brand-600 dark:text-brand-400">
                                    {{ deal.dealable?.name }}
                                </span>
                            </div>
                        </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs text-slate-500 uppercase font-bold tracking-wider">{{ __('Sales Rep') }}</span>
                            <button 
                                @click="showAssignmentModal = true"
                                class="text-xs text-brand-600 hover:text-brand-700 font-semibold"
                            >
                                {{ __('Reassign') }}
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <img :src="deal.assigned_to_user?.profile_photo_url || `https://ui-avatars.com/api/?name=${deal.assigned_to_user?.name}&background=random`" class="w-8 h-8 rounded-full object-cover">
                            <div>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ deal.assigned_to_user?.name || __('Unassigned') }}</p>
                                <p class="text-xs text-slate-500">{{ deal.assigned_to_user?.email }}</p>
                            </div>
                        </div>
                    </div>
                    </div>

                    <!-- Custom Data Widget -->
                    <div v-if="customData && Object.keys(customData).length > 0" class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                        <h3 class="font-bold text-md mb-4 text-slate-900 dark:text-white">{{ __('Custom Fields') }}</h3>
                        <div class="space-y-4">
                            <div v-for="(value, key) in customData" :key="key" class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-sm text-slate-500">{{ formatLabel(key) }}</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ value }}</span>
                            </div>
                        </div>
                    </div>


                    <!-- Files Widget -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                         <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-md text-slate-900 dark:text-white">{{ __('Files') }}</h3>
                        </div>
                        
                        <FileUploader
                            :model-type="'App\\Models\\Deal'"
                            :model-id="deal.id"
                            :media="deal.media"
                        />
                    </div>
                </div>
            </div>
        </div>

        <ActivityLogModal 
            :show="showActivityModal" 
            :activityable-type="'App\\Models\\Deal'"
            :activityable-id="deal.id"
            :activity="selectedActivity"
            @close="showActivityModal = false" 
        />

        <TaskCreateModal 
            :show="showTaskModal" 
            :taskable-type="'App\\Models\\Deal'"
            :taskable-id="deal.id"
            :users="users" 
            :deals="[deal]"
            :task="selectedTask"
            @close="showTaskModal = false" 
        />

        <TaskDetailsModal
            :show="showDetailsModal"
            :task="selectedTask"
            @close="showDetailsModal = false"
        />

        <DealAssignmentModal
            :show="showAssignmentModal"
            :deal="deal"
            :users="users"
            @close="showAssignmentModal = false"
        />

        <ConfirmationModal
            :show="showDeleteModal"
            :title="itemToDelete?.type === 'deal' ? 'Delete Deal' : (itemToDelete?.type === 'task' ? 'Delete Task' : 'Delete Activity')"
            :message="`Are you sure you want to delete this ${itemToDelete?.type}? This action cannot be undone.`"
            confirm-text="Yes, delete it"
            :processing="deleteProcessing"
            @close="showDeleteModal = false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
