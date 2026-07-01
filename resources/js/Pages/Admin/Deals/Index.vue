<script setup lang="ts">
import { ref, computed, PropType } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, LayoutGrid, List, Search, Filter, Eye, Pencil, X } from 'lucide-vue-next';
import DealCard from '@/Components/CRM/DealCard.vue';
import Pagination from '@/Components/Core/Pagination.vue';
import TextInput from '@/Components/Core/TextInput.vue';
import SelectInput from '@/Components/Core/SelectInput.vue';
import PrimaryButton from '@/Components/Core/PrimaryButton.vue';
import SecondaryButton from '@/Components/Core/SecondaryButton.vue';
import { wTrans as __ } from '@/Core/i18n';
import { formatMoney as money } from '@/Core/money';
import admin from '@/routes/admin';

const props = defineProps({
    deals: Object as PropType<any>,
    users: Array as PropType<any[]>,
    filters: Object as PropType<any>,
});

const draggingOverStage = ref<string | null>(null);
const isDraggingCard = ref(false);

const handleDragStart = (e: DragEvent) => {
    isDraggingCard.value = true;
    isPanning.value = false; // Stop panning if a drag starts
};

const handleDragEnd = () => {
    isDraggingCard.value = false;
    stopAutoScroll();
};

const handleDragOver = (e: DragEvent, stage: string) => {
    e.preventDefault();
    if (e.dataTransfer) {
        e.dataTransfer.dropEffect = 'move';
    }
    draggingOverStage.value = stage;
};

const handleDragLeave = () => {
    draggingOverStage.value = null;
    stopAutoScroll();
};

const scrollSpeed = ref(0);
const scrollAnimationFrame = ref<number | null>(null);

const startAutoScroll = () => {
    if (scrollAnimationFrame.value) return;
    
    const scroll = () => {
        if (boardContainer.value && scrollSpeed.value !== 0) {
            boardContainer.value.scrollLeft += scrollSpeed.value;
            scrollAnimationFrame.value = requestAnimationFrame(scroll);
        } else {
            scrollAnimationFrame.value = null;
        }
    };
    
    scrollAnimationFrame.value = requestAnimationFrame(scroll);
};

const stopAutoScroll = () => {
    if (scrollAnimationFrame.value) {
        cancelAnimationFrame(scrollAnimationFrame.value);
        scrollAnimationFrame.value = null;
    }
    scrollSpeed.value = 0;
};

const handleContainerDragOver = (e: DragEvent) => {
    if (!boardContainer.value) return;
    
    const rect = boardContainer.value.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const threshold = 120; // Slightly larger threshold
    const maxSpeed = 20; // Faster speed
    
    if (x < threshold) {
        // Scroll left - speed increases as cursor gets closer to edge
        const ratio = 1 - (x / threshold);
        scrollSpeed.value = -maxSpeed * Math.pow(ratio, 1.5);
    } else if (x > rect.width - threshold) {
        // Scroll right - speed increases as cursor gets closer to edge
        const ratio = 1 - (rect.width - x) / threshold;
        scrollSpeed.value = maxSpeed * Math.pow(ratio, 1.5);
    } else {
        scrollSpeed.value = 0;
    }
    
    if (scrollSpeed.value !== 0) {
        startAutoScroll();
    } else {
        stopAutoScroll();
    }
};

const handleDrop = (e: DragEvent, targetStage: string) => {
    e.preventDefault();
    draggingOverStage.value = null;
    handleDragEnd();
    
    if (!e.dataTransfer) return;
    
    const dealId = e.dataTransfer.getData('dealId');
    const sourceStage = e.dataTransfer.getData('sourceStage');
    
    if (sourceStage === targetStage) return;
    
    // Use the correctly imported admin helper instead of undefined route()
    router.patch(admin.deals.stage.url(Number(dealId)), {
        stage: targetStage
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Success notification if needed
        }
    });
};

const viewMode = ref<string>(props.filters?.view || 'board'); // board or list
const search = ref(props.filters?.search || '');
const stageFilter = ref(props.filters?.stage || '');
const userFilter = ref(props.filters?.user_id || '');

