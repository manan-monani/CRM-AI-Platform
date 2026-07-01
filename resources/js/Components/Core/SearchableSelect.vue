<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { ChevronDown, Check, Search, X } from 'lucide-vue-next';

interface Option {
    value: string | number;
    label: string;
    image?: string;
}

const props = defineProps<{
    modelValue: string | number | null;
    options: Option[];
    placeholder?: string;
    error?: string;
    disabled?: boolean;
    triggerClass?: string;
}>();

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const searchInput = ref<HTMLInputElement | null>(null);
const containerRef = ref<HTMLElement | null>(null);

const selectedOption = computed(() => {
    return props.options.find(o => o.value === props.modelValue);
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const query = searchQuery.value.toLowerCase();
    return props.options.filter(option => 
        option.label.toLowerCase().includes(query)
    );
});

const shouldShowSearch = computed(() => {
    return props.options.length > 5;
});

const dropdownPosition = ref<'top' | 'bottom'>('bottom');

const toggleDropdown = async () => {
    if (props.disabled) return;
    
    if (!isOpen.value) {
        isOpen.value = true;
        
        // Check positioning
        await nextTick();
        if (containerRef.value) {
            const rect = containerRef.value.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            // Max height is roughly 250px (search + 60vh max or fixed height)
            // If less than 250px below and more space above, flip it
            if (spaceBelow < 250 && rect.top > 250) {
                dropdownPosition.value = 'top';
            } else {
                dropdownPosition.value = 'bottom';
            }
        }
        
        if (shouldShowSearch.value) {
             searchInput.value?.focus();
        }
    } else {
        isOpen.value = false;
        searchQuery.value = '';
    }
};

const selectOption = (option: Option) => {
    emit('update:modelValue', option.value);
    isOpen.value = false;
    searchQuery.value = '';
};

const clearSelection = (e: Event) => {
    e.stopPropagation();
    emit('update:modelValue', null);
};

onClickOutside(containerRef, () => {
    isOpen.value = false;
    searchQuery.value = '';
});
</script>

<template>
    <div ref="containerRef" class="relative w-full">
        <!-- Trigger -->
        <div
            @click="toggleDropdown"
            class="w-full h-14 px-6 flex items-center justify-between text-[15px] font-semibold border-[1.5px] rounded-xl shadow-sm transition-all duration-200 cursor-pointer"
            :class="[
                disabled 
                    ? 'bg-slate-50 dark:bg-slate-800/50 opacity-50 cursor-not-allowed border-slate-200 dark:border-slate-700 text-slate-500' 
                    : (triggerClass || 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-900 dark:text-white'),
                isOpen ? 'ring-4 ring-brand-100 dark:ring-brand-900/30 border-brand-400 dark:border-brand-500' : 'shadow-slate-100/50 dark:shadow-none',
                error ? 'border-red-300 focus:border-red-500 focus:ring-red-100' : ''
            ]"
        >
            <div class="flex items-center gap-2 truncate pr-2">
                <img 
                    v-if="selectedOption?.image" 
                    :src="selectedOption.image" 
                    class="w-6 h-6 rounded-full border border-slate-200 dark:border-slate-700 flex-shrink-0 object-cover"
                    alt="" 
                />
                <span v-if="selectedOption" class="truncate">{{ selectedOption.label }}</span>
                <span v-else class="text-slate-400 font-normal">{{ placeholder || 'Select an option' }}</span>
            </div>

            <div class="flex items-center gap-2 text-slate-400">
                <button 
                    v-if="selectedOption && !disabled" 
                    @click="clearSelection"
                    class="p-0.5 hover:text-slate-600 dark:hover:text-slate-300 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                >
                    <X :size="14" />
                </button>
                <ChevronDown 
                    :size="16" 
                    class="transition-transform duration-200"
                    :class="{ 'rotate-180': isOpen }"
                />
            </div>
        </div>

        <!-- Dropdown Menu -->
        <div 
            v-if="isOpen"
            class="absolute z-50 w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-100"
            :class="[
                dropdownPosition === 'top' ? 'bottom-full mb-2 origin-bottom' : 'mt-2 origin-top'
            ]"
        >
            <!-- Search Input -->
            <div v-if="shouldShowSearch" class="p-2 border-b border-slate-100 dark:border-slate-700/50">
                <div class="relative">
                    <Search :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        ref="searchInput"
                        v-model="searchQuery"
                        type="text"
                        class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none focus:border-brand-400 dark:focus:border-brand-500 text-slate-900 dark:text-white placeholder-slate-400"
                        placeholder="Search..."
                        @click.stop
                    />
                </div>
            </div>

            <!-- Options List -->
            <div class="max-h-60 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-700">
                <div v-if="filteredOptions.length === 0" class="p-4 text-center text-sm text-slate-500 dark:text-slate-400">
                    No results found
                </div>
                
                <button
                    v-for="option in filteredOptions"
                    :key="option.value"
                    @click="selectOption(option)"
                    class="w-full px-4 py-2.5 flex items-center justify-between text-sm transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/50"
                    :class="[
                        modelValue === option.value 
                            ? 'text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/10 font-medium' 
                            : 'text-slate-700 dark:text-slate-300'
                    ]"
                >
                    <div class="flex items-center gap-2 truncate">
                        <img 
                            v-if="option.image" 
                            :src="option.image" 
                            class="w-6 h-6 rounded-full border border-slate-200 dark:border-slate-600 object-cover"
                            alt="" 
                        />
                        <span class="truncate">{{ option.label }}</span>
                    </div>
                    <Check v-if="modelValue === option.value" :size="16" class="flex-shrink-0" />
                </button>
            </div>
        </div>
    </div>
</template>
