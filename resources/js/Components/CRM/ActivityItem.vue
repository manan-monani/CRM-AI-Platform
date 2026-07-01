<script setup lang="ts">
import { PropType } from 'vue';
import { Phone, Mail, Users, FileText, Clock, CheckCircle, User, MoreHorizontal, Pencil, Trash2 } from 'lucide-vue-next';
import { wTrans as __ } from '@/Core/i18n';

const props = defineProps({
    activity: {
        type: Object as PropType<any>,
        required: true,
    },
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleString();
};

const getIcon = (type: string) => {
    switch(type) {
        case 'call': return Phone;
        case 'email': return Mail;
        case 'meeting': return Users;
        case 'note': return FileText;
        default: return Clock;
    }
};

const getColors = (type: string) => {
     switch(type) {
        case 'call': return 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400';
        case 'email': return 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400';
        case 'meeting': return 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400';
        case 'note': return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400';
        default: return 'bg-gray-100 text-gray-600';
    }
};
</script>

<template>
    <div class="relative pl-8 pb-8 last:pb-0 group">
        <!-- Timeline Line -->
        <div class="absolute left-3 top-8 bottom-0 w-px bg-slate-200 dark:bg-slate-700 last:hidden"></div>
        
        <!-- Icon -->
        <div class="absolute left-0 top-0 w-6 h-6 rounded-full flex items-center justify-center z-10" :class="getColors(activity.type)">
             <component :is="getIcon(activity.type)" :size="12" />
        </div>

        <div class="bg-white dark:bg-slate-800/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700/50 shadow-sm relative group-hover:border-slate-200 dark:group-hover:border-slate-600 transition-colors">
            <!-- Action Buttons (Visible on Hover) -->
            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex items-center gap-1 bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700 rounded-lg p-0.5">
                <button 
                    @click.stop="$emit('edit', activity)"
                    class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors"
                    title="Edit Activity"
                >
                    <Pencil :size="14" />
                </button>
                <button 
                    @click.stop="$emit('delete', activity)"
                    class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                    title="Delete Activity"
                >
                    <Trash2 :size="14" />
                </button>
            </div>

            <div class="flex justify-between items-start mb-1 pr-8">
                <h4 class="font-bold text-sm text-slate-900 dark:text-white">{{ activity.subject }}</h4>
                <span class="text-[10px] text-slate-400">{{ formatDate(activity.created_at) }}</span>
            </div>
            <p v-if="activity.description" class="text-xs text-slate-600 dark:text-slate-400 whitespace-pre-wrap">{{ activity.description }}</p>
            
            <div class="flex items-center gap-3 mt-3 text-[10px] text-slate-500">
                <span v-if="activity.user" class="flex items-center gap-1">
                    <User :size="10" /> {{ activity.user.name }}
                </span>
                <span v-if="activity.duration_minutes" class="flex items-center gap-1">
                    <Clock :size="10" /> {{ activity.duration_minutes }}m
                </span>
                <span v-if="activity.outcome" class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 capitalize">
                    {{ activity.outcome }}
                </span>
            </div>
        </div>
    </div>
</template>
