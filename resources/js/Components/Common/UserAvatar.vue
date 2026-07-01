<script setup lang="ts">
import { computed, ref, watch } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg, xl
    },
    src: {
        type: String,
        default: null,
    },
});

const imageError = ref(false);

// Reset error state when user or src changes
watch(() => [props.user?.id, props.src], () => {
    imageError.value = false;
});

const initials = computed(() => {
    if (!props.user?.name) return '';
    const names = props.user.name.split(' ');
    if (names.length >= 2) {
        return (names[0][0] + names[1][0]).toUpperCase();
    }
    return names[0].substring(0, 2).toUpperCase();
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'xs': return 'w-6 h-6 text-[10px]';
        case 'sm': return 'w-8 h-8 text-xs';
        case 'md': return 'w-10 h-10 text-sm';
        case 'lg': return 'w-12 h-12 text-base';
        case 'xl': return 'w-16 h-16 text-xl';
        case '2xl': return 'w-24 h-24 text-3xl';
        default: return 'w-10 h-10 text-sm';
    }
});

// Generate a consistent background color based on the name
const bgColor = computed(() => {
    const colors = [
        'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
        'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
        'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
        'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
        'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
        'bg-teal-100 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400',
        'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400',
        'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400',
        'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
        'bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400',
        'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
        'bg-fuchsia-100 text-fuchsia-600 dark:bg-fuchsia-900/30 dark:text-fuchsia-400',
        'bg-pink-100 text-pink-600 dark:bg-pink-900/30 dark:text-pink-400',
        'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
    ];
    
    if (!props.user?.name) return colors[0];
    
    // Simple hash to select color
    let hash = 0;
    for (let i = 0; i < props.user.name.length; i++) {
        hash = props.user.name.charCodeAt(i) + ((hash << 5) - hash);
    }
    
    return colors[Math.abs(hash) % colors.length];
});

const imageUrl = computed(() => {
    // Priority: 1. Prop src, 2. User profile_image_url (if available via accessor), 3. User profile_image (raw path)
    if (props.src) return props.src;
    if (props.user?.profile_photo_url) return props.user.profile_photo_url; // Jetstream/Standard style
    if (props.user?.profile_image) return '/storage/' + props.user.profile_image;
    return null;
});

const shouldShowImage = computed(() => {
    return imageUrl.value && !imageError.value;
});

const handleImageError = () => {
    imageError.value = true;
};
</script>

<template>
    <div 
        class="relative inline-flex items-center justify-center rounded-full overflow-hidden shrink-0 border border-white/10"
        :class="[sizeClasses, !shouldShowImage ? bgColor : 'bg-slate-100 dark:bg-slate-800']"
    >
        <img 
            v-if="shouldShowImage" 
            :src="imageUrl" 
            :alt="user.name" 
            @error="handleImageError"
            class="w-full h-full object-cover"
        />
        <span v-else class="font-bold select-none">
            {{ initials }}
        </span>
    </div>
</template>