const stages = ['lead', 'new', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];

const handleSearch = () => {
    router.get(admin.deals.index.url(), {
        search: search.value,
        stage: stageFilter.value,
        user_id: userFilter.value,
        view: viewMode.value,
    }, {
        preserveState: true,
        replace: true
    });
};

const switchView = (mode: string) => {
    viewMode.value = mode;
    handleSearch();
};

const dealsByStage = computed(() => {
    const grouped: { [key: string]: any[] } = {
        lead: [],
        new: [],
        qualified: [],
        proposal: [],
        negotiation: [],
        won: [],
        lost: [],
    };
    
    if (props.deals?.data) {
        props.deals.data.forEach((deal: any) => {
            if (grouped[deal.stage]) {
                grouped[deal.stage].push(deal);
            } else {
                // If stage not in default list, put in new or handle dynamic stages
                 if (!grouped[deal.stage]) grouped[deal.stage] = [];
                 grouped[deal.stage].push(deal);
            }
        });
    }
    
    return grouped;
});

const getStageColor = (stage: string) => {
    switch(stage) {
        case 'lead': return 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300';
        case 'new': return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
        case 'qualified': return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300';
        case 'proposal': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300';
        case 'negotiation': return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
        case 'won': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
        case 'lost': return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
        default: return 'bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-300';
    }
};

const formatMoney = (value: number) => {
    return money(value);
};

// Drag to scroll logic
const boardContainer = ref<HTMLElement | null>(null);
const isPanning = ref(false);
const startX = ref(0);
const scrollLeft = ref(0);

const startDragging = (e: MouseEvent) => {
    if (!boardContainer.value) return;
    isPanning.value = true;
    startX.value = e.pageX - boardContainer.value.offsetLeft;
    scrollLeft.value = boardContainer.value.scrollLeft;
};

const stopDragging = () => {
    isPanning.value = false;
};

const onDrag = (e: MouseEvent) => {
    if (!isPanning.value || !boardContainer.value) return;
    e.preventDefault();
    const x = e.pageX - boardContainer.value.offsetLeft;
    const walk = (x - startX.value) * 2; // Scroll speed multiplier
    boardContainer.value.scrollLeft = scrollLeft.value - walk;
};

import { onMounted, onUnmounted } from 'vue';

onMounted(() => {
    window.addEventListener('mouseup', stopDragging);
});

onUnmounted(() => {
    window.removeEventListener('mouseup', stopDragging);
});

const getRowStyle = (deal: any) => {
    // Status overrides stage colors
    if (deal.status === 'won') return 'bg-emerald-50/30 hover:bg-emerald-50/80 dark:bg-emerald-900/5 dark:hover:bg-emerald-900/20';
    if (deal.status === 'lost') return 'bg-red-50/30 hover:bg-red-50/80 dark:bg-red-900/5 dark:hover:bg-red-900/20 opacity-75';

    switch(deal.stage) {
        case 'lead': return 'bg-purple-50/30 hover:bg-purple-50/80 dark:bg-purple-900/5 dark:hover:bg-purple-900/20';
        case 'new': return 'bg-slate-50/50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700/50';
        case 'qualified': return 'bg-indigo-50/30 hover:bg-indigo-50/80 dark:bg-indigo-900/5 dark:hover:bg-indigo-900/20';
        case 'proposal': return 'bg-blue-50/30 hover:bg-blue-50/80 dark:bg-blue-900/5 dark:hover:bg-blue-900/20';
        case 'negotiation': return 'bg-amber-50/30 hover:bg-amber-50/80 dark:bg-amber-900/5 dark:hover:bg-amber-900/20';
        case 'won': return 'bg-emerald-50/30 hover:bg-emerald-50/80 dark:bg-emerald-900/5 dark:hover:bg-emerald-900/20';
        case 'lost': return 'bg-red-50/30 hover:bg-red-50/80 dark:bg-red-900/5 dark:hover:bg-red-900/20 opacity-75';
        default: return 'bg-white hover:bg-slate-50 dark:bg-gray-800 dark:hover:bg-slate-700/30';
    }
};
</script>

