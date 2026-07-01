<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { login, register } from '@/routes';
import { login as adminLogin } from '@/routes/admin';
import auth from '@/routes/auth';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import Toast from '@/Components/Common/Toast.vue';
import { ref, computed } from 'vue';

defineProps<{
    status?: string;
    canResetPassword?: boolean;
}>();

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

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const isAuthenticating = ref(false);

const submit = () => {
    isAuthenticating.value = true;
    form.post(login.url(), {
        onFinish: () => {
             form.reset('password');
             isAuthenticating.value = false;
        },
        onError: () => {
            isAuthenticating.value = false;
        }
    });
};

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <Head :title="`${appName} | Login`" />
    <Toast />

    <div class="min-h-screen lg:h-screen lg:overflow-hidden flex flex-col lg:flex-row bg-white dark:bg-gray-900 font-sans text-slate-700 dark:text-slate-300 transition-colors duration-300">
        <!-- Left Side: System Branding/Information -->
        <div class="lg:w-1/2 bg-gradient-to-br from-primary-900 to-primary-600 relative flex flex-col items-center justify-center p-12 text-white lg:h-full lg:overflow-y-auto hide-scrollbar">
            <!-- Decorative Shapes -->
            <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-white rounded-full mix-blend-overlay filter blur-3xl animate-float"></div>
                <div class="absolute bottom-1/4 right-0 w-64 h-64 bg-white rounded-full mix-blend-overlay filter blur-3xl animate-float" style="animation-delay: 2s;"></div>
            </div>

            <div class="relative z-10 max-w-lg text-center lg:text-left">
                <Link href="/" class="flex items-center space-x-3 mb-8 group hover:opacity-90 transition-opacity">
                    <div class="inline-flex items-center justify-center w-10 h-10 bg-white/10 backdrop-blur-md rounded-xl text-white border border-white/20 overflow-hidden shadow-lg transition-transform group-hover:scale-105">
                        <template v-if="logoUrl">
                            <img :src="logoUrl" class="dark:hidden w-full h-full object-contain p-1" />
                            <img :src="logoDarkUrl || logoUrl" class="hidden dark:block w-full h-full object-contain p-1" />
                        </template>
                        <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 320 512"><path d="M296 160H180.6l42.6-129.8C227.2 15 215.7 0 200 0H56C44 0 33.8 8.9 32.2 20.8l-32 240C-1.7 275.2 25.2 288 40 288h115.4l-42.6 129.8C108.8 434.9 120.3 448 136 448h144c12 0 22.2-8.9 23.8-20.8l32-240c1.9-14.3-9.2-27.2-24-27.2zM250.7 220.3l-32 240c-.6 4.3-4.1 7.7-8.7 7.7h-144c-4.1 0-7.7-2.9-8.4-7L100.2 332H40c-4.1 0-7.7-2.9-8.4-7L-11.4 69.8c-.8-5.8 3.7-10.9 9.5-10.9h144c4.1 0 7.7 2.9 8.4 7L167.8 196H228c4.1 0 7.7 2.9 8.4 7l24.3 15.3z"/></svg>
                    </div>
                    <span class="text-xl font-extrabold tracking-tight text-white uppercase">{{ appName }}<span class="text-primary-400">.</span></span>
                </Link>
                <h1 class="text-4xl lg:text-6xl font-bold mb-6 leading-tight">{{ businessSettings.login_headline || appName }}</h1>
                <p class="text-primary-100 text-lg lg:text-xl mb-12 font-light leading-relaxed">
                    {{ businessSettings.login_subheadline || businessSettings.tagline }}
                </p>

                <!-- Feature Checklist -->
                <div class="space-y-4">
                    <div v-for="(feature, index) in $page.props.branding.login?.features" :key="index" class="flex items-center space-x-3">
                        <div class="w-6 h-6 rounded-full bg-primary-400/20 flex items-center justify-center text-primary-200">
                             <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 512 512"><path d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"/></svg>
                        </div>
                        <span class="text-primary-50 font-medium">{{ __(feature) }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer Copyright (Left) -->
            <div class="absolute bottom-8 left-12 hidden lg:block text-primary-200/50 text-sm">
                &copy; {{ new Date().getFullYear() }} {{ appName }} Inc. All rights reserved.
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="lg:w-1/2 flex items-center justify-center p-6 md:p-8 bg-white dark:bg-gray-900 transition-colors duration-300 relative lg:h-full lg:overflow-y-auto hide-scrollbar">
            
            <div class="absolute top-4 right-4 flex items-center space-x-2">
                <ThemeToggle />
            </div>

            <div class="w-full max-w-md">
                <!-- Mobile Branding (Only visible on small screens) -->
                <div class="lg:hidden text-center mb-10">
                    <Link href="/" class="inline-flex items-center justify-center w-12 h-12 bg-primary-600 dark:bg-primary-900/30 rounded-xl mb-4 text-white p-2">
                        <template v-if="logoUrl">
                            <img :src="logoUrl" class="dark:hidden w-full h-full object-contain" />
                            <img :src="logoDarkUrl || logoUrl" class="hidden dark:block w-full h-full object-contain" />
                        </template>
                        <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 320 512"><path d="M296 160H180.6l42.6-129.8C227.2 15 215.7 0 200 0H56C44 0 33.8 8.9 32.2 20.8l-32 240C-1.7 275.2 25.2 288 40 288h115.4l-42.6 129.8C108.8 434.9 120.3 448 136 448h144c12 0 22.2-8.9 23.8-20.8l32-240c1.9-14.3-9.2-27.2-24-27.2zM250.7 220.3l-32 240c-.6 4.3-4.1 7.7-8.7 7.7h-144c-4.1 0-7.7-2.9-8.4-7L100.2 332H40c-4.1 0-7.7-2.9-8.4-7L-11.4 69.8c-.8-5.8 3.7-10.9 9.5-10.9h144c4.1 0 7.7 2.9 8.4 7L167.8 196H228c4.1 0 7.7 2.9 8.4 7l24.3 15.3z"/></svg>
                    </Link>
                    <h2 class="text-2xl font-bold text-slate-800 dark:text-white">{{ businessSettings.login_welcome_back || __('Welcome Back') }}</h2>
                </div>

                <!-- Header -->
                <div class="mb-10 hidden lg:block">
                    <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">{{ __($page.props.branding.login?.form_title) }}</h2>
                    <p class="text-slate-500 dark:text-slate-400">{{ __($page.props.branding.login?.form_subtitle) }}</p>
                </div>

                 <div v-if="status" class="mb-6 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 rounded-xl p-4 text-sm font-medium">
                    {{ status }}
                </div>


                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2 ml-1" for="email">{{ __('Email Address') }}</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-primary-600 dark:group-focus-within:text-primary-400 transition-colors duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </span>
                            <input
                                id="email"
                                type="email"
                                class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl outline-none text-slate-700 dark:text-gray-200 placeholder:text-slate-400 font-medium transition-all duration-300 focus:bg-white dark:focus:bg-gray-800 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 dark:focus:ring-primary-500/20"
                                :placeholder="__('name@company.com')"
                                v-model="form.email"
                                required
                                autofocus
                            />
                        </div>
                        <div v-if="form.errors.email" class="mt-2 text-sm text-red-600 ml-1">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <div class="flex justify-between mb-2 px-1">
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300" for="password">{{ __('Password') }}</label>
                            <a v-if="canResetPassword" href="#" class="text-xs font-bold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">{{ __('Forgot?') }}</a>
                        </div>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-primary-600 dark:group-focus-within:text-primary-400 transition-colors duration-300">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 448 512"><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>
                            </span>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                class="w-full pl-11 pr-12 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl outline-none text-slate-700 dark:text-gray-200 placeholder:text-slate-400 font-medium transition-all duration-300 focus:bg-white dark:focus:bg-gray-800 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 dark:focus:ring-primary-500/20"
                                :placeholder="__('••••••••')"
                                v-model="form.password"
                                required
                            />
                            <button
                                type="button"
                                @click="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none transition-colors"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="mt-2 text-sm text-red-600 ml-1">{{ form.errors.password }}</div>
                    </div>

                    <div class="flex items-center px-1">
                        <input
                            type="checkbox"
                            id="remember"
                            v-model="form.remember"
                            class="w-4 h-4 rounded text-primary-600 border-slate-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 highlight-primary focus:ring-primary-500 cursor-pointer transition-all"
                        />
                        <label for="remember" class="ml-2 text-sm text-slate-600 dark:text-slate-400 cursor-pointer select-none font-medium">{{ __('Stay signed in') }}</label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || isAuthenticating"
                        :class="{'btn-loading': form.processing || isAuthenticating}"
                        class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl shadow-xl shadow-primary-200 dark:shadow-none transform transition-all active:scale-95 flex items-center justify-center space-x-2 disabled:opacity-75 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing || isAuthenticating">{{ __('Logging in...') }}</span>
                        <span v-else>{{ __('Login to Platform') }}</span>
                        <svg v-if="!(form.processing || isAuthenticating)" class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        <svg v-else class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </button>
                </form>


                <!-- Footer -->
                <p class="text-center mt-12 text-slate-500 dark:text-slate-400 text-sm">
                    {{ __('New to our platform?') }} 
                    <Link :href="register.url()" class="font-bold text-primary-600 hover:text-primary-700 dark:hover:text-primary-400">{{ __('Create an account') }}</Link>
                </p>

            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

/* .branded-bg removed in favor of Tailwind classes */

.animate-float {
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.input-focus {
    transition: all 0.3s ease;
}

.input-focus:focus {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.1);
}

.btn-loading {
    pointer-events: none;
    opacity: 0.8;
}
</style>
