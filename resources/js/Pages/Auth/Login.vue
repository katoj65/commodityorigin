<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="auth-shell min-h-screen bg-[#121611] text-[#eef2e8]">
        <header class="fixed top-0 w-full z-50 bg-[#121611]/90 backdrop-blur-xl border-b border-[#bfcaba]/10">
            <div class="h-20 max-w-7xl mx-auto px-4 md:px-8 flex items-center justify-between">
                <Link :href="route('home')" class="flex items-center gap-2 md:gap-3 no-underline">
                    <ApplicationMark class="h-9 w-9 md:h-10 md:w-10 flex-shrink-0" />
                    <span class="text-base md:text-lg font-bold text-white tracking-tight whitespace-nowrap">Bean Origin</span>
                </Link>
                <div class="flex items-center gap-4">
                    <span class="hidden sm:inline text-[11px] text-[#bfcaba] uppercase tracking-[0.15em]">New to the exchange?</span>
                    <Link
                        :href="route('register')"
                        class="bg-[#0d631b] text-white px-4 md:px-5 py-2.5 rounded text-xs font-semibold tracking-[0.02em] uppercase hover:bg-[#2e7d32] transition-all no-underline whitespace-nowrap"
                    >Create Account</Link>
                </div>
            </div>
        </header>

        <div class="relative z-10 mx-auto grid min-h-screen max-w-7xl gap-10 px-4 md:px-8 pt-32 pb-16 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
            <section class="flex flex-col justify-center">
                <h1 class="text-[40px] md:text-[56px] leading-[1.1] text-white tracking-tight font-light">
                    Welcome back to <br />
                    <span class="text-[#a3f69c] font-semibold italic">Bean Origin.</span>
                </h1>

                <p class="mt-6 max-w-lg text-lg leading-relaxed text-[#bfcaba]">
                    Sign in to continue trading verified coffee with live pricing and full origin traceability.
                </p>
            </section>

            <section class="flex items-center justify-center">
                <div class="w-full max-w-xl bg-[#1a2018] rounded-xl shadow-2xl p-6 sm:p-8">
                    <p class="text-[11px] text-[#a3f69c] uppercase tracking-[0.2em]">Sign In</p>
                    <h2 class="mt-3 text-[32px] font-semibold leading-none tracking-tight text-white">Welcome back</h2>
                    <p class="mt-3 max-w-md text-sm leading-relaxed text-[#bfcaba]">
                        Continue to your trading workspace and account dashboard.
                    </p>

                    <div v-if="status" class="mt-5 rounded-lg bg-[#10B981]/10 px-4 py-3 text-sm text-[#10B981]">
                        {{ status }}
                    </div>

                    <form class="mt-8 space-y-5" @submit.prevent="submit">
                        <div>
                            <label for="email" class="auth-label">Email address</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="auth-input mt-2"
                                autofocus
                                required
                                autocomplete="username"
                                placeholder="joshua@example.com"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between gap-4">
                                <label for="password" class="auth-label">Password</label>
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-[11px] text-[#a3f69c]/80 uppercase tracking-[0.1em] no-underline transition-colors hover:text-[#a3f69c]"
                                >
                                    Forgot password?
                                </Link>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="auth-input mt-2"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <label class="flex items-center gap-3 bg-[#121611] rounded-lg px-3 py-3">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                name="remember"
                                class="auth-check"
                            />
                            <span class="text-sm text-[#bfcaba]">Keep this session signed in on this device</span>
                        </label>

                        <SubmitButton class="mt-6" :loading="form.processing" :disabled="form.processing">
                            Login
                        </SubmitButton>
                    </form>

                    <div class="mt-6 flex items-center justify-between gap-4 border-t border-[#707a6c]/15 pt-5">
                        <p class="text-sm text-[#bfcaba]">
                            New to Bean Origin?
                        </p>
                        <Link
                            :href="route('register')"
                            class="text-xs text-[#a3f69c] uppercase tracking-[0.1em] no-underline transition-colors hover:text-[#88d982]"
                        >
                            Create an account
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<style>
.auth-shell {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    position: relative;
}

.auth-label {
    display: block;
    font-size: 11px;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(191, 202, 186, 0.85);
}

.auth-input {
    width: 100%;
    border: 1px solid rgba(112, 122, 108, 0.3);
    background: #121611;
    color: #eef2e8;
    border-radius: 8px;
    padding: 0.95rem 1rem;
    outline: none;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.auth-input::placeholder {
    color: rgba(191, 202, 186, 0.4);
}

.auth-input:focus {
    border-color: rgba(163, 246, 156, 0.6);
    box-shadow: 0 0 0 3px rgba(163, 246, 156, 0.12);
}

.auth-check {
    width: 1rem;
    height: 1rem;
    accent-color: #a3f69c;
}

.auth-shell .submit-button.el-button {
    background: #a3f69c !important;
    border-color: #a3f69c !important;
    color: #002204 !important;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    letter-spacing: 0.05em !important;
    border-radius: 8px !important;
    margin-top: 20px !important;
}

.auth-shell .submit-button.el-button:hover,
.auth-shell .submit-button.el-button:focus-visible {
    background: #88d982 !important;
    border-color: #88d982 !important;
    color: #002204 !important;
}

.auth-shell .submit-button.el-button .el-icon.is-loading {
    color: #002204 !important;
}
</style>
