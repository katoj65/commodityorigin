<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';

const form = useForm({
    first_name: '',
    last_name: '',
    role: '',
    telephone: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register">
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
                        :href="route('login')"
                        class="rounded-sm border border-white/[0.14] px-3.5 py-1.5 font-mono text-[9px] uppercase tracking-[0.12em] text-cream/72 no-underline transition-all hover:border-gold hover:text-gold"
                    >
                        Sign in
                    </Link>
                </div>
            </div>
        </nav>

        <div class="relative z-10 mx-auto grid min-h-[calc(100vh-60px)] max-w-7xl gap-10 px-6 py-10 lg:grid-cols-[1fr_1.05fr] lg:px-10">
            <section class="flex flex-col justify-center">
                <div class="inline-flex w-fit items-center gap-2 rounded-sm border border-gold/25 bg-gold/12 px-3.5 py-1.5">
                    <span class="font-mono text-[9px] uppercase tracking-[0.16em] text-gold">
                        Open account
                    </span>
                </div>

                <h1 class="mt-6 font-display text-cream" style="font-size:clamp(40px,5vw,78px);line-height:0.95;letter-spacing:-0.03em;">
                    <span class="block text-cream/88">Join Uganda's</span>
                    <span class="block text-gold">Coffee Origin</span>
                </h1>

                <p class="mt-6 max-w-xl text-[15px] leading-relaxed text-cream/72">
                    Create your marketplace profile to buy, sell, trace, and verify export-ready coffee lots through one unified exchange.
                </p>

                <div class="mt-8 space-y-4">
                    <div class="rounded-sm border border-white/[0.06] bg-surface px-4 py-4">
                        <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-gold/80">Why register</div>
                        <div class="mt-2 font-display text-[24px] font-bold tracking-tight text-cream">Trade with visibility</div>
                        <p class="mt-2 text-[13px] leading-relaxed text-cream/58">
                            View live lots, verified grades, shipment-ready records, and structured market data in one place.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-sm border border-white/[0.06] bg-surface2/75 px-4 py-4">
                            <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-gold/80">Buyers</div>
                            <p class="mt-2 text-[13px] leading-relaxed text-cream/62">
                                Compare verified lots by score, region, process, volume, and price.
                            </p>
                        </div>
                        <div class="rounded-sm border border-white/[0.06] bg-surface2/75 px-4 py-4">
                            <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-gold/80">Suppliers</div>
                            <p class="mt-2 text-[13px] leading-relaxed text-cream/62">
                                Present your inventory with traceability details buyers can trust.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 rounded-sm border border-white/[0.06] bg-bg/30 px-4 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-sm border border-gold/20 bg-gold/12 text-gold">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                <path d="M12 2v20M2 12h20" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-mono text-[9px] uppercase tracking-[0.14em] text-cream/46">Account profile</div>
                            <div class="mt-1 text-[13px] leading-relaxed text-cream/70">
                                Use a role and telephone number that match how you operate in the marketplace.
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="flex items-center justify-center">
                <div class="w-full max-w-2xl rounded-[26px] border border-white/[0.08] bg-surface/92 p-6 shadow-[0_40px_100px_rgba(0,0,0,0.42)] backdrop-blur-xl sm:p-8">
                    <div class="mb-8 flex items-start justify-between gap-4">
                        <div>
                            <p class="font-mono text-[9px] uppercase tracking-[0.16em] text-gold/80">
                                Registration
                            </p>
                            <h2 class="mt-3 font-display text-[34px] font-bold leading-none tracking-[-0.03em] text-cream">
                                Create your account
                            </h2>
                            <p class="mt-3 max-w-lg text-[14px] leading-relaxed text-cream/62">
                                Set up your profile with the same identity fields used in the trading database.
                            </p>
                        </div>

                        <Link
                            :href="route('home')"
                            class="rounded-sm border border-white/[0.1] px-3 py-2 font-mono text-[9px] uppercase tracking-[0.14em] text-cream/52 no-underline transition-colors hover:border-gold/30 hover:text-gold"
                        >
                            Home
                        </Link>
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="auth-label">First name</label>
                                <input
                                    id="first_name"
                                    v-model="form.first_name"
                                    type="text"
                                    class="auth-input mt-2"
                                    autofocus
                                    autocomplete="given-name"
                                    placeholder="Joshua"
                                />
                                <InputError class="mt-2" :message="form.errors.first_name" />
                            </div>

                            <div>
                                <label for="last_name" class="auth-label">Last name</label>
                                <input
                                    id="last_name"
                                    v-model="form.last_name"
                                    type="text"
                                    class="auth-input mt-2"
                                    autocomplete="family-name"
                                    placeholder="Kato"
                                />
                                <InputError class="mt-2" :message="form.errors.last_name" />
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="role" class="auth-label">Role</label>
                                <input
                                    id="role"
                                    v-model="form.role"
                                    type="text"
                                    class="auth-input mt-2"
                                    autocomplete="organization-title"
                                    placeholder="buyer, farmer, exporter..."
                                />
                                <InputError class="mt-2" :message="form.errors.role" />
                            </div>

                            <div>
                                <label for="telephone" class="auth-label">Telephone</label>
                                <input
                                    id="telephone"
                                    v-model="form.telephone"
                                    type="tel"
                                    class="auth-input mt-2"
                                    autocomplete="tel"
                                    placeholder="+256752567534"
                                />
                                <InputError class="mt-2" :message="form.errors.telephone" />
                            </div>
                        </div>

                        <div>
                            <label for="email" class="auth-label">Email address</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="auth-input mt-2"
                                autocomplete="username"
                                placeholder="joshua@example.com"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="password" class="auth-label">Password</label>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="auth-input mt-2"
                                    autocomplete="new-password"
                                    placeholder="Create a password"
                                />
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div>
                                <label for="password_confirmation" class="auth-label">Confirm password</label>
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="auth-input mt-2"
                                    autocomplete="new-password"
                                    placeholder="Repeat your password"
                                />
                                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                            </div>
                        </div>

                        <label
                            v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                            class="flex items-start gap-3 rounded-sm border border-white/[0.06] bg-bg/35 px-3 py-3"
                        >
                            <input
                                id="terms"
                                v-model="form.terms"
                                type="checkbox"
                                name="terms"
                                class="auth-check mt-0.5"
                            />
                            <span class="text-[13px] leading-relaxed text-cream/68">
                                I agree to the
                                <a target="_blank" :href="route('terms.show')" class="text-gold no-underline transition-colors hover:text-gold2">Terms of Service</a>
                                and
                                <a target="_blank" :href="route('policy.show')" class="text-gold no-underline transition-colors hover:text-gold2">Privacy Policy</a>.
                            </span>
                        </label>
                        <InputError class="mt-2" :message="form.errors.terms" />

                        <button
                            type="submit"
                            class="auth-button"
                            :class="{ 'cursor-not-allowed opacity-60': form.processing }"
                            :disabled="form.processing"
                        >
                            <span>Create account</span>
                            <span class="font-mono text-[10px] tracking-[0.14em]">/REGISTER</span>
                        </button>
                    </form>

                    <div class="mt-6 flex items-center justify-between gap-4 border-t border-white/[0.06] pt-5">
                        <p class="text-[13px] text-cream/55">
                            Already part of the exchange?
                        </p>
                        <Link
                            :href="route('login')"
                            class="font-mono text-[10px] uppercase tracking-[0.14em] text-gold no-underline transition-colors hover:text-gold2"
                        >
                            Sign in instead
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
