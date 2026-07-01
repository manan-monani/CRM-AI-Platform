<script setup lang="ts">
import { AlertCircle } from 'lucide-vue-next';
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message" class="flex items-center gap-1.5 mt-1.5 transition-all animate-in fade-in slide-in-from-top-1">
        <AlertCircle :size="14" class="text-[#D32F2F] flex-shrink-0" />
        <p class="text-xs font-medium text-[#D32F2F]">
            {{ message }}
        </p>
    </div>
</template>
