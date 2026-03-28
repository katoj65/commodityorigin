<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';

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
    <Head title="Log in">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@400;500;600;700;800&family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="auth-shell min-h-screen bg-bg text-cream">
        <div class="absolute inset-0 pointer-events-none auth-radial" />
        <div class="absolute inset-0 pointer-events-none auth-grid" />

        <nav class="relative z-10 border-b border-white/[0.06] bg-bg/88 backdrop-blur-xl">
            <div class="mx-auto flex h-[60px] max-w-7xl items-center justify-between px-6 lg:px-10">
                <Link :href="route('home')" class="flex items-center gap-3 no-underline">
                    <ApplicationMark class="h-8 w-8 flex-shrink-0" />
                    <span class="font-display text-base font-bold tracking-tight text-cream">
                        Commodity <span class="text-gold">Origin</span>
                    </span>
                </Link>

                <div class="flex items-center gap-3">
                    <span class="font-mono text-[9px] uppercase tracking-[0.18em] text-cream/45">
                        Uganda coffee exchange
                    </span>
                    <Link
                        :href="route('register')"
                        class="rounded-sm border border-gold bg-gold px-3.5 py-1.5 font-mono text-[9px] font-medium uppercase tracking-[0.12em] text-bg no-underline transition-all hover:bg-gold2"
                    >
                        Open account
                    </Link>
                </div>
            </div>
        </nav>

        <div class="relative z-10 mx-auto grid min-h-[calc(100vh-60px)] max-w-7xl gap-10 px-6 py-10 lg:grid-cols-[1.05fr_0.95fr] lg:px-10">
            <section class="flex flex-col justify-center">
                <div class="inline-flex w-fit items-center gap-2 rounded-sm border border-gold/25 bg-gold/12 px-3.5 py-1.5">
                    <span class="font-mono text-[9px] uppercase tracking-[0.16em] text-gold">
                        Member access
                    </span>
                </div>

                <h1 class="mt-6 font-display text-cream" style="font-size:clamp(40px,5vw,78px);line-height:0.95;letter-spacing:-0.03em;">
                    <span class="block text-cream/88">Return to</span>
                    <span class="block text-gold">Origin</span>
                </h1>

                <p class="mt-6 max-w-xl text-[15px] leading-relaxed text-cream/72">
                    Access verified lots, live pricing, origin intelligence, and the full farm-to-export record from Uganda's coffee marketplace.
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-sm border border-white/[0.06] bg-surface px-4 py-4">
                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-gold/80">Verified lots</div>
                        <div class="mt-2 font-display text-3xl font-bold text-cream">312</div>
                        <p class="mt-1 text-[12px] leading-relaxed text-cream/58">UCDA-graded inventory available for direct trade.</p>
                    </div>
                    <div class="rounded-sm border border-white/[0.06] bg-surface px-4 py-4">
                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-gold/80">Export ready</div>
                        <div class="mt-2 font-display text-3xl font-bold text-cream">47K</div>
                        <p class="mt-1 text-[12px] leading-relaxed text-cream/58">Kilograms currently visible in the exchange pipeline.</p>
                    </div>
                    <div class="rounded-sm border border-white/[0.06] bg-surface px-4 py-4">
                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-gold/80">Traceability</div>
                        <div class="mt-2 font-display text-3xl font-bold text-cream">100%</div>
                        <p class="mt-1 text-[12px] leading-relaxed text-cream/58">Origin records preserved from farm registration to shipment.</p>
                    </div>
                </div>

                <div class="mt-8 flex items-center gap-3 rounded-sm border border-white/[0.06] bg-surface2/75 px-4 py-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-sm border border-gold/20 bg-gold/12 text-gold">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path d="M12 3v18M3 12h18" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-cream/46">Market status</div>
                        <div class="mt-1 font-mono text-[11px] font-medium tracking-[0.08em] text-up">Live pricing feed active</div>
                    </div>
                </div>
            </section>

            <section class="flex items-center justify-center">
                <div class="w-full max-w-xl rounded-[26px] border border-white/[0.08] bg-surface/92 p-6 shadow-[0_40px_100px_rgba(0,0,0,0.42)] backdrop-blur-xl sm:p-8">
                    <div class="mb-8 flex items-start justify-between gap-4">
                        <div>
                            <p class="font-mono text-[9px] uppercase tracking-[0.16em] text-gold/80">
                                Sign in
                            </p>
                            <h2 class="mt-3 font-display text-[34px] font-bold leading-none tracking-[-0.03em] text-cream">
                                Welcome back
                            </h2>
                            <p class="mt-3 max-w-md text-[14px] leading-relaxed text-cream/62">
                                Continue to your trading workspace and account dashboard.
                            </p>
                        </div>

                        <Link
                            :href="route('home')"
                            class="rounded-sm border border-white/[0.1] px-3 py-2 font-mono text-[9px] uppercase tracking-[0.14em] text-cream/52 no-underline transition-colors hover:border-gold/30 hover:text-gold"
                        >
                            Home
                        </Link>
                    </div>

                    <div v-if="status" class="mb-5 rounded-sm border border-green/25 bg-green/10 px-4 py-3 text-sm text-green">
                        {{ status }}
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div>
                            <label for="email" class="auth-label">Email address</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="auth-input mt-2"
                                autofocus
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
                                    class="font-mono text-[9px] uppercase tracking-[0.14em] text-gold/80 no-underline transition-colors hover:text-gold"
                                >
                                    Forgot password?
                                </Link>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="auth-input mt-2"
                                autocomplete="current-password"
                                placeholder="Enter your password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <label class="flex items-center gap-3 rounded-sm border border-white/[0.06] bg-bg/35 px-3 py-3">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                name="remember"
                                class="auth-check"
                            />
                            <span class="text-[13px] text-cream/72">Keep this session signed in on this device</span>
                        </label>

                        <button
                            type="submit"
                            class="auth-button"
                            :class="{ 'cursor-not-allowed opacity-60': form.processing }"
                            :disabled="form.processing"
                        >
                            <span>Enter marketplace</span>
                            <span class="font-mono text-[10px] tracking-[0.14em]">/LOGIN</span>
                        </button>
                    </form>

                    <div class="mt-6 flex items-center justify-between gap-4 border-t border-white/[0.06] pt-5">
                        <p class="text-[13px] text-cream/55">
                            New to Commodity Origin?
                        </p>
                        <Link
                            :href="route('register')"
                            class="font-mono text-[10px] uppercase tracking-[0.14em] text-gold no-underline transition-colors hover:text-gold2"
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
    font-family: 'Manrope', sans-serif;
    position: relative;
    overflow: hidden;
}

