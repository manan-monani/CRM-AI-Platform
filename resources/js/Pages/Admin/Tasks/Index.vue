<script setup lang="ts">
import { ref, computed, PropType } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, CheckSquare, Search, X } from 'lucide-vue-next';
import PrimaryButton from '@/Components/Core/PrimaryButton.vue';
import SecondaryButton from '@/Components/Core/SecondaryButton.vue';
import TextInput from '@/Components/Core/TextInput.vue';
import Pagination from '@/Components/Core/Pagination.vue';
import SelectInput from '@/Components/Core/SelectInput.vue';
import SearchableSelect from '@/Components/Core/SearchableSelect.vue';
import TaskItem from '@/Components/CRM/TaskItem.vue';
import TaskCreateModal from '@/Components/CRM/TaskCreateModal.vue';
import TaskDetailsModal from '@/Components/CRM/TaskDetailsModal.vue';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';
import { wTrans as __ } from '@/Core/i18n';
import admin from '@/routes/admin';

const props = defineProps({
    tasks: {
        type: Object as PropType<any>,
        required: true,
    },
    users: {
        type: Array as PropType<any[]>,
        default: () => [],
    },
    deals: {
        type: Array as PropType<any[]>,
        default: () => [],
    },
    filters: {
        type: Object as PropType<any>,
        default: () => ({}),
    },
});

const showCreateModal = ref(false);
const showDetailsModal = ref(false);
const selectedTask = ref<any>(null);
const taskToDelete = ref<any>(null);
const deleteProcessing = ref(false);
const showDeleteModal = ref(false);
const statusFilter = ref(props.filters?.status || 'all');
const userFilter = ref(props.filters?.user_id || '');
const dealFilter = ref(props.filters?.deal_id || '');
const searchFilter = ref(props.filters?.search || '');

const userOptions = computed(() => {
    const options = (props.users as any[])?.map(user => ({
        value: user.id,
        label: user.name,
        image: user.profile_image ? `/storage/${user.profile_image}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random&color=fff`
    })) || [];
    return options;
});

const dealOptions = computed(() => {
    return (props.deals as any[])?.map(deal => ({
        value: deal.id,
        label: deal.title
    })) || [];
});

const handleFilter = () => {
    router.get(admin.tasks.index.url(), {
        status: statusFilter.value,
        user_id: userFilter.value,
        deal_id: dealFilter.value,
        search: searchFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    statusFilter.value = 'all';
    userFilter.value = '';
    dealFilter.value = '';
    searchFilter.value = '';
    handleFilter();
};

const openDetails = (task: any) => {
    selectedTask.value = task;
    showDetailsModal.value = true;
};

const openCreateModal = () => {
    selectedTask.value = null;
    showCreateModal.value = true;
};

const editTask = (task: any) => {
    selectedTask.value = task;
    showCreateModal.value = true;
};

const requestDeleteTask = (task: any) => {
    taskToDelete.value = task;
    showDeleteModal.value = true;
};

const confirmDeleteTask = () => {
    if (!taskToDelete.value) {
        return;
    }

    deleteProcessing.value = true;
    router.delete(admin.tasks.destroy.url(taskToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            taskToDelete.value = null;
        },
        onFinish: () => {
            deleteProcessing.value = false;
        },
    });
};
</script>

<template>
    <Head :title="__('Tasks')" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Tasks') }}
                </h2>
                <PrimaryButton @click="openCreateModal">
                    <Plus class="w-4 h-4 mr-2" />
                    {{ __('Create Task') }}
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8 flex flex-col lg:flex-row gap-4 justify-between items-stretch lg:items-center bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200/60 dark:border-slate-700 shadow-sm transition-all duration-300">
                    <div class="flex flex-col sm:flex-row items-center gap-4 flex-1">
                        <!-- Search Box -->
                        <div class="relative w-full lg:max-w-md">
                            <TextInput
                                v-model="searchFilter"
                                type="text"
                                :placeholder="__('Search tasks...')"
                                class="!pl-11 w-full bg-slate-50 dark:bg-slate-900 border-transparent focus:bg-white dark:focus:bg-slate-800 transition-all rounded-xl"
                                @input="handleFilter"
                            />
                            <Search class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Selects Row -->
                        <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 w-full lg:w-auto">
                            <SelectInput
                                v-model="statusFilter"
                                class="w-full sm:w-40 bg-slate-50 dark:bg-slate-900 border-transparent focus:bg-white dark:focus:bg-slate-800 transition-all rounded-xl"
                                @change="handleFilter"
                            >
                                <option value="all">{{ __('All Status') }}</option>
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="completed">{{ __('Completed') }}</option>
                                <option value="cancelled">{{ __('Cancelled') }}</option>
                            </SelectInput>

                            <div class="w-full sm:w-56">
                                <SearchableSelect
                                    v-if="users.length > 0"
                                    v-model="userFilter"
                                    :options="userOptions"
                                    :placeholder="__('All Users')"
                                    trigger-class="bg-slate-50 dark:bg-slate-900 border-transparent focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white"
                                    @update:modelValue="handleFilter"
                                />
                            </div>

                            <div class="w-full sm:w-56">
                                <SearchableSelect
                                    v-model="dealFilter"
                                    :options="dealOptions"
                                    :placeholder="__('All Deals')"
                                    trigger-class="bg-slate-50 dark:bg-slate-900 border-transparent focus:bg-white dark:focus:bg-slate-800 text-slate-900 dark:text-white"
                                    @update:modelValue="handleFilter"
                                />
                            </div>

                            <SecondaryButton 
                                v-if="searchFilter || statusFilter !== 'pending' || userFilter || dealFilter"
                                @click="clearFilters"
                                class="whitespace-nowrap py-3 px-4 rounded-xl border-dashed border-2 hover:border-brand-500 hover:text-brand-600"
                                :title="__('Clear Filters')"
                            >
                                <X class="w-4 h-4 sm:mr-2" />
                                <span class="hidden sm:inline">{{ __('Clear') }}</span>
                            </SecondaryButton>
                        </div>
                    </div>
                </div>

                <!-- Tasks List -->
                 <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-xl">
                    <div v-if="tasks.data.length > 0" class="divide-y divide-slate-100 dark:divide-slate-700">
                        <div v-for="task in tasks.data" :key="task.id" class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <TaskItem
                                :task="task"
                                :users="users"
                                @show-details="openDetails"
                                @edit="editTask"
                                @delete="requestDeleteTask"
                            />
                        </div>
                    </div>
                    <div v-else class="p-12 text-center text-slate-400 flex flex-col items-center">
                        <CheckSquare class="w-12 h-12 mb-4 opacity-20" />
                        <p>{{ __('No tasks found') }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <Pagination :links="tasks.links" :meta="{ from: tasks.from, to: tasks.to, total: tasks.total }" />
                </div>
            </div>
        </div>

        <TaskCreateModal 
            :show="showCreateModal" 
            :users="users"
            :deals="deals"
            :task="selectedTask"
            @close="showCreateModal = false; selectedTask = null" 
        />

        <TaskDetailsModal
            :show="showDetailsModal"
            :task="selectedTask"
            @close="showDetailsModal = false"
        />

        <ConfirmationModal
            :show="showDeleteModal"
            :title="__('Delete Task?')"
            :message="__('Are you sure you want to delete this task? This action cannot be undone.')"
            :confirmText="__('Yes, Delete Task')"
            :cancelText="__('No, Cancel')"
            type="danger"
            :processing="deleteProcessing"
            @close="showDeleteModal = false"
            @confirm="confirmDeleteTask"
        />
    </AdminLayout>
</template>
