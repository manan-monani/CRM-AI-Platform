<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Bell, 
    Search, 
    ChevronRight, 
    ChevronLeft,
    Check,
    CheckCheck,
    Trash2,
    Info,
    CheckCircle,
    AlertTriangle,
    AlertCircle,
    Activity
} from 'lucide-vue-next';
import { trans as __ } from '@/Core/i18n';
import admin from '@/routes/admin';
import axios from 'axios';
import ConfirmationModal from '@/Components/Common/ConfirmationModal.vue';

const props = defineProps<{
    notifications: {
        data: any[];
        links: any[];
        total: number;
        from: number;
        to: number;
    };
    unreadCount: number;
}>();

const isLoading = ref(false);
const showDeleteModal = ref(false);
const notificationToDeleteId = ref<number | null>(null);

// Mark as read
const markAsRead = async (id: number) => {
    try {
        await axios.post(admin.notifications.read.url({ id }));
        router.reload({ preserveScroll: true });
        window.dispatchEvent(new Event('notifications-updated'));
    } catch (error) {
        console.error('Failed to mark notification as read:', error);
    }
};

// Mark all as read
const markAllAsRead = async () => {
    try {
        await axios.post(admin.notifications.read_all.url());
        router.reload({ preserveScroll: true });
        window.dispatchEvent(new Event('notifications-updated'));
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    }
};

// Delete notification
const deleteNotification = (id: number) => {
    notificationToDeleteId.value = id;
    showDeleteModal.value = true;
};

const handleDeleteNotification = async () => {
    if (!notificationToDeleteId.value) {
        return;
    }

    try {
        await axios.delete(admin.notifications.destroy.url({ id: notificationToDeleteId.value }));
        router.reload({ preserveScroll: true });
        window.dispatchEvent(new Event('notifications-updated'));
        showDeleteModal.value = false;
        notificationToDeleteId.value = null;
    } catch (error) {
        console.error('Failed to delete notification:', error);
    }
};

// Get icon for notification type
const getTypeIcon = (type: string) => {
    switch (type) {
        case 'success': return CheckCircle;
        case 'warning': return AlertTriangle;
        case 'error': return AlertCircle;
        case 'reminder': return Activity;
        default: return Info;
    }
};

