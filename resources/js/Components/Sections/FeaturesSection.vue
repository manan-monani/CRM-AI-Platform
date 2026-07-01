<script setup lang="ts">
import { 
    LayoutDashboard, Users, CheckCircle2, 
    BarChart3, ShieldCheck, Zap, Clock, Layout
} from 'lucide-vue-next';

defineProps({
    data: {
        type: Object,
        default: () => ({}),
    },
});

const icons: Record<string, any> = {
    LayoutDashboard, Users, CheckCircle2, BarChart3, ShieldCheck, Zap, Clock, Layout
};

const getIcon = (name: string) => {
    return icons[name] || Zap;
};

const kanbanStages = [
    { name: 'Lead', deals: 3, color: 'bg-purple-500' },
    { name: 'Qualified', deals: 5, color: 'bg-indigo-500' },
    { name: 'Negotiation', deals: 2, color: 'bg-blue-500' }
];

const kanbanDeals = [
    { id: 1, title: 'Acme Corp Expansion', value: '$12k', stage: 'Lead', user: 'SA' },
    { id: 2, title: 'Global Tech Solution', value: '$45k', stage: 'Qualified', user: 'JD' },
    { id: 3, title: 'Nexus Integration', value: '$8.5k', stage: 'Qualified', user: 'EB' },
    { id: 4, title: 'Cloud Migration', value: '$32k', stage: 'Negotiation', user: 'SA' },
];
</script>

<template>
    <section class="py-32 lg:py-48 bg-slate-50 dark:bg-slate-950/50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-24">
                <h2 v-if="data.title" class="text-4xl lg:text-6xl font-black text-slate-900 dark:text-white mb-6 tracking-tighter leading-tight">
                    {{ data.title }}
                </h2>
                <p v-if="data.description" class="text-slate-500 dark:text-slate-400 max-w-2xl mx-auto text-lg font-medium leading-relaxed">
                    {{ data.description }}
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <template v-for="(item, index) in data.items" :key="index">
                    <!-- Standard Feature Card -->
                    <div 
                        v-if="index !== 1 || data.items.length !== 3"
                        class="p-10 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 shadow-xl shadow-slate-200/10 hover:shadow-2xl hover:shadow-brand-600/5 transition-all duration-500 group overflow-hidden relative"
                        :class="data.items.length === 4 ? 'lg:col-span-1' : 'lg:col-span-1'"
                    >
                        <div class="absolute -top-16 -left-16 w-32 h-32 bg-brand-500/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="w-16 h-16 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 mb-8 group-hover:rotate-12 transition-transform shadow-lg shadow-brand-500/5">
                            <component :is="getIcon(item.icon)" class="w-8 h-8 fill-current" />
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-4 tracking-tight">{{ item.title }}</h3>
                        <p class="text-slate-500 dark:text-slate-400 font-medium leading-[1.6]">
                            {{ item.description }}
                        </p>
                    </div>

                    <!-- Wider Bento Card with Kanban Mockup (Always the 2nd item if there are 3) -->
                    <div 
                        v-else
                        class="lg:col-span-2 rounded-xl bg-slate-900 dark:bg-slate-900 border border-slate-800 shadow-2xl overflow-hidden group"
                    >
                        <div class="p-10 border-b border-white/5 bg-gradient-to-r from-slate-900 to-slate-800">
                             <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-brand-600/20 flex items-center justify-center border border-brand-600/30">
                                        <component :is="getIcon(item.icon)" class="text-brand-500 w-5 h-5" />
                                    </div>
                                    <h3 class="text-2xl font-black text-white tracking-tight">{{ item.title }}</h3>
                                </div>
                                <div class="hidden sm:flex gap-1.5 p-1.5 bg-slate-950 rounded-xl border border-white/5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-slate-800"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-slate-800"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-slate-800"></div>
                                </div>
                             </div>
                             <p class="text-slate-400 font-medium text-sm">{{ item.description }}</p>
                        </div>
                        
                        <!-- Kanban Mockup Area -->
                        <div class="p-8 bg-slate-950 relative min-h-[300px] overflow-hidden">
                            <div class="flex gap-6 h-full pointer-events-none group-hover:translate-x-[-10px] transition-transform duration-700">
                                <div v-for="stage in kanbanStages" :key="stage.name" class="min-w-[200px] w-full flex flex-col gap-4">
                                    <div class="flex items-center justify-between px-3 py-1.5 bg-slate-900/50 rounded-xl border border-white/5 backdrop-blur-sm">
                                        <div class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full" :class="stage.color"></div>
                                            <span class="text-[11px] uppercase font-black text-slate-300 tracking-wider">{{ stage.name }}</span>
                                        </div>
                                        <span class="bg-slate-800 text-[10px] px-2 py-0.5 rounded-full text-slate-400 font-bold border border-white/5">{{ stage.deals }}</span>
                                    </div>
                                    <div v-for="deal in kanbanDeals.filter(d => d.stage === stage.name)" :key="deal.id" class="p-4 bg-slate-900/40 rounded-xl border border-white/5 shadow-lg group/deal relative overflow-hidden">
                                        <div class="absolute top-0 right-0 w-20 h-20 bg-brand-600/5 rounded-full blur-xl translate-x-10 -translate-y-10 group-hover/deal:scale-150 transition-transform"></div>
                                        <div class="text-xs text-white font-bold mb-3 relative">{{ deal.title }}</div>
                                        <div class="flex items-center justify-between relative">
                                            <span class="px-2 py-1 rounded-lg bg-emerald-500/10 text-[10px] text-emerald-400 font-black border border-emerald-500/20">{{ deal.value }}</span>
                                            <div class="w-6 h-6 rounded-full bg-brand-600 border-2 border-slate-900 text-[9px] flex items-center justify-center text-white font-black shadow-lg">
                                                {{ deal.user }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Mockup Decorative Gradients -->
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-transparent to-slate-950 pointer-events-none opacity-50"></div>
                            
                            <!-- Mockup cursor interaction -->
                            <div class="absolute top-[60%] left-[45%] w-10 h-10 pointer-events-none animate-[bounce_1.5s_infinite] flex items-center justify-center -z-0">
                                <div class="w-12 h-12 bg-brand-500/10 rounded-full blur-xl absolute"></div>
                                <div class="w-6 h-6 bg-brand-600 rounded-lg shadow-[0_0_20px_rgba(37,99,235,0.6)] border-2 border-white/20 flex items-center justify-center">
                                    <Layout class="w-3.5 h-3.5 text-white" />
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes bounce {
  0%, 100% {
    transform: translateY(-10%);
    animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
  }
  50% {
    transform: translateY(0);
    animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
  }
}
</style>
