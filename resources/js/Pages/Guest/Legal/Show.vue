<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { FileText, Home, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    content: string | null;
}>();

const renderedContent = computed(() => {
    if (!props.content) return '';
    
    // Content is already HTML from the rich text editor
    return props.content;
});

const lastUpdated = computed(() => {
    return new Date().toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
});
</script>

<template>
    <Head :title="__(title)" />
    <PublicLayout>
        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-2 text-sm mb-6 animate-fade-in">
            <Link href="/" class="flex items-center text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                <Home class="w-4 h-4 mr-1" />
                {{ __('Home') }}
            </Link>
            <ChevronRight class="w-4 h-4 text-gray-400" />
            <span class="text-gray-900 dark:text-white font-medium">{{ __(title) }}</span>
        </nav>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden animate-fade-in-up">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-primary-50 to-primary-100 dark:from-gray-700 dark:to-gray-800 px-8 sm:px-12 py-10 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-start gap-4">
                    <div class="hidden sm:flex items-center justify-center w-16 h-16 bg-white dark:bg-gray-900 rounded-xl shadow-md">
                        <FileText class="w-8 h-8 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div class="flex-1">
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white mb-2">
                            {{ __(title) }}
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ __('Last updated') }}: {{ lastUpdated }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Content Section -->
            <div class="px-8 sm:px-12 py-10">
                <div v-if="content" 
                    class="prose prose-lg dark:prose-invert max-w-none
                           prose-headings:font-bold prose-headings:text-gray-900 dark:prose-headings:text-white prose-headings:scroll-mt-20
                           prose-h1:text-3xl prose-h1:mb-6 prose-h1:mt-8 prose-h1:pb-3 prose-h1:border-b prose-h1:border-gray-200 dark:prose-h1:border-gray-700
                           prose-h2:text-2xl prose-h2:mb-4 prose-h2:mt-8
                           prose-h3:text-xl prose-h3:mb-3 prose-h3:mt-6
                           prose-p:text-gray-600 dark:prose-p:text-gray-300 prose-p:leading-relaxed prose-p:mb-4
                           prose-a:text-primary-600 dark:prose-a:text-primary-400 prose-a:no-underline hover:prose-a:underline prose-a:font-medium
                           prose-strong:text-gray-900 dark:prose-strong:text-white prose-strong:font-semibold
                           prose-ul:my-6 prose-ul:list-disc prose-ul:pl-6
                           prose-ol:my-6 prose-ol:list-decimal prose-ol:pl-6
                           prose-li:text-gray-600 dark:prose-li:text-gray-300 prose-li:my-2
                           prose-blockquote:border-l-4 prose-blockquote:border-primary-500 prose-blockquote:pl-4 prose-blockquote:italic prose-blockquote:text-gray-700 dark:prose-blockquote:text-gray-300
                           prose-code:text-primary-600 dark:prose-code:text-primary-400 prose-code:bg-gray-100 dark:prose-code:bg-gray-900 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-sm prose-code:font-mono
                           prose-pre:bg-gray-900 dark:prose-pre:bg-black prose-pre:text-gray-100 prose-pre:rounded-lg prose-pre:p-4
                           prose-table:border-collapse prose-table:w-full
                           prose-th:bg-gray-50 dark:prose-th:bg-gray-700 prose-th:p-3 prose-th:text-left prose-th:font-semibold
                           prose-td:p-3 prose-td:border-t prose-td:border-gray-200 dark:prose-td:border-gray-700
                           prose-hr:border-gray-200 dark:prose-hr:border-gray-700 prose-hr:my-8" 
                    v-html="renderedContent">
                </div>
                
                <!-- Empty State -->
                <div v-else class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-full mb-6 shadow-inner">
                        <FileText class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ __('Content Coming Soon') }}
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-6">
                        {{ __('This page is currently being updated. Please check back later for more information.') }}
                    </p>
                    <Link href="/" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-all shadow-lg shadow-primary-500/30 hover:shadow-primary-500/50">
                        <Home class="w-4 h-4" />
                        {{ __('Return Home') }}
                    </Link>
                </div>
            </div>

            <!-- Footer Info -->
            <div v-if="content" class="px-8 sm:px-12 py-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('If you have any questions about this policy, please') }} 
                        <a href="mailto:support@example.com" class="text-primary-600 dark:text-primary-400 hover:underline font-medium">{{ __('contact us') }}</a>.
                    </p>
                    <Link href="/" class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        <Home class="w-4 h-4" />
                        {{ __('Back to Home') }}
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeInUp {
    from { 
        opacity: 0; 
        transform: translateY(20px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

/* Smooth scroll behavior for anchor links */
:deep(a[href^="#"]) {
    scroll-behavior: smooth;
}

/* Custom scrollbar for content */
:deep(.prose) {
    scrollbar-width: thin;
    scrollbar-color: rgb(209 213 219) transparent;
}

:deep(.prose)::-webkit-scrollbar {
    width: 8px;
}

:deep(.prose)::-webkit-scrollbar-track {
    background: transparent;
}

:deep(.prose)::-webkit-scrollbar-thumb {
    background-color: rgb(209 213 219);
    border-radius: 4px;
}

.dark :deep(.prose)::-webkit-scrollbar-thumb {
    background-color: rgb(75 85 99);
}
</style>
