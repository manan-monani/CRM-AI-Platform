<script setup lang="ts">
import { onMounted, ref } from 'vue';

defineProps<{
    modelValue: string | number | null;
}>();

defineEmits(['update:modelValue']);

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        ref="input"
        class="w-full h-14 px-6 text-[15px] font-semibold text-slate-900 dark:text-white placeholder:text-slate-400/80 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl outline-none transition-all focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 disabled:opacity-50 disabled:cursor-not-allowed"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
</template>