<template>
    <Head :title="__('Deals')" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Sales Pipeline') }}
                </h2>
                <Link :href="admin.deals.create.url()">
                    <PrimaryButton>
                        <Plus class="w-4 h-4 mr-2" />
                        {{ __('Create Deal') }}
                    </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters & Controls -->
                <div class="mb-6 flex flex-col lg:flex-row gap-4 justify-between items-stretch lg:items-center bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div class="flex flex-col sm:flex-row items-center gap-3 flex-1">
                        <div class="relative w-full lg:max-w-md">
                            <TextInput
                                v-model="search"
                                type="text"
                                :placeholder="__('Search deals...')"
                                class="!pl-10 w-full bg-slate-50 dark:bg-slate-900 border-transparent focus:bg-white dark:focus:bg-slate-800 transition-all"
                                @input="handleSearch"
                            />
                            <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                        </div>
                        
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <SelectInput
                                v-model="stageFilter"
                                class="w-full sm:w-44 bg-slate-50 dark:bg-slate-900 border-transparent focus:bg-white dark:focus:bg-slate-800 transition-all"
                                @change="handleSearch"
                            >
                                <option value="">{{ __('All Stages') }}</option>
                                <option v-for="stage in stages" :key="stage" :value="stage" class="capitalize">
                                    {{ stage }}
                                </option>
                            </SelectInput>

                            <SelectInput
                                v-if="(users?.length ?? 0) > 0"
                                v-model="userFilter"
                                class="w-full sm:w-48 bg-slate-50 dark:bg-slate-900 border-transparent focus:bg-white dark:focus:bg-slate-800 transition-all"
                                @change="handleSearch"
                            >
                                <option value="">{{ __('All Users') }}</option>
                                <option v-for="user in users || []" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </SelectInput>

                            <SecondaryButton 
                                v-if="search || stageFilter || userFilter"
                                @click="search = ''; stageFilter = ''; userFilter = ''; handleSearch()"
                                class="whitespace-nowrap py-2.5 px-3"
                                :title="__('Clear Filters')"
                            >
                                <X class="w-4 h-4" />
                            </SecondaryButton>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-4 mt-2 lg:mt-0">
                        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-900 p-1 rounded-lg">
                            <button
                                @click="switchView('board')"
                                class="px-3 py-1.5 rounded-md transition-all flex items-center gap-2 text-xs font-bold"
                                :class="viewMode === 'board' ? 'bg-white dark:bg-slate-800 text-brand-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                            >
                                <LayoutGrid class="w-3.5 h-3.5" />
                                <span>{{ __('Board') }}</span>
                            </button>
                            <button
                                @click="switchView('list')"
                                class="px-3 py-1.5 rounded-md transition-all flex items-center gap-2 text-xs font-bold"
                                :class="viewMode === 'list' ? 'bg-white dark:bg-slate-800 text-brand-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                            >
                                <List class="w-3.5 h-3.5" />
                                <span>{{ __('List') }}</span>
                            </button>
                        </div>
                        
                        <div class="h-8 w-px bg-slate-200 dark:bg-slate-700 hidden sm:block"></div>
                        
                        <Link :href="admin.deals.create.url()" class="sm:hidden w-full">
                            <PrimaryButton class="w-full justify-center">
                                <Plus class="w-4 h-4 mr-2" />
                                {{ __('New') }}
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>

                <!-- Kanban Board View -->
                <div 
                    v-if="viewMode === 'board'" 
                    ref="boardContainer"
                    class="flex gap-6 overflow-x-auto pb-6 hide-scrollbar h-[calc(100vh-15rem)] cursor-grab"
                    :class="{ 
                        'cursor-grabbing select-none': isPanning,
                        'cursor-grabbing': isDraggingCard 
                    }"
                    @mousedown="startDragging"
                    @mouseleave="stopDragging"
                    @mouseup="stopDragging"
                    @mousemove="onDrag"
                    @dragstart="handleDragStart"
                    @dragover="handleContainerDragOver"
                    @dragend="handleDragEnd"
                    @drop="handleDragEnd"
                >
                    <div 
                        v-for="stage in stages" 
                        :key="stage" 
                        class="min-w-[320px] w-80 flex-shrink-0 bg-slate-50 dark:bg-slate-800/50 rounded-xl p-3 flex flex-col h-full sticky top-0 border transition-all duration-200"
                        :class="[
                            draggingOverStage === stage 
                                ? 'border-brand-500 bg-brand-50/30 dark:bg-brand-900/10 ring-2 ring-brand-500/20' 
                                : 'border-slate-200/50 dark:border-slate-700/50'
                        ]"
                        @dragover="handleDragOver($event, stage)"
                        @dragleave="handleDragLeave"
                        @drop="handleDrop($event, stage)"
                    >
                        <div class="flex items-center justify-between mb-4 flex-shrink-0 sticky top-0 bg-slate-50 dark:bg-slate-800/50 z-10 py-1 border-t-4 rounded-t-sm" :class="getStageColor(stage).replace('bg-', 'border-').split(' ')[0]">
                            <div class="flex items-center gap-2 px-1">
                                <span class="text-sm font-bold capitalize text-slate-700 dark:text-slate-200">
                                    {{ stage }}
                                </span>
                                <span class="bg-white dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-xs font-semibold px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-600">
                                    {{ dealsByStage[stage]?.length || 0 }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 flex-1 overflow-y-auto px-1 pb-2 hide-scrollbar">
                            <DealCard 
                                v-for="deal in dealsByStage[stage]" 
                                :key="deal.id" 
                                :deal="deal" 
                            />
                            <div v-if="!dealsByStage[stage]?.length" class="h-32 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center text-slate-400 text-sm gap-2">
                                <span class="font-medium">{{ __('No deals') }}</span>
                                <span class="text-xs text-slate-400">{{ __('Drag deals here') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div v-else class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-6 py-4 font-semibold">{{ __('Title') }}</th>
                                    <th class="px-6 py-4 font-semibold">{{ __('Value') }}</th>
                                    <th class="px-6 py-4 font-semibold">{{ __('Stage') }}</th>
                                    <th class="px-6 py-4 font-semibold">{{ __('Related To') }}</th>
                                    <th class="px-6 py-4 font-semibold">{{ __('Assigned') }}</th>
                                    <th class="px-6 py-4 font-semibold">{{ __('Closing') }}</th>
                                    <th class="px-6 py-4 text-right font-semibold">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="deal in deals.data" :key="deal.id" class="bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                        <Link :href="admin.deals.show.url(deal.id)" class="hover:text-brand-600 transition-colors">
                                            {{ deal.title }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 font-medium">{{ formatMoney(deal.value) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold capitalize border border-transparent" :class="getStageColor(deal.stage)">
                                            {{ deal.stage }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 font-medium">
                                        {{ deal.dealable?.name || '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="deal.assigned_to_user" class="flex items-center gap-2">
                                            <img :src="deal.assigned_to_user.profile_photo_url || `https://ui-avatars.com/api/?name=${deal.assigned_to_user.name}`" class="w-6 h-6 rounded-full border border-slate-200 dark:border-slate-600 object-cover">
                                            <span class="text-xs font-medium">{{ deal.assigned_to_user.name }}</span>
                                        </div>
                                        <span v-else class="text-slate-400 italic">-</span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">{{ deal.expected_close_date }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end items-center gap-2">
                                            <Link :href="admin.deals.show.url(deal.id)" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all flex items-center justify-center" :title="__('View Deal')">
                                                <Eye :size="14" />
                                            </Link>
                                            <Link :href="admin.deals.edit.url(deal.id)" class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-all flex items-center justify-center" :title="__('Edit Deal')">
                                                <Pencil :size="14" />
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="viewMode === 'list'" class="mt-6">
                    <Pagination :links="deals.links" :meta="{ from: deals.from, to: deals.to, total: deals.total }" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
