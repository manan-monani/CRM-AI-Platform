<script setup lang="ts">
defineProps<{
    type?: 'button' | 'submit' | 'reset';
}>();
</script>

<template>
    <button
        :type="type || 'button'"
        class="inline-flex items-center justify-center px-6 py-3 h-12 bg-white dark:bg-slate-800 border-[1.5px] border-slate-200 dark:border-slate-700 rounded-xl font-bold text-[15px] text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 hover:border-slate-300 dark:hover:border-slate-600 hover:shadow-md active:scale-95 focus:outline-none focus:ring-4 focus:ring-slate-200 dark:focus:ring-slate-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:scale-100 transition-all duration-200"
    >
        <slot />
    </button>
</template>
