<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { login, register } from '@/routes';
import { privacy, terms } from '@/routes/legal';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import Toast from '@/Components/Common/Toast.vue';
import { ref, computed } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const page = usePage();
const businessSettings = computed(() => (page.props.branding as any)?.business_settings || {});
const appName = computed(() => businessSettings.value.business_name || page.props.name || 'Laravel');

const logoUrl = computed(() => {
    const logo = businessSettings.value.logo_url;
    if (!logo) return null;
    return logo.startsWith('http') || logo.startsWith('/storage') ? logo : '/storage/' + logo;
});

const logoDarkUrl = computed(() => {
    const logo = businessSettings.value.logo_dark_url;
    if (!logo) return null;
    return logo.startsWith('http') || logo.startsWith('/storage') ? logo : '/storage/' + logo;
});

const isAuthenticating = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    isAuthenticating.value = true;
    form.post(register.url(), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
            isAuthenticating.value = false;
        },
        onError: () => {
             isAuthenticating.value = false;
        }
    });
};
</script>

<template>
    <Head :title="`Register | ${appName}`" />
    <Toast />

    <div class="min-h-screen lg:h-screen lg:overflow-hidden flex flex-col lg:flex-row bg-white dark:bg-gray-900 font-sans antialiased text-slate-800 dark:text-slate-200 transition-colors duration-300">
        <!-- Left Side: Professional Branding -->
        <div class="lg:w-[45%] mesh-gradient relative flex flex-col justify-between p-12 text-white lg:h-full lg:overflow-y-auto hide-scrollbar">
            <!-- Abstract Pattern Overlay -->
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#ffffff 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>

            <!-- Top Branding -->
            <div class="relative z-10">
                <Link href="/" class="flex items-center gap-3 mb-16 group hover:opacity-90 transition-opacity">
                     <div class="flex items-center space-x-3">
                        <div v-if="logoUrl" class="h-10 w-10 bg-white rounded-xl p-1 shadow-lg border border-slate-200/20 flex items-center justify-center transition-transform group-hover:scale-105 overflow-hidden">
                            <img :src="logoUrl" class="dark:hidden h-full w-full object-contain" />
                            <img :src="logoDarkUrl || logoUrl" class="hidden dark:block h-full w-full object-contain" />
                        </div>
                         <div v-else class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/20 border border-primary-500/20 transition-transform group-hover:scale-105">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 576 512"><path d="M0 80C0 53.5 21.5 32 48 32h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V80zM64 96v64h64V96H64zM0 336c0-26.5 21.5-48 48-48h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V336zM64 352v64h64v-64H64zM309.7 185.3l-147.9 29.8C154.2 216.7 148 221.7 148 228v56c0 8.8 7.2 16 16 16h32c6.9 0 13.1-4.2 15.3-10.7l22-66H310l34.8 104.5c4.8 14.5-9.6 28.2-22.8 28.2H320c-8.8 0-16 7.2-16 16V392c0 8.8 7.2 16 16 16h32c12 0 22.4-8.2 25.1-19.9l32.3-139c1.9-8.3-5.2-16-13.8-16h-42.5l-23.4-70.3c-2.8-8.4-11.8-13.3-20.2-11.5l-63.5 12.7c36.4 7.2 63.8 39.2 63.8 77.2v.2c0 7.3-1.1 14.3-3 21.1zM480 32c-26.5 0-48 21.5-48 48v96c0 26.5 21.5 48 48 48h96c26.5 0 48-21.5 48-48V80c0-26.5-21.5-48-48-48H480zm16 144v-64h64v64H496zm-16 112c-26.5 0-48 21.5-48 48v96c0 26.5 21.5 48 48 48h96c26.5 0 48-21.5 48-48V336c0-26.5-21.5-48-48-48H480zm16 144v-64h64v64H496z"/></svg>
                        </div>
                        <span class="text-xl font-extrabold tracking-tight text-white uppercase">{{ appName }}<span class="text-primary-400">.</span></span>
                    </div>
                </Link>
                
                <div class="animate-in">
                    <h1 class="text-4xl lg:text-5xl font-extrabold mb-6 leading-[1.15]">
                        {{ businessSettings.register_headline || appName }}
                    </h1>
                    <p class="text-slate-200 text-lg max-w-md leading-relaxed mb-8">
                        {{ businessSettings.register_subheadline || businessSettings.tagline }}
                    </p>
                </div>
            </div>

            <!-- Middle: Social Proof / Value Prop -->
             <div class="relative z-10 glass-panel p-6 rounded-xl max-w-sm animate-in" style="animation-delay: 0.2s;">
                <div class="flex items-center gap-4 mb-2">
                    <div class="flex -space-x-2">
                         <div class="w-8 h-8 rounded-full bg-primary-500 border-2 border-slate-800 flex items-center justify-center text-xs font-bold ring-2 ring-transparent">A</div>
                         <div class="w-8 h-8 rounded-full bg-secondary-500 border-2 border-slate-800 flex items-center justify-center text-xs font-bold ring-2 ring-transparent">B</div>
                         <div class="w-8 h-8 rounded-full bg-primary-400 border-2 border-slate-800 flex items-center justify-center text-xs font-bold ring-2 ring-transparent">C</div>
                    </div>
                    <div class="text-sm font-semibold text-slate-200">
                        {{ businessSettings.register_social_proof || __('Trusted by thousands') }}
                    </div>
                </div>
                <p class="text-xs text-slate-300">{{ businessSettings.register_social_proof_sub || __('Join our community today') }}</p>
            </div>


            <!-- Footer: System Stats -->
            <div class="relative z-10 flex items-center space-x-8 text-sm text-slate-300 border-t border-white/10 pt-8 mt-12">
                <div class="flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ businessSettings.register_registration_status || __('Open for new members') }}</span>
                </div>
                <span>{{ businessSettings.register_security_badge || __('Secure & Encrypted') }}</span>
            </div>
        </div>

        <!-- Right Side: Register Form -->
       <div class="lg:w-1/2 flex items-center justify-center p-8 lg:p-12 bg-white dark:bg-gray-900 transition-colors duration-300 relative lg:h-full lg:overflow-y-auto hide-scrollbar">
            
            <div class="absolute top-4 right-4 flex items-center space-x-2">
                <ThemeToggle />
            </div>

            <div class="w-full max-w-md">
                
                <div class="mb-10">
                    <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-2 transition-colors">{{ businessSettings.register_form_title || __('Create Account') }}</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">{{ businessSettings.register_form_subtitle || __('Get started with your account') }}</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                     <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2 ml-1" for="name">{{ __('Full Name') }}</label>
                        <div class="relative group">
                            <input
                                id="name"
                                type="text"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl outline-none transition-all duration-300 focus:bg-white dark:focus:bg-gray-800 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 dark:focus:ring-primary-500/20 placeholder:text-slate-400 font-medium text-slate-700 dark:text-white"
                                :placeholder="__('John Doe')"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                            />
                        </div>
                        <div v-if="form.errors.name" class="mt-2 text-sm text-red-600 ml-1">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2 ml-1" for="email">{{ __('Work Email') }}</label>
                        <div class="relative group">
                            <input
                                id="email"
                                type="text"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl outline-none transition-all duration-300 focus:bg-white dark:focus:bg-gray-800 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 dark:focus:ring-primary-500/20 placeholder:text-slate-400 font-medium text-slate-700 dark:text-white"
                                :placeholder="__('name@company.com')"
                                v-model="form.email"
                                required
                                autocomplete="username"
                            />
                        </div>
                        <div v-if="form.errors.email" class="mt-2 text-sm text-red-600 ml-1">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2 ml-1" for="password">{{ __('Password') }}</label>
                        <div class="relative group">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl outline-none transition-all duration-300 focus:bg-white dark:focus:bg-gray-800 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 dark:focus:ring-primary-500/20 placeholder:text-slate-400 font-medium text-slate-700 dark:text-white pr-10"
                                :placeholder="__('••••••••')"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                        </div>
                         <div v-if="form.errors.password" class="mt-2 text-sm text-red-600 ml-1">{{ form.errors.password }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2 ml-1" for="password_confirmation">{{ __('Confirm Password') }}</label>
                        <div class="relative group">
                            <input
                                :type="showConfirmPassword ? 'text' : 'password'"
                                id="password_confirmation"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl outline-none transition-all duration-300 focus:bg-white dark:focus:bg-gray-800 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 dark:focus:ring-primary-500/20 placeholder:text-slate-400 font-medium text-slate-700 dark:text-white pr-10"
                                :placeholder="__('••••••••')"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors"
                            >
                                <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                        </div>
                         <div v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600 ml-1">{{ form.errors.password_confirmation }}</div>
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing || isAuthenticating"
                             :class="{'btn-loading': form.processing || isAuthenticating}"
                            class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-xl shadow-primary-200 dark:shadow-none transform transition-all active:scale-95 flex items-center justify-center space-x-2 disabled:opacity-75 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing || isAuthenticating">{{ __('Creating Account...') }}</span>
                            <span v-else>{{ __('Create Account') }}</span>
                            <svg v-if="!(form.processing || isAuthenticating)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                             <svg v-else class="animate-spin ml-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </div>
                </form>

                <!-- Helper Footer -->
                <div class="mt-8 pt-8 border-t border-slate-50 dark:border-gray-800 flex flex-col items-center space-y-4">
                    <p class="text-slate-500 dark:text-slate-400 text-sm">
                        {{ __('Already have an account?') }} <Link :href="login.url()" class="font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">{{ __('Sign In') }}</Link>
                    </p>
                     <div class="flex space-x-4 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-tighter">
                        <Link :href="privacy.url()" class="hover:text-slate-600 dark:hover:text-slate-300">{{ __('Privacy Policy') }}</Link>
                        <span>•</span>
                        <Link :href="terms.url()" class="hover:text-slate-600 dark:hover:text-slate-300">{{ __('Terms of Service') }}</Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

.mesh-gradient {
    background: linear-gradient(135deg, var(--color-primary-900) 0%, var(--color-primary-600) 100%);
}

.glass-panel {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.input-transition {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-loading {
    pointer-events: none;
    opacity: 0.8;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-in {
    animation: slideIn 0.5s ease-out forwards;
}
</style>
