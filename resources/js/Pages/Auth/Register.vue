<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ElMessage } from 'element-plus';
import { View, Hide, ArrowRight, CircleCheckFilled } from '@element-plus/icons-vue';
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

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

function handleGoogleSignUp() {
    ElMessage.info('Google Workspace sign-up is not configured yet.');
}

const passwordStrength = computed(() => {
    const password = form.password;
    if (!password) return { score: 0, label: '', color: '#cbd5e1' };

    let score = 0;
    if (password.length >= 8) score += 1;
    if (password.length >= 12) score += 1;
    if (/[a-z]/.test(password)) score += 1;
    if (/[A-Z]/.test(password)) score += 1;
    if (/\d/.test(password)) score += 1;
    if (/[@$!%*#?&^()_+\-=\[\]{};':"\\|,.<>\/?`~]/.test(password)) score += 1;

    if (score <= 2) return { score, label: 'Weak', color: '#ef4444' };
    if (score <= 4) return { score, label: 'Fair', color: '#f59e0b' };
    if (score <= 5) return { score, label: 'Good', color: '#22c55e' };
    return { score, label: 'Strong', color: '#0d631b' };
});

const strengthBars = computed(() => Math.min(4, Math.ceil((passwordStrength.value.score / 6) * 4)));

const passwordMatch = computed(() => {
    if (!form.password_confirmation) return { state: 'empty', text: '' };
    if (form.password === form.password_confirmation) {
        if (form.password.length >= 8) return { state: 'match', text: 'Passwords match' };
        return { state: 'empty', text: '' };
    }
    return { state: 'mismatch', text: 'Passwords do not match' };
});

const corridorStats = [
    { label: 'Robusta Screen 18', value: '$4.20', unit: '/kg', change: '▲ +2.4%' },
    { label: 'Bugisu Arabica AA', value: '$5.10', unit: '/kg', change: '▲ +1.2%' },
    { label: 'Escrow Settlement', value: '< 24h', unit: '', change: 'Stanbic Custody' },
];
</script>

<template>
    <Head title="Register" />

    <div class="auth-shell min-h-screen lg:flex">
        <!-- LEFT: Brand panel -->
        <div class="auth-brand hidden lg:flex lg:flex-col lg:justify-between lg:flex-[0_0_45%] lg:max-w-[45%] px-14 py-12 text-white relative overflow-hidden">
            <div class="flex items-center mb-4 relative z-10">
                <Link :href="route('home')" class="flex items-center gap-2.5 no-underline">
                    <span class="auth-mark-wrap">
                        <ApplicationMark class="h-8 w-8" />
                    </span>
                    <div>
                        <div class="font-bold text-white text-lg leading-none">Bean Origin</div>
                        <div class="font-mono text-[11px] tracking-[0.1em] uppercase text-white/50 mt-1">Digital Exchange</div>
                    </div>
                </Link>
            </div>

            <div class="my-auto py-6 relative z-10">
                <div class="font-mono text-[#f6c453] text-xs uppercase tracking-[0.12em] mb-3">Physical Coffee · Institutional Trust</div>
                <h1 class="text-[36px] leading-[1.15] font-bold text-white mb-4">The digital exchange for physical coffee.</h1>
                <p class="text-white/60 text-base leading-relaxed max-w-md mb-6">
                    Discover verified lots, connect directly with East African dry mills and cooperatives, and settle trades through automated escrow custody.
                </p>

                <div class="auth-pulse-card mt-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-mono text-[11px] uppercase text-white/50">Real-Time Corridor Depth</span>
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-[#a3f69c]/10 text-[#a3f69c] border border-[#a3f69c]/25">100% Geofenced</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div v-for="(stat, i) in corridorStats" :key="stat.label" :class="i > 0 ? 'border-l border-white/10 pl-3' : ''">
                            <div class="font-mono text-[11px] text-white/50">{{ stat.label }}</div>
                            <div class="font-mono font-bold text-white text-base mt-0.5">{{ stat.value }}<span class="text-white/50 text-xs font-normal">{{ stat.unit }}</span></div>
                            <div class="font-mono text-[11px] text-[#a3f69c] mt-0.5">{{ stat.change }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-end justify-between text-white/50 text-xs border-t border-white/10 pt-4 relative z-10">
                <div>
                    <span class="font-semibold text-white block">Bean Origin Physical Commodity Exchange</span>
                    <div class="font-mono text-[11px] mt-1">Regulated under UCDA Standards · Stanbic Tier-1 Escrow</div>
                </div>
                <div class="font-mono text-[11px] whitespace-nowrap">v2.6 Institutional</div>
            </div>
        </div>

        <!-- RIGHT: Form panel -->
        <div class="auth-form-side flex-1 lg:flex-[0_0_55%] lg:max-w-[55%] flex flex-col bg-white px-5 py-8 sm:px-10 lg:px-20 lg:py-14">
            <!-- Mobile brand header -->
            <div class="lg:hidden flex items-center justify-between pb-4 mb-3 border-b border-slate-100">
                <Link :href="route('home')" class="flex items-center gap-2 no-underline">
                    <span class="auth-mark-wrap auth-mark-wrap--dark">
                        <ApplicationMark class="h-7 w-7" />
                    </span>
                    <span class="font-bold text-lg text-[#181d17]">Bean Origin</span>
                </Link>
            </div>

            <div class="my-auto w-full max-w-[480px] mx-auto">
                <div v-if="Object.keys(form.errors).length > 0" class="auth-alert auth-alert--error">
                    <div>
                        <strong>Please correct the following:</strong>
                        <ul class="list-disc list-inside mt-1 space-y-0.5">
                            <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                        </ul>
                    </div>
                </div>

                <div class="mb-5">
                    <h2 class="text-[28px] font-bold text-[#181d17] mb-1 tracking-tight">Create your account</h2>
                    <p class="text-slate-500 text-[15px] m-0">Join Bean Origin and start trading coffee digitally.</p>
                </div>

                <button type="button" class="auth-google-btn" @click="handleGoogleSignUp">
                    <svg width="18" height="18" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
                        <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.16 0 9.97 0 12c0 2.03.45 3.84 1.25 5.42l4.03-3.15z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                    </svg>
                    Continue with Google Workspace
                </button>

                <div class="auth-divider"><span>or register with email</span></div>

                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="auth-label">First Name</label>
                            <input
                                id="first_name"
                                v-model="form.first_name"
                                type="text"
                                class="auth-field mt-1.5"
                                autofocus
                                required
                                autocomplete="given-name"
                                placeholder="Moses"
                            />
                            <InputError class="mt-1.5" :message="form.errors.first_name" />
                        </div>
                        <div>
                            <label for="last_name" class="auth-label">Last Name</label>
                            <input
                                id="last_name"
                                v-model="form.last_name"
                                type="text"
                                class="auth-field mt-1.5"
                                required
                                autocomplete="family-name"
                                placeholder="Kato"
                            />
                            <InputError class="mt-1.5" :message="form.errors.last_name" />
                        </div>
                    </div>

                    <div>
                        <label for="telephone" class="auth-label">Telephone</label>
                        <input
                            id="telephone"
                            v-model="form.telephone"
                            type="tel"
                            class="auth-field mt-1.5"
                            required
                            autocomplete="tel"
                            placeholder="+256 752 567 534"
                        />
                        <InputError class="mt-1.5" :message="form.errors.telephone" />
                    </div>

                    <div>
                        <label for="email" class="auth-label">Email Address</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="auth-field mt-1.5"
                            required
                            autocomplete="username"
                            placeholder="kato.moses@origincoffee.ug"
                        />
                        <InputError class="mt-1.5" :message="form.errors.email" />
                    </div>

                    <div>
                        <label for="password" class="auth-label">Password</label>
                        <div class="relative mt-1.5">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="auth-field pr-11"
                                required
                                autocomplete="new-password"
                                placeholder="Create a password"
                            />
                            <button type="button" class="auth-password-toggle" :aria-label="showPassword ? 'Hide password' : 'Show password'" @click="showPassword = !showPassword">
                                <el-icon :size="16"><component :is="showPassword ? Hide : View" /></el-icon>
                            </button>
                        </div>

                        <div v-if="form.password" class="mt-2">
                            <div class="grid grid-cols-4 gap-1.5">
                                <div v-for="n in 4" :key="n" class="h-1 rounded-full transition-colors" :style="{ backgroundColor: n <= strengthBars ? passwordStrength.color : '#e2e8f0' }"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1.5">
                                <span>At least 8 characters, mixed case, numbers &amp; symbols.</span>
                                <span class="font-semibold" :style="{ color: passwordStrength.color }">{{ passwordStrength.label }}</span>
                            </div>
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="auth-label">Confirm Password</label>
                        <div class="relative mt-1.5">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                class="auth-field pr-11"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm your password"
                            />
                            <button type="button" class="auth-password-toggle" :aria-label="showPasswordConfirmation ? 'Hide password' : 'Show password'" @click="showPasswordConfirmation = !showPasswordConfirmation">
                                <el-icon :size="16"><component :is="showPasswordConfirmation ? Hide : View" /></el-icon>
                            </button>
                        </div>
                        <div v-if="passwordMatch.state !== 'empty'" class="flex items-center gap-1.5 text-xs mt-1.5" :class="passwordMatch.state === 'match' ? 'text-[#0d631b]' : 'text-red-600'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="passwordMatch.state === 'match' ? 'bg-[#0d631b]' : 'bg-red-500'"></span>
                            {{ passwordMatch.text }}
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                    </div>

                    <label v-if="$page.props.jetstream?.hasTermsAndPrivacyPolicyFeature" class="flex items-start gap-2.5 pt-1 cursor-pointer select-none">
                        <input id="terms" v-model="form.terms" type="checkbox" name="terms" required class="auth-checkbox mt-0.5" />
                        <span class="text-xs text-slate-600 leading-normal">
                            I agree to the Bean Origin
                            <a target="_blank" :href="route('terms.show')" class="text-[#0d631b] font-semibold no-underline hover:underline">Terms of Service</a>
                            and
                            <a target="_blank" :href="route('policy.show')" class="text-[#0d631b] font-semibold no-underline hover:underline">Privacy Policy</a>.
                        </span>
                    </label>
                    <InputError :message="form.errors.terms" />

                    <SubmitButton class="auth-submit mt-2" :loading="form.processing" :disabled="form.processing">
                        Create Account <el-icon :size="14"><ArrowRight /></el-icon>
                    </SubmitButton>
                </form>

                <div class="text-center mt-6 pt-1">
                    <span class="text-slate-500 text-[15px]">Already have a Bean Origin account?</span>
                    <Link :href="route('login')" class="text-[#0d631b] font-semibold no-underline hover:underline ml-1">Sign In</Link>
                </div>

                <div class="text-center mt-6 pt-1">
                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-400">
                        <el-icon :size="13" class="text-[#0d631b]"><CircleCheckFilled /></el-icon>
                        Your connection is secure. Encrypted session tokens.
                    </span>
                </div>
            </div>

            <div class="text-center text-slate-400 text-sm pt-6 mt-auto border-t border-slate-100">
                <p class="text-[13px] mb-1">
                    By continuing, you agree to Bean Origin's
                    <a href="#" class="text-[#181d17] font-semibold no-underline">Terms of Exchange</a>
                    and
                    <a href="#" class="text-[#181d17] font-semibold no-underline">Privacy Policy</a>.
                </p>
                <p class="font-mono text-[11px] text-slate-400 m-0">© 2026 Bean Origin Physical Commodity Exchange Ltd. All rights reserved.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.auth-shell {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.auth-brand {
    background:
        radial-gradient(circle at 15% 15%, rgba(163, 246, 156, 0.10), transparent 45%),
        radial-gradient(circle at 85% 85%, rgba(163, 246, 156, 0.06), transparent 50%),
        linear-gradient(160deg, #121611 0%, #071712 100%);
}

.auth-mark-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    border-radius: 10px;
    background: #ffffff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}
.auth-mark-wrap--dark {
    width: 34px;
    height: 34px;
}

.auth-pulse-card {
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
}

.auth-alert {
    border-radius: 8px;
    font-size: 14px;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    margin-bottom: 1.25rem;
}
.auth-alert--error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}

.auth-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.auth-field {
    width: 100%;
    height: 46px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    padding: 0.7rem 0.9rem;
    font-size: 15px;
    color: #0f172a;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
    box-sizing: border-box;
}
.auth-field::placeholder {
    color: #94a3b8;
    font-weight: 400;
}
.auth-field:focus {
    border-color: #0d631b;
    box-shadow: 0 0 0 3px rgba(13, 99, 27, 0.12);
}

.auth-password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    padding: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: color 0.15s;
}
.auth-password-toggle:hover {
    color: #181d17;
}

.auth-checkbox {
    width: 16px;
    height: 16px;
    accent-color: #0d631b;
}

.auth-google-btn {
    height: 46px;
    width: 100%;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #181d17;
    font-weight: 600;
    font-size: 14px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.65rem;
    cursor: pointer;
    transition: all 0.15s ease;
}
.auth-google-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.auth-divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 1.25rem 0;
    color: #94a3b8;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}
.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #e2e8f0;
}
.auth-divider span {
    padding: 0 0.85rem;
}

:deep(.auth-submit.submit-button.el-button) {
    background: #0d631b !important;
    border-color: #0d631b !important;
    color: #ffffff !important;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
    text-transform: none !important;
    letter-spacing: 0 !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    height: 48px;
    border-radius: 8px !important;
}
:deep(.auth-submit.submit-button.el-button:hover),
:deep(.auth-submit.submit-button.el-button:focus-visible) {
    background: #0a4f15 !important;
    border-color: #0a4f15 !important;
    color: #ffffff !important;
}
:deep(.auth-submit .submit-button__content) {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
</style>