// Get color classes for notification type
const getTypeClasses = (type: string) => {
    switch (type) {
        case 'success': return 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20';
        case 'warning': return 'text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20';
        case 'error': return 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20';
        case 'reminder': return 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20';
        default: return 'text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/20';
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString();
};

const getRelativeTime = (dateString: string) => {
    const now = new Date();
    const past = new Date(dateString);
    const seconds = Math.floor((now.getTime() - past.getTime()) / 1000);
    
    if (seconds < 60) return __('Just now');
    if (seconds < 3600) return `${Math.floor(seconds / 60)}${__('m ago')}`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}${__('h ago')}`;
    if (seconds < 604800) return `${Math.floor(seconds / 86400)}${__('d ago')}`;
    return past.toLocaleDateString();
};
</script>

<template>
    <Head :title="__('Notifications')" />

    <AdminLayout>
        <div class="space-y-6 animate-fade-in max-w-7xl mx-auto w-full p-4 lg:p-0">
            <!-- Header section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('Notifications') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Stay updated with your latest alerts and reminders') }}</p>
                </div>
                <button 
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="bg-slate-900 dark:bg-white dark:text-slate-900 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-xl hover:opacity-90 transition-all flex items-center gap-2"
                >
                    <CheckCheck :size="16" />
                    {{ __('Mark All as Read') }}
                </button>
            </div>

            <!-- Notifications List -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div v-if="notifications.data.length === 0" class="flex flex-col items-center justify-center py-24 px-4 text-center">
                    <div class="w-20 h-20 rounded-xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-300 dark:text-slate-600 mb-6">
                        <Bell :size="40" />
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ __('All clear!') }}</h3>
                    <p class="text-slate-500 dark:text-slate-400 max-w-xs mt-2">{{ __('You don\'t have any notifications at the moment.') }}</p>
                </div>

                <div v-else class="divide-y divide-slate-100 dark:divide-slate-800">
                    <div 
                        v-for="notification in notifications.data" 
                        :key="notification.id"
                        class="group p-6 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all cursor-default"
                        :class="{ 'bg-blue-50/30 dark:bg-blue-900/5': !notification.read_at }"
                    >
                        <div class="flex gap-6">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center relative shadow-sm" :class="getTypeClasses(notification.type)">
                                    <component :is="getTypeIcon(notification.type)" :size="24" />
                                    <!-- Unread Dot -->
                                    <span v-if="!notification.read_at" class="absolute -top-1 -right-1 w-4 h-4 bg-blue-500 border-4 border-white dark:border-slate-900 rounded-full"></span>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-3 flex-wrap">
                                            <h4 class="text-base font-black text-slate-900 dark:text-white leading-tight">
                                                {{ notification.title }}
                                            </h4>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-lg">
                                                {{ notification.type }}
                                            </span>
                                        </div>
                                        <p v-if="notification.description" class="text-sm text-slate-600 dark:text-slate-300 font-medium">
                                            {{ notification.description }}
                                        </p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            v-if="!notification.read_at"
                                            @click="markAsRead(notification.id)"
                                            class="w-9 h-9 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-blue-500 hover:border-blue-500/30 transition-all flex items-center justify-center shadow-sm"
                                            :title="__('Mark as read')"
                                        >
                                            <Check :size="18" />
                                        </button>
                                        <button
                                            @click="deleteNotification(notification.id)"
                                            class="w-9 h-9 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-rose-500 hover:border-rose-500/30 transition-all flex items-center justify-center shadow-sm"
                                            :title="__('Delete')"
                                        >
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between mt-4">
                                    <div class="flex items-center gap-4">
                                        <span class="text-xs font-bold text-slate-400 flex items-center gap-1">
                                            <Bell :size="12" />
                                            {{ getRelativeTime(notification.created_at) }}
                                        </span>
                                        <span class="text-[10px] font-bold text-slate-300 dark:text-slate-600">
                                            {{ formatDate(notification.created_at) }}
                                        </span>
                                    </div>
                                    
                                    <Link
                                        v-if="notification.action_url"
                                        :href="notification.action_url"
                                        class="inline-flex items-center gap-2 text-sm font-black text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-all group/link"
                                    >
                                        {{ __('View Details') }}
                                        <ChevronRight :size="16" class="transition-transform group-hover/link:translate-x-1 rtl:group-hover/link:-translate-x-1" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination Section -->
                <div v-if="notifications.total > 0" class="p-6 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-6">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        {{ __('Showing') }} {{ notifications.from || 0 }}-{{ notifications.to || 0 }} {{ __('of') }} {{ notifications.total }} {{ __('notifications') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <template v-for="(link, index) in notifications.links" :key="index">
                            <Link 
                                v-if="link.url"
                                :href="link.url"
                                class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all shadow-sm"
                                :class="link.active 
                                    ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xl' 
                                    : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'"
                            >
                                <ChevronLeft v-if="link.label.includes('Previous')" :size="16" />
                                <ChevronRight v-if="link.label.includes('Next')" :size="16" />
                                <span v-if="!link.label.includes('Previous') && !link.label.includes('Next')" v-html="link.label"></span>
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="showDeleteModal"
            :title="__('Delete Notification?')"
            :message="__('Are you sure you want to delete this notification? This action cannot be undone.')"
            :confirmText="__('Yes, Delete Notification')"
            :cancelText="__('No, Cancel')"
            type="danger"
            @close="showDeleteModal = false"
            @confirm="handleDeleteNotification"
        />
    </AdminLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
