<script setup lang="ts">
defineProps<{
    type?: 'button' | 'submit' | 'reset';
}>();
</script>

<template>
    <button
        :type="type || 'button'"
        class="inline-flex items-center justify-center px-6 py-2.5 h-[44px] bg-brand-600 dark:bg-brand-500 border border-brand-700 dark:border-brand-600 rounded-lg font-bold text-[15px] text-white shadow-md shadow-brand-500/20 hover:bg-brand-700 dark:hover:bg-brand-400 hover:shadow-lg active:scale-95 focus:outline-none focus:ring-4 focus:ring-brand-500/20 disabled:opacity-50 disabled:cursor-not-allowed disabled:scale-100 transition-all duration-200 cursor-pointer"
    >
        <slot />
    </button>
</template>
