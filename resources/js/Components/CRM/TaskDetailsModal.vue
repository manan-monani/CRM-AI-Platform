<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Modal from '@/Components/Common/Modal.vue';
import SecondaryButton from '@/Components/Core/SecondaryButton.vue';
import { wTrans as __ } from '@/Core/i18n';
import admin from '@/routes/admin';
import { Clock, CheckCircle, AlertCircle, XCircle, User, Calendar } from 'lucide-vue-next';



const props = defineProps({
    show: Boolean,
    task: {
        type: Object,
        required: true,
    },
});

defineEmits(['close']);

const getPriorityColor = (priority) => {
    switch (priority) {
        case 'high': return 'text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400';
        case 'medium': return 'text-amber-600 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400';
        case 'low': return 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 dark:text-emerald-400';
        default: return 'text-slate-600 bg-slate-50 dark:bg-slate-700 dark:text-slate-400';
    }
};

const getStatusIcon = (status) => {
    switch (status) {
        case 'completed': return CheckCircle;
        case 'in_progress': return Clock;
        case 'cancelled': return XCircle;
        default: return AlertCircle;
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'completed': return 'text-emerald-500';
        case 'in_progress': return 'text-blue-500';
        case 'cancelled': return 'text-red-500';
        default: return 'text-amber-500';
    }
};
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ task?.title }}
                    </h2>
                    <div v-if="task?.taskable" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Related to') }}: 
                        <Link 
                            v-if="task.taskable_type === 'App\\Models\\Deal'"
                            :href="admin.deals.show.url(task.taskable_id)"
                            class="font-medium text-brand-600 dark:text-brand-400 hover:underline"
                        >
                            {{ task.taskable.name || task.taskable.title }}
                        </Link>
                        <span v-else class="font-medium text-gray-700 dark:text-gray-300">
                            {{ task.taskable.name || task.taskable.title }}
                        </span>
                    </div>
                </div>
                <div 
                    class="px-3 py-1 rounded-full text-xs font-medium capitalize"
                    :class="getPriorityColor(task?.priority)"
                >
                    {{ task?.priority || 'Medium' }}
                </div>
            </div>

            <div class="space-y-6">
                <!-- Status & Due Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center space-x-2 text-sm">
                        <component 
                            :is="getStatusIcon(task?.status)" 
                            class="w-5 h-5"
                            :class="getStatusColor(task?.status)"
                        />
                        <span class="capitalize text-gray-700 dark:text-gray-300">
                            {{ task?.status?.replace('_', ' ') }}
                        </span>
                    </div>
                    
                    <div v-if="task?.due_date" class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                        <Calendar class="w-4 h-4" />
                        <span>Due: {{ task.due_date }}</span>
                    </div>
                </div>

                <!-- Assignment -->
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                            <User class="w-4 h-4" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Assigned To') }}</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ task?.assigned_to_user?.name || __('Unassigned') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div v-if="task?.description">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('Description') }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">
                        {{ task.description }}
                    </p>
                </div>


            </div>

            <div class="mt-8 flex justify-end">
                <SecondaryButton @click="$emit('close')">
                    {{ __('Close') }}
                </SecondaryButton>
            </div>
        </div>
    </Modal>
</template>
