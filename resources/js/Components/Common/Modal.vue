<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: Boolean,
    title: {
        type: String,
        default: '',
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show && props.closeable) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
});

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
        '3xl': 'sm:max-w-3xl',
        '4xl': 'sm:max-w-4xl',
    }[props.maxWidth] || 'sm:max-w-2xl';
});
</script>

<template>
    <div v-show="show" class="fixed inset-0 z-[100] overflow-y-auto px-4 py-6 sm:px-0" scroll-region>
        <transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="show" class="fixed inset-0 transform transition-all" @click="close">
                <div class="absolute inset-0 bg-gradient-to-br from-slate-900/70 via-slate-900/60 to-slate-900/70 backdrop-blur-md"></div>
            </div>
        </transition>

        <transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-90"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-90"
        >
            <div
                v-show="show"
                @click.stop
                class="relative z-10 mb-6 bg-white dark:bg-slate-900 rounded-xl shadow-2xl shadow-slate-900/30 dark:shadow-black/50 transform transition-all sm:w-full sm:mx-auto ring-1 ring-slate-200/50 dark:ring-white/10 border border-slate-100 dark:border-slate-800"
                :class="maxWidthClass"
            >
                <div v-if="title" class="flex items-center justify-between px-8 py-5 bg-gradient-to-b from-slate-50/50 to-transparent dark:from-slate-800/30 dark:to-transparent border-b border-slate-100 dark:border-slate-800/50 rounded-t-xl">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                        {{ title }}
                    </h3>
                    <button
                        v-if="closeable"
                        type="button"
                        class="group flex items-center justify-center w-10 h-10 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-slate-200 dark:focus:ring-slate-700 active:scale-95"
                        @click="close"
                    >
                        <X class="w-5 h-5 group-hover:scale-110 transition-transform" />
                    </button>
                </div>
                
                <div class="px-8 py-6">
                    <slot />
                </div>
                
                
                <div v-if="$slots.footer" class="px-8 py-5 bg-gradient-to-t from-slate-50/50 to-transparent dark:from-slate-800/30 dark:to-transparent border-t border-slate-100 dark:border-slate-800/50 rounded-b-xl">
                    <slot name="footer" />
                </div>
            </div>
        </transition>
    </div>
</template>
