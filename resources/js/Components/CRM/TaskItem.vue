<script setup lang="ts">
import { ref, PropType } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Calendar, AlertCircle, CheckCircle, Clock, MoreHorizontal, Pencil, Trash2 } from 'lucide-vue-next';
import { wTrans as __ } from '@/Core/i18n';
import admin from '@/routes/admin';
import TaskStatusModal from '@/Components/CRM/TaskStatusModal.vue';

const props = defineProps({
    task: {
        type: Object as PropType<any>,
        required: true,
    },
    users: {
        type: Array as PropType<any[]>,
        default: () => [],
    },
});

const showStatusModal = ref(false);

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};

const isOverdue = (date: string) => {
    return new Date(date) < new Date() && props.task.status !== 'completed';
};

const openStatusModal = () => {
    showStatusModal.value = true;
};

const getPriorityColor = (priority: string) => {
    switch(priority) {
        case 'urgent': return 'text-red-600 bg-red-50 dark:bg-red-900/20';
        case 'high': return 'text-orange-600 bg-orange-50 dark:bg-orange-900/20';
        case 'medium': return 'text-blue-600 bg-blue-50 dark:bg-blue-900/20';
        default: return 'text-slate-600 bg-slate-50 dark:bg-slate-900/20';
    }
};

const getStatusColor = (status: string) => {
    switch(status) {
        case 'completed': return 'bg-emerald-500 border-emerald-500 text-white';
        case 'in_progress': return 'border-blue-500 text-blue-500 bg-blue-50 dark:bg-blue-900/20';
        case 'cancelled': return 'border-slate-300 text-slate-300 bg-slate-100 dark:bg-slate-800';
        default: return 'border-slate-300 dark:border-slate-600 hover:border-emerald-500';
    }
};
const getCardStyle = () => {
    if (props.task.status === 'completed') {
        return 'bg-emerald-50/50 border-emerald-100 dark:bg-emerald-900/10 dark:border-emerald-800 opacity-75';
    }
    
    if (props.task.status === 'cancelled') {
        return 'bg-slate-50 border-slate-200 dark:bg-slate-800/50 dark:border-slate-700 opacity-60 grayscale';
    }

    const dueDate = new Date(props.task.due_date);
    const today = new Date();
    const nextWeek = new Date();
    nextWeek.setDate(today.getDate() + 7);

    // Reset hours for accurate date comparison
    today.setHours(0, 0, 0, 0);
    dueDate.setHours(0, 0, 0, 0);

    if (dueDate < today) {
        return 'bg-red-50 border-red-100 dark:bg-red-900/20 dark:border-red-800';
    }

    if (dueDate <= nextWeek && dueDate >= today) {
        return 'bg-amber-50 border-amber-100 dark:bg-amber-900/20 dark:border-amber-800';
    }

    return 'hover:bg-slate-50 dark:hover:bg-slate-800/50 border-transparent hover:border-slate-100 dark:hover:border-slate-700';
};
</script>

<template>
    <div 
        class="group flex items-start gap-3 p-3 rounded-xl transition-colors border relative"
        :class="getCardStyle()"
    >
        <!-- Action Buttons (Visible on Hover) -->
        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex items-center gap-1 bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700 rounded-lg p-0.5">
            <button 
                @click.stop="$emit('edit', task)"
                class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors"
                title="Edit Task"
            >
                <Pencil :size="14" />
            </button>
            <button 
                @click.stop="$emit('delete', task)"
                class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                title="Delete Task"
            >
                <Trash2 :size="14" />
            </button>
        </div>
        <button 
            type="button"
            @click.stop="openStatusModal"
            class="mt-1 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0 cursor-pointer hover:scale-110"
            :class="getStatusColor(task.status)"
        >
            <CheckCircle v-if="task.status === 'completed'" :size="12" />
            <div v-else-if="task.status === 'in_progress'" class="w-2 h-2 rounded-full bg-blue-500"></div>
            <!-- Pending: no icon, just border -->
        </button>

        <Link :href="admin.tasks.show.url(task.id)" class="flex-1 min-w-0 cursor-pointer">
            <div class="flex justify-between items-start">
                <span 
                    class="text-sm font-medium truncate"
                    :class="task.status === 'completed' || task.status === 'cancelled' ? 'text-slate-400 line-through' : 'text-slate-900 dark:text-white'"
                >
                    {{ task.title }}
                </span>
                <span class="text-[10px] px-1.5 py-0.5 rounded capitalize font-bold" :class="getPriorityColor(task.priority)">
                    {{ task.priority }}
                </span>
            </div>
            
            <p v-if="task.description" class="text-xs text-slate-500 mt-1 line-clamp-1 group-hover:line-clamp-none transition-all">{{ task.description }}</p>
            
            <div class="flex items-center gap-3 mt-2 text-[10px] text-slate-400">
                <span class="flex items-center gap-1" :class="{ 'text-red-500 font-bold': isOverdue(task.due_date) }">
                    <Calendar :size="10" /> {{ formatDate(task.due_date) }}
                </span>
                <span v-if="task.assigned_to_user" class="flex items-center gap-1">
                    <img :src="task.assigned_to_user.profile_photo_url || `https://ui-avatars.com/api/?name=${task.assigned_to_user.name}&background=random`" class="w-3 h-3 rounded-full object-cover" alt="">
                    {{ task.assigned_to_user.name }}
                </span>
                <span class="capitalize">{{ task.status.replace('_', ' ') }}</span>
            </div>

            <div v-if="task.taskable" class="mt-2 text-[10px] text-slate-400 font-medium">
                {{ __('Related to') }}: <span class="text-slate-600 dark:text-slate-300">{{ task.taskable.name || task.taskable.title }}</span>
            </div>
        </Link>

        <TaskStatusModal 
            :show="showStatusModal" 
            :task="task" 
            :users="users"
            @close="showStatusModal = false" 
        />
    </div>
</template>