.auth-shell .font-display {
    font-family: 'Bricolage Grotesque', sans-serif;
}

.auth-shell .font-mono {
    font-family: 'IBM Plex Mono', monospace;
}

.auth-radial {
    background:
        radial-gradient(circle at 20% 20%, rgba(200, 134, 42, 0.14), transparent 28%),
        radial-gradient(circle at 82% 18%, rgba(82, 183, 136, 0.1), transparent 26%),
        radial-gradient(circle at 60% 78%, rgba(224, 155, 58, 0.1), transparent 34%);
}

.auth-grid {
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
    background-size: 40px 40px;
    mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.45), transparent 85%);
}

.auth-label {
    display: block;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: rgba(245, 234, 211, 0.68);
}

.auth-input {
    width: 100%;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(9, 11, 8, 0.68);
    color: #f5ead3;
    border-radius: 6px;
    padding: 0.95rem 1rem;
    outline: none;
    transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
}

.auth-input::placeholder {
    color: rgba(245, 234, 211, 0.34);
}

.auth-input:focus {
    border-color: rgba(200, 134, 42, 0.72);
    box-shadow: 0 0 0 3px rgba(200, 134, 42, 0.14);
    background: rgba(9, 11, 8, 0.9);
}

.auth-check {
    width: 1rem;
    height: 1rem;
    accent-color: #c8862a;
}

.auth-button {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    border: 1px solid #c8862a;
    background: #c8862a;
    color: #0e0b07;
    border-radius: 6px;
    padding: 0.95rem 1rem;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-weight: 600;
    transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease;
}

.auth-button:hover {
    background: #e09b3a;
    border-color: #e09b3a;
    transform: translateY(-1px);
}
</style>
