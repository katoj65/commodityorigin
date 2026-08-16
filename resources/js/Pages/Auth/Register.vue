<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import SubmitButton from '@/Components/Button/SubmitButton.vue';

const form = useForm({
    first_name: '',
    last_name: '',
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
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    </Head>

    <div class="auth-shell min-h-screen bg-[#121611] text-[#eef2e8]">
        <header class="fixed top-0 w-full z-50 bg-[#121611]/90 backdrop-blur-xl border-b border-[#bfcaba]/10">
            <div class="h-20 max-w-7xl mx-auto px-4 md:px-8 flex items-center justify-between">
                <Link :href="route('home')" class="flex items-center gap-2 md:gap-3 no-underline">
                    <ApplicationMark class="h-9 w-9 md:h-10 md:w-10 flex-shrink-0" />
                    <span class="text-base md:text-lg font-bold text-white tracking-tight whitespace-nowrap">Bean Origin</span>
                </Link>
                <div class="flex items-center gap-4">
                    <span class="hidden sm:inline text-[11px] text-[#bfcaba] uppercase tracking-[0.15em]">Already a member?</span>
                    <Link
                        :href="route('login')"
                        class="bg-[#1a2018] text-white px-4 md:px-5 py-2.5 rounded text-xs font-semibold tracking-[0.02em] uppercase hover:bg-[#20281e] transition-all border border-[#707a6c]/30 no-underline whitespace-nowrap"
                    >Sign In</Link>
                </div>
            </div>
        </header>

        <div class="relative z-10 mx-auto grid min-h-screen max-w-7xl gap-10 px-4 md:px-8 pt-32 pb-16 lg:grid-cols-[1fr_1.05fr] lg:items-center">
            <section class="flex flex-col justify-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-[#1a2018] rounded-full w-fit border border-[#0d631b]/30">
                    <span class="w-2 h-2 rounded-full bg-[#a3f69c]"></span>
                    <span class="text-[11px] text-[#a3f69c] uppercase tracking-[0.2em]">Open Account</span>
                </div>

                <h1 class="mt-6 text-[40px] md:text-[56px] leading-[1.1] text-white tracking-tight font-light">
                    Join the Digital <br />
                    <span class="text-[#a3f69c] font-semibold italic">Coffee Exchange.</span>
                </h1>

                <p class="mt-6 max-w-lg text-lg leading-relaxed text-[#bfcaba]">
                    Create your marketplace profile to buy, sell, trace, and verify export-ready coffee lots through one unified exchange.
                </p>

                <div class="mt-10 space-y-4">
                    <div class="bg-[#1a2018]/50 backdrop-blur-md rounded-lg px-4 py-4">
                        <div class="text-[10px] text-[#a3f69c] uppercase tracking-[0.15em]">Why Register</div>
                        <div class="mt-2 text-xl font-semibold tracking-tight text-white">Trade with visibility</div>
                        <p class="mt-2 text-sm leading-relaxed text-[#bfcaba]">
                            View live lots, verified grades, shipment-ready records, and structured market data in one place.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="bg-[#1a2018]/50 backdrop-blur-md rounded-lg px-4 py-4">
                            <div class="text-[10px] text-[#a3f69c] uppercase tracking-[0.15em]">Buyers</div>
                            <p class="mt-2 text-sm leading-relaxed text-[#bfcaba]">
                                Compare verified lots by score, region, process, volume, and price.
                            </p>
                        </div>
                        <div class="bg-[#1a2018]/50 backdrop-blur-md rounded-lg px-4 py-4">
                            <div class="text-[10px] text-[#a3f69c] uppercase tracking-[0.15em]">Suppliers</div>
                            <p class="mt-2 text-sm leading-relaxed text-[#bfcaba]">
                                Present your inventory with traceability details buyers can trust.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex items-center gap-3 bg-[#1a2018]/50 backdrop-blur-md rounded-lg px-4 py-4">
                    <div class="w-2 h-2 rounded-full bg-[#a3f69c] auth-pulse flex-shrink-0"></div>
                    <div>
                        <div class="text-[10px] text-[#bfcaba] uppercase tracking-[0.15em]">Account Profile</div>
                        <div class="mt-1 text-sm text-[#bfcaba]">
                            Use your direct contact details so the marketplace can reach you reliably.
                        </div>
                    </div>
                </div>
            </section>

            <section class="flex items-center justify-center">
                <div class="w-full max-w-2xl bg-[#1a2018] rounded-xl shadow-2xl p-6 sm:p-8">
                    <p class="text-[11px] text-[#a3f69c] uppercase tracking-[0.2em]">Registration</p>
                    <h2 class="mt-3 text-[32px] font-semibold leading-none tracking-tight text-white">Create your account</h2>
                    <p class="mt-3 max-w-lg text-sm leading-relaxed text-[#bfcaba]">
                        Set up your profile with the same identity fields used in the trading database.
                    </p>

                    <form class="mt-8 space-y-5" @submit.prevent="submit">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="auth-label">First name</label>
                                <input
                                    id="first_name"
                                    v-model="form.first_name"
                                    type="text"
                                    class="auth-input mt-2"
                                    autofocus
                                    required
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
                                    required
                                    autocomplete="family-name"
                                    placeholder="Kato"
                                />
                                <InputError class="mt-2" :message="form.errors.last_name" />
                            </div>
                        </div>

                        <div>
                            <label for="telephone" class="auth-label">Telephone</label>
                            <input
                                id="telephone"
                                v-model="form.telephone"
                                type="tel"
                                class="auth-input mt-2"
                                required
                                autocomplete="tel"
                                placeholder="+256752567534"
                            />
                            <InputError class="mt-2" :message="form.errors.telephone" />
                        </div>

                        <div>
                            <label for="email" class="auth-label">Email address</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="auth-input mt-2"
                                required
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
                                    required
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
                                    required
                                    autocomplete="new-password"
                                    placeholder="Repeat your password"
                                />
                                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                            </div>
                        </div>

                        <label
                            v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                            class="flex items-start gap-3 bg-[#121611] rounded-lg px-3 py-3"
                        >
                            <input
                                id="terms"
                                v-model="form.terms"
                                type="checkbox"
                                name="terms"
                                required
                                class="auth-check mt-0.5"
                            />
                            <span class="text-sm leading-relaxed text-[#bfcaba]">
                                I agree to the
                                <a target="_blank" :href="route('terms.show')" class="text-[#a3f69c] no-underline transition-colors hover:text-[#88d982]">Terms of Service</a>
                                and
                                <a target="_blank" :href="route('policy.show')" class="text-[#a3f69c] no-underline transition-colors hover:text-[#88d982]">Privacy Policy</a>.
                            </span>
                        </label>
                        <InputError class="mt-2" :message="form.errors.terms" />

                        <SubmitButton class="mt-6" :loading="form.processing" :disabled="form.processing">
                            Create account
                        </SubmitButton>
                    </form>

                    <div class="mt-6 flex items-center justify-between gap-4 border-t border-[#707a6c]/15 pt-5">
                        <p class="text-sm text-[#bfcaba]">
                            Already part of the exchange?
                        </p>
                        <Link
                            :href="route('login')"
                            class="text-xs text-[#a3f69c] uppercase tracking-[0.1em] no-underline transition-colors hover:text-[#88d982]"
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
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    position: relative;
}

@keyframes authPulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(163, 246, 156, 0.5);
    }
    50% {
        box-shadow: 0 0 0 6px rgba(163, 246, 156, 0);
    }
}

.auth-shell .auth-pulse {
    animation: authPulse 2s ease-in-out infinite;
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
    font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
    letter-spacing: 0.05em !important;
    border-radius: 8px !important;
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
