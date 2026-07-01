<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    Search,
    Filter,
    Plus,
    FileText,
    Calendar,
    Clock,
    User,
    ArrowRight,
    Trash2,
    Activity,
    CheckCircle2,
    XCircle,
    AlertCircle,
    ChevronDown,
    X
} from "lucide-vue-next";
import Pagination from "@/Components/Core/Pagination.vue";
import Modal from "@/Components/Common/Modal.vue";
import ConfirmationModal from "@/Components/Common/ConfirmationModal.vue";
import SearchableSelect from "@/Components/Core/SearchableSelect.vue";
import { debounce } from "lodash";

import admin from '@/routes/admin';

const props = defineProps<{
    activities: {
        data: any[];
        links: any[];
        meta: any;
    };
    filters: {
        search?: string;
        type?: string;
        user_id?: string;
    };
    users: any[];
    types: string[];
}>();

const userOptions = computed(() => {
    return props.users.map(user => ({
        value: user.id,
        label: user.name,
        image: user.profile_photo_url || `https://ui-avatars.com/api/?name=${user.name}&background=random`
    }));
});

const search = ref(props.filters.search || "");
const typeFilter = ref(props.filters.type || "");
const userFilter = ref(props.filters.user_id || "");

const resetFilters = () => {
    search.value = "";
    typeFilter.value = "";
    userFilter.value = "";
    
    router.get(
        admin.activities.index.url(),
        { page: 1 },
        { preserveState: true, preserveScroll: true }
    );
};

// Debounced search
const updateSearch = debounce((value) => {
    router.get(
        admin.activities.index.url(),
        { search: value, type: typeFilter.value, user_id: userFilter.value, page: 1 },
        { preserveState: true, preserveScroll: true }
    );
}, 300);

// Filter watchers
watch([typeFilter, userFilter], () => {
    router.get(
        admin.activities.index.url(),
        { search: search.value, type: typeFilter.value, user_id: userFilter.value, page: 1 },
        { preserveState: true, preserveScroll: true }
    );
});

const getTypeIcon = (type: string) => {
    switch (type) {
        case 'call': return FileText; // Placeholder, adjust as needed
        case 'meeting': return Calendar;
        case 'email': return FileText;
        case 'task': return CheckCircle2;
        default: return Activity;
    }
};

const getTypeColor = (type: string) => {
    switch (type) {
        case 'call': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        case 'meeting': return 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400';
        case 'email': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
        default: return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400';
    }
};

const form = useForm({});
const showDeleteModal = ref(false);
const activityToDeleteId = ref<number | null>(null);

const deleteActivity = (id: number) => {
    activityToDeleteId.value = id;
    showDeleteModal.value = true;
};

const handleDeleteActivity = () => {
    if (!activityToDeleteId.value) {
        return;
    }

    form.delete(admin.activities.destroy.url(activityToDeleteId.value), {
        onSuccess: () => {
            showDeleteModal.value = false;
            activityToDeleteId.value = null;
        },
    });
};
</script>

<template>
    <Head title="Activity Management" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                        Activity Management
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-1">
                        Track and manage all system activities
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="relative">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="20" />
                        <input
                            v-model="search"
                            @input="updateSearch(($event.target as HTMLInputElement).value)"
                            type="text"
                            placeholder="Search activities..."
                            class="w-full h-14 pl-12 pr-6 text-[15px] font-semibold bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all shadow-sm shadow-slate-100/50 dark:shadow-none placeholder:font-normal placeholder:text-slate-400"
                        />
                    </div>

                    <!-- Type Filter -->
                    <div class="relative">
                        <Filter class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="20" />
                        <select
                            v-model="typeFilter"
                            class="w-full h-14 pl-12 pr-10 text-[15px] font-semibold bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all shadow-sm shadow-slate-100/50 dark:shadow-none appearance-none cursor-pointer"
                        >
                            <option value="">All Types</option>
                            <option v-for="type in types" :key="type" :value="type">
                                {{ type.charAt(0).toUpperCase() + type.slice(1) }}
                            </option>
                        </select>
                        <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" :size="16" />
                    </div>

                    <!-- User Filter -->
                    <div class="relative" v-if="users.length > 0">
                        <SearchableSelect
                            v-model="userFilter"
                            :options="userOptions"
                            placeholder="All Users"
                            class="w-full"
                        />
                    </div>

                    <!-- Reset Filter -->
                    <div v-if="search || typeFilter || userFilter" class="flex items-center">
                        <button
                            @click="resetFilters"
                            class="h-14 w-14 flex items-center justify-center bg-white dark:bg-slate-900 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl text-slate-500 hover:text-red-500 hover:border-red-200 hover:bg-red-50 dark:hover:bg-red-900/20 dark:hover:border-red-800 transition-all shadow-sm shadow-slate-100/50 dark:shadow-none"
                            title="Reset Filters"
                        >
                            <X :size="20" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Activity</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Related To</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr v-if="activities.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-3">
                                            <Activity class="w-6 h-6 text-slate-400" />
                                        </div>
                                        <p class="font-medium">No activities found</p>
                                        <p class="text-sm mt-1">Try adjusting your search or filters</p>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="activity in activities.data"
                                :key="activity.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3">
                                        <div :class="['w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0', getTypeColor(activity.type)]">
                                            <component :is="getTypeIcon(activity.type)" :size="16" />
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900 dark:text-white">
                                                {{ activity.subject }}
                                            </div>
                                            <div class="text-sm text-slate-500 dark:text-slate-400 line-clamp-1">
                                                {{ activity.description || 'No description' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="activity.activityable" class="flex items-center gap-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                            {{ activity.activityable_type.split('\\').pop() }}
                                        </span>
                                        <span class="text-sm text-slate-700 dark:text-slate-300">
                                            {{ activity.activityable.title || activity.activityable.name || '#' + activity.activityable.id }}
                                        </span>
                                    </div>
                                    <span v-else class="text-slate-400 text-sm">-</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2" v-if="activity.performed_by_user">
                                        <img
                                            :src="activity.performed_by_user.profile_photo_url || `https://ui-avatars.com/api/?name=${activity.performed_by_user.name}&background=random`"
                                            :alt="activity.performed_by_user.name"
                                            class="w-6 h-6 rounded-full"
                                        />
                                        <span class="text-sm text-slate-700 dark:text-slate-300">
                                            {{ activity.performed_by_user.name }}
                                        </span>
                                    </div>
                                    <span v-else class="text-slate-400 text-sm">System</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ new Date(activity.created_at).toLocaleDateString() }}
                                    <span class="text-xs text-slate-400 ml-1">
                                        {{ new Date(activity.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        @click="deleteActivity(activity.id)"
                                        class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors opacity-0 group-hover:opacity-100"
                                        title="Delete Activity"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="border-t border-slate-200 dark:border-slate-700 p-4">
                    <Pagination :links="activities.links" />
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="showDeleteModal"
            :title="__('Delete Activity?')"
            :message="__('Are you sure you want to delete this activity? This action cannot be undone.')"
            :confirmText="__('Yes, Delete Activity')"
            :cancelText="__('No, Cancel')"
            type="danger"
            :processing="form.processing"
            @close="showDeleteModal = false"
            @confirm="handleDeleteActivity"
        />
    </AdminLayout>
</template>
