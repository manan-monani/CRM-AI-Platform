<script setup lang="ts">
import { PropType } from 'vue';
import { AlertTriangle, AlertCircle, Info, Loader2, X } from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: 'Are you sure?',
    },
    message: {
        type: String,
        default: 'This action cannot be undone.',
    },
    confirmText: {
        type: String,
        default: 'Confirm',
    },
    cancelText: {
        type: String,
        default: 'Cancel',
    },
    type: {
        type: String as PropType<'danger' | 'warning' | 'info'>,
        default: 'danger',
    },
    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'confirm']);

const colors = {
    danger: {
        iconBg: 'bg-gradient-to-br from-red-50 to-red-100 dark:from-red-950/50 dark:to-red-900/30',
        iconText: 'text-red-600 dark:text-red-400',
        iconRing: 'ring-red-100 dark:ring-red-900/50',
        button: 'bg-gradient-to-br from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white shadow-lg shadow-red-600/30',
        icon: AlertTriangle,
    },
    warning: {
        iconBg: 'bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-950/50 dark:to-amber-900/30',
        iconText: 'text-amber-600 dark:text-amber-400',
        iconRing: 'ring-amber-100 dark:ring-amber-900/50',
        button: 'bg-gradient-to-br from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white shadow-lg shadow-amber-600/30',
        icon: AlertCircle,
    },
    info: {
        iconBg: 'bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950/50 dark:to-blue-900/30',
        iconText: 'text-blue-600 dark:text-blue-400',
        iconRing: 'ring-blue-100 dark:ring-blue-900/50',
        button: 'bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white shadow-lg shadow-blue-600/30',
        icon: Info,
    },
};
</script>

<template>
    <transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gradient-to-br from-slate-900/70 via-slate-900/60 to-slate-900/70 backdrop-blur-md transition-all" @click="$emit('close')"></div>

            <transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 scale-90"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-90"
            >
                <div class="relative bg-white dark:bg-slate-900 rounded-xl shadow-2xl shadow-slate-900/30 dark:shadow-black/50 overflow-hidden transform transition-all sm:max-w-lg w-full ring-1 ring-slate-200/50 dark:ring-white/10 border border-slate-100 dark:border-slate-800">
                    <!-- Close button -->
                    <button
                        @click="$emit('close')"
                        class="absolute top-4 right-4 z-10 group flex items-center justify-center w-9 h-9 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-slate-100/80 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-all duration-200 backdrop-blur-sm active:scale-95"
                    >
                        <X class="w-4 h-4 group-hover:scale-110 transition-transform" />
                    </button>

                    <div class="p-8 text-center space-y-6">
                        <!-- Icon with pulsing animation -->
                        <div class="relative mx-auto w-20 h-20 flex items-center justify-center">
                            <div class="absolute inset-0 rounded-full animate-ping opacity-20" :class="colors[type].iconBg"></div>
                            <div class="relative rounded-xl w-20 h-20 flex items-center justify-center ring-4 transition-all" 
                                 :class="[colors[type].iconBg, colors[type].iconText, colors[type].iconRing]">
                                <component :is="colors[type].icon" :size="36" class="relative z-10" />
                            </div>
                        </div>

                        <!-- Text -->
                        <div class="space-y-3">
                             <h3 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __(title) }}</h3>
                             <p class="text-[15px] text-slate-600 dark:text-slate-400 leading-relaxed max-w-sm mx-auto">{{ __(message) }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-4">
                             <button 
                                @click="$emit('close')" 
                                class="flex-1 px-6 py-3.5 rounded-xl border-[1.5px] border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-bold text-[15px] text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200 active:scale-95 shadow-sm"
                            >
                                 {{ __(cancelText) }}
                            </button>
                            <button 
                                @click="$emit('confirm')" 
                                :disabled="processing"
                                class="flex-1 px-6 py-3.5 rounded-xl font-bold text-[15px] transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:scale-100 flex items-center justify-center gap-2"
                                :class="colors[type].button"
                            >
                                <Loader2 v-if="processing" class="animate-spin" :size="18" />
                                <span>{{ __(confirmText) }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>
