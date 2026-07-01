<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Bell, Calendar, Clock, AlertCircle, CheckCircle2, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    reminders: {
        data: Array<{
            id: number;
            remindable_type: string;
            remindable_id: number;
            remind_at: string;
            type: string;
            priority: string;
            note: string;
            status: string;
            assigned_to: {
                id: number;
                name: string;
            };
            remindable: {
                id: number;
                title?: string;
                name?: string;
                taskable?: {
                    id: number;
                    title?: string;
                };
            };
        }>;
        links: any[];
        current_page: number;
        from: number;
        to: number;
        total: number;
        per_page: number;
        last_page: number;
    };
    summary: {
        today: number;
        overdue: number;
        upcoming: number;
    };
    filters: {
        status?: string;
        type?: string;
        priority?: string;
        search?: string;
        category?: string;
    };
}>();

const currentCategory = computed(() => props.filters.category || 'all');

const categories = [
    { label: 'All', value: 'all' },
    { label: 'Tasks', value: 'tasks' },
    { label: 'Deals', value: 'deals' },
    { label: 'Others', value: 'others' },
];

function getCategoryLink(value: string) {
    const params = new URLSearchParams(window.location.search);
    if (value === 'all') {
        params.delete('category');
    } else {
        params.set('category', value);
    }
    return `${window.location.pathname}?${params.toString()}`;
}

const priorityColors = {
    urgent: 'bg-rose-100 text-rose-700 border-rose-300',
    high: 'bg-orange-100 text-orange-700 border-orange-300',
    medium: 'bg-yellow-100 text-yellow-700 border-yellow-300',
    low: 'bg-gray-100 text-gray-700 border-gray-300',
};

const statusColors = {
    pending: 'bg-blue-100 text-blue-700',
    sent: 'bg-green-100 text-green-700',
    dismissed: 'bg-gray-100 text-gray-700',
    snoozed: 'bg-purple-100 text-purple-700',
};

const typeIcons = {
    follow_up_call: '📞',
    meeting: '📅',
    proposal_submission: '📝',
    closing_date: '🎯',
    task_due: '✅',
    customer_follow_up: '👤',
    custom: '🔔',
};

function formatDate(dateString: string) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = date.getTime() - now.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (days < 0) return `${Math.abs(days)} days overdue`;
    if (days === 0) return 'Today';
    if (days === 1) return 'Tomorrow';
    return `In ${days} days`;
}
</script>

<template>
    <Head title="Reminders" />
    
    <AdminLayout>
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reminders</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage your reminders and notifications</p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-rose-50 to-rose-100 dark:from-rose-900/20 dark:to-rose-800/20 rounded-xl p-6 border border-rose-200 dark:border-rose-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-rose-600 dark:text-rose-400">Overdue</p>
                            <p class="text-3xl font-bold text-rose-700 dark:text-rose-300 mt-1">{{ summary.overdue }}</p>
                        </div>
                        <AlertCircle :size="40" class="text-rose-500 dark:text-rose-400" />
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-6 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Today</p>
                            <p class="text-3xl font-bold text-blue-700 dark:text-blue-300 mt-1">{{ summary.today }}</p>
                        </div>
                        <Calendar :size="40" class="text-blue-500 dark:text-blue-400" />
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-6 border border-green-200 dark:border-green-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">Upcoming</p>
                            <p class="text-3xl font-bold text-green-700 dark:text-green-300 mt-1">{{ summary.upcoming }}</p>
                        </div>
                        <Clock :size="40" class="text-green-500 dark:text-green-400" />
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex items-center space-x-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl w-fit">
                <Link
                    v-for="cat in categories"
                    :key="cat.value"
                    :href="getCategoryLink(cat.value)"
                    class="px-5 py-2 rounded-lg text-sm font-bold transition-all"
                    :class="currentCategory === cat.value 
                        ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-sm' 
                        : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                >
                    {{ cat.label }}
                </Link>
            </div>

            <!-- Reminders List -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">All Reminders</h2>
                    
                    <div v-if="reminders.data.length === 0" class="text-center py-12">
                        <Bell :size="48" class="mx-auto text-gray-400 mb-4" />
                        <p class="text-gray-500 dark:text-gray-400">No reminders found in this category</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="reminder in reminders.data"
                            :key="reminder.id"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-2xl">{{ typeIcons[reminder.type] || '🔔' }}</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ reminder.type.replace(/_/g, ' ').toUpperCase() }}</span>
                                        <span 
                                            :class="priorityColors[reminder.priority]"
                                            class="text-xs px-2 py-1 rounded-full font-medium border"
                                        >
                                            {{ reminder.priority.toUpperCase() }}
                                        </span>
                                        <span 
                                            :class="statusColors[reminder.status]"
                                            class="text-xs px-2 py-1 rounded-full font-medium"
                                        >
                                            {{ reminder.status.toUpperCase() }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">{{ reminder.note }}</p>
                                    
                                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="flex items-center gap-1 font-semibold" :class="new Date(reminder.remind_at) < new Date() ? 'text-rose-500' : ''">
                                            <Calendar :size="14" />
                                            {{ formatDate(reminder.remind_at) }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-70"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            {{ reminder.assigned_to?.name || 'Unassigned' }}
                                        </span>
                                        <div v-if="reminder.remindable" class="flex items-center gap-1">
                                            <span class="opacity-70">Related:</span>
                                            <span class="font-semibold text-gray-700 dark:text-gray-200">
                                                {{ reminder.remindable.title || reminder.remindable.name }}
                                            </span>
                                            <!-- Related Deal for Task -->
                                            <span v-if="reminder.remindable.taskable" class="ml-2 px-2 py-0.5 bg-sky-50 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400 rounded border border-sky-100 dark:border-sky-800 text-[10px] font-bold">
                                                DEAL: {{ reminder.remindable.taskable.title }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="reminders.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Showing {{ reminders.from }} to {{ reminders.to }} of {{ reminders.total }} reminders
                        </p>
                        <div class="flex gap-2">
                        <div class="flex gap-2">
                            <template v-for="link in reminders.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-1 rounded text-sm',
                                        link.active 
                                            ? 'bg-blue-600 text-white' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                    ]"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="px-3 py-1 rounded text-sm bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
