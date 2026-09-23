<script setup>
import { computed, ref, watchEffect } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
    Plus, Star, StarFilled, Search, User, Message, Phone,
    OfficeBuilding, Location, Close, PhoneFilled, ChatDotRound,
} from '@element-plus/icons-vue';

const props = defineProps({
    contacts: { type: Array, default: () => [] },
});

/* ── Master–detail selection — real data from the Contact model, scoped
   server-side to the logged-in user (see ContactController::index). ──── */
const selectedId = ref(props.contacts[0]?.id ?? null);
const selectedContact = computed(() => props.contacts.find((c) => c.id === selectedId.value) || null);

function selectContact(contact) {
    selectedId.value = contact.id;
}

function initials(name) {
    return (name || '').split(' ').filter(Boolean).slice(0, 2).map((p) => p[0]?.toUpperCase()).join('') || '?';
}

/* ── Search + filters ────────────────────────────────────────────────────
   The Contact model only has name/email/phone/company/job_title/address/
   notes/is_favorite — no role taxonomy, verification, or status. "Type"
   below is derived from a real field (company presence) rather than
   invented: a contact with no company is treated as its own organization;
   one with a company is a person at that company. */
const search = ref('');
const typeFilter = ref('all');
const companyFilter = ref('all');
const favoritesOnly = ref(false);

function contactType(c) {
    return c.company ? 'person' : 'organization';
}

const typeTabs = [
    { key: 'all', label: 'All' },
    { key: 'person', label: 'People' },
    { key: 'organization', label: 'Organizations' },
];

function typeCount(key) {
    return key === 'all' ? props.contacts.length : props.contacts.filter((c) => contactType(c) === key).length;
}

const companies = computed(() => [...new Set(props.contacts.map((c) => c.company).filter(Boolean))].sort());

function matchesSearch(c) {
    const q = search.value.trim().toLowerCase();
    if (!q) return true;
    return [c.name, c.company, c.job_title, c.email, c.phone, c.address]
        .filter(Boolean)
        .some((f) => f.toLowerCase().includes(q));
}

const filteredContacts = computed(() => props.contacts
    .filter((c) => (
        matchesSearch(c)
        && (typeFilter.value === 'all' || contactType(c) === typeFilter.value)
        && (companyFilter.value === 'all' || c.company === companyFilter.value)
        && (!favoritesOnly.value || c.is_favorite)
    ))
    .slice()
    .sort((a, b) => Number(b.is_favorite) - Number(a.is_favorite) || String(a.name ?? '').localeCompare(String(b.name ?? ''))));

watchEffect(() => {
    if (!selectedContact.value && filteredContacts.value.length) {
        selectedId.value = filteredContacts.value[0].id;
    }
});

function resetFilters() {
    search.value = '';
    typeFilter.value = 'all';
    companyFilter.value = 'all';
    favoritesOnly.value = false;
}

function formatDate(dateTimeStr) {
    if (!dateTimeStr) return '—';
    return new Date(dateTimeStr.replace(' ', 'T')).toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
}

/* ── Favorite toggle ────────────────────────────────────────────────────
   Reused as the preview panel's third quick action, standing in for the
   mockup's "Message" button since there's no in-app messaging feature. */
function toggleFavorite(contact) {
    router.patch(route('contact.update', contact.id), {
        name: contact.name,
        email: contact.email,
        phone: contact.phone,
        company: contact.company,
        job_title: contact.job_title,
        address: contact.address,
        notes: contact.notes,
        is_favorite: !contact.is_favorite,
    }, { preserveScroll: true });
}

/* ── Notes — real field, saved via a real PATCH to the Contact backend ─── */
const noteDraft = ref(props.contacts[0]?.notes ?? '');
const noteSaved = ref(false);
const savingNote = ref(false);
watchEffect(() => { noteDraft.value = selectedContact.value?.notes ?? ''; noteSaved.value = false; });

function saveNote() {
    if (!selectedContact.value) return;
    const contact = selectedContact.value;
    savingNote.value = true;
    router.patch(route('contact.update', contact.id), {
        name: contact.name,
        email: contact.email,
        phone: contact.phone,
        company: contact.company,
        job_title: contact.job_title,
        address: contact.address,
        notes: noteDraft.value,
        is_favorite: contact.is_favorite,
    }, {
        preserveScroll: true,
        onFinish: () => {
            savingNote.value = false;
            noteSaved.value = true;
            setTimeout(() => { noteSaved.value = false; }, 2400);
        },
    });
}

/* ── Create dialog (real — posts to the Contact backend) ──────────────── */
const dialogOpen = ref(false);

const form = useForm({
    name: '',
    company: '',
    job_title: '',
    email: '',
    phone: '',
    address: '',
    notes: '',
    is_favorite: false,
});

function openCreateDialog() {
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
}

function saveContact() {
    form.clearErrors();

    if (!form.name.trim()) form.setError('name', 'Name is required.');
    if (form.errors.name) return;

    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => { dialogOpen.value = false; },
    });
}
</script>

<template>
<MainLayout title="Contacts">
    <Head title="Contacts" />

    <div class="cp-page">
        <!-- ── Header ──────────────────────────────────────────────── -->
        <div class="cp-page-header">
            <div class="cp-page-header__left">
                <h1 class="cp-title">Contacts</h1>
                <p class="cp-subtitle">Manage people and partner organizations across your coffee supply chain.</p>
            </div>
            <div class="cp-page-header__actions">
                <button type="button" class="cp-btn-primary" @click="openCreateDialog">
                    <el-icon><Plus /></el-icon> Add Contact
                </button>
            </div>
        </div>

        <!-- ── Search + filter controls ────────────────────────────── -->
        <div class="cp-toolbar-card">
            <div class="cp-toolbar-row">
                <el-input
                    v-model="search"
                    placeholder="Search contacts by name, company, email, phone, or address..."
                    class="cp-search"
                    :prefix-icon="Search"
                    clearable
                />
                <div class="cp-toolbar-selects">
                    <el-select v-model="companyFilter" class="cp-select">
                        <el-option label="Company: All" value="all" />
                        <el-option v-for="co in companies" :key="co" :label="co" :value="co" />
                    </el-select>
                    <button
                        type="button"
                        class="cp-fav-toggle"
                        :class="{ 'cp-fav-toggle--active': favoritesOnly }"
                        @click="favoritesOnly = !favoritesOnly"
                    >
                        <el-icon><component :is="favoritesOnly ? StarFilled : Star" /></el-icon>
                        Favorites
                    </button>
                    <button type="button" class="cp-clear-btn" @click="resetFilters">Clear</button>
                </div>
            </div>

            <div class="cp-role-tabs">
                <button
                    v-for="t in typeTabs"
                    :key="t.key"
                    type="button"
                    class="cp-role-tab"
                    :class="{ 'cp-role-tab--active': typeFilter === t.key }"
                    @click="typeFilter = t.key"
                >
                    {{ t.label }} ({{ typeCount(t.key) }})
                </button>
            </div>
        </div>

        <!-- ── Roster + profile preview ────────────────────────────── -->
        <div class="cp-layout">
            <div class="cp-roster">
                <div class="cp-roster__head">
                    <span>Your Contacts ({{ filteredContacts.length }} Showing)</span>
                    <span>Sort: Favorites First</span>
                </div>

                <div class="cp-roster__list">
                    <div
                        v-for="c in filteredContacts"
                        :key="c.id"
                        class="cp-roster-row"
                        :class="{ 'cp-roster-row--active': c.id === selectedId }"
                        @click="selectContact(c)"
                    >
                        <div class="cp-roster-row__id">
                            <div class="cp-roster-row__avatar">{{ initials(c.name) }}</div>
                            <span v-if="c.is_favorite" class="cp-roster-row__status-dot cp-dot--active" />
                        </div>
                        <div class="cp-roster-row__body cp-cell-truncate">
                            <div class="cp-roster-row__top">
                                <span class="cp-roster-row__name cp-ellipsis">{{ c.name }}</span>
                                <span class="cp-mono cp-muted">#{{ c.id }}</span>
                            </div>
                            <div class="cp-roster-row__sub">
                                <span v-if="c.company" class="cp-roster-row__org cp-ellipsis">{{ c.company }}</span>
                                <span v-if="c.company && c.address">·</span>
                                <span v-if="c.address" class="cp-roster-row__loc"><el-icon :size="13"><Location /></el-icon>{{ c.address }}</span>
                            </div>
                        </div>
                        <div class="cp-roster-row__meta">
                            <span v-if="c.job_title" class="cp-role-badge">{{ c.job_title }}</span>
                            <span class="cp-type-chip" :class="`cp-type-chip--${contactType(c)}`">{{ contactType(c) === 'person' ? 'Person' : 'Organization' }}</span>
                            <div class="cp-roster-row__quick" @click.stop>
                                <a v-if="c.email" :href="`mailto:${c.email}`" class="cp-quick-icon" title="Send Email"><el-icon :size="16"><ChatDotRound /></el-icon></a>
                                <a v-if="c.phone" :href="`tel:${c.phone}`" class="cp-quick-icon" title="Phone Call"><el-icon :size="16"><Phone /></el-icon></a>
                            </div>
                        </div>
                    </div>

                    <div v-if="!filteredContacts.length" class="cp-roster-empty">
                        <el-icon :size="22"><User /></el-icon>
                        <p>{{ contacts.length ? 'No contacts match your search.' : 'No contacts yet. Add your first one.' }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Right profile preview panel ─────────────────────── -->
            <aside class="cp-preview-card">
                <template v-if="selectedContact">
                    <div class="cp-preview__head">
                        <div class="cp-preview__avatar">{{ initials(selectedContact.name) }}</div>
                        <div class="cp-cell-truncate">
                            <div class="cp-preview__name-row">
                                <h3 class="cp-preview__name cp-ellipsis">{{ selectedContact.name }}</h3>
                                <el-icon v-if="selectedContact.is_favorite" class="cp-preview__verified" :size="18"><StarFilled /></el-icon>
                            </div>
                            <p v-if="selectedContact.job_title || selectedContact.company" class="cp-preview__org cp-ellipsis">
                                {{ [selectedContact.job_title, selectedContact.company].filter(Boolean).join(' · ') }}
                            </p>
                            <div class="cp-preview__badges">
                                <span v-if="selectedContact.job_title" class="cp-role-badge">{{ selectedContact.job_title }}</span>
                                <span class="cp-mono cp-muted">#{{ selectedContact.id }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="cp-preview__quick-actions">
                        <a v-if="selectedContact.email" :href="`mailto:${selectedContact.email}`" class="cp-quick-btn">
                            <el-icon><Message /></el-icon> Send Email
                        </a>
                        <a v-if="selectedContact.phone" :href="`tel:${selectedContact.phone}`" class="cp-quick-btn">
                            <el-icon><PhoneFilled /></el-icon> Call
                        </a>
                        <button type="button" class="cp-quick-btn" @click="toggleFavorite(selectedContact)">
                            <el-icon><component :is="selectedContact.is_favorite ? StarFilled : Star" /></el-icon>
                            {{ selectedContact.is_favorite ? 'Favorited' : 'Favorite' }}
                        </button>
                    </div>

                    <div class="cp-spec-card">
                        <div class="cp-spec-card__title">Contact Specifications</div>
                        <div v-if="selectedContact.email" class="cp-info-row">
                            <span class="cp-info-row__label">Email</span>
                            <span class="cp-info-row__value">{{ selectedContact.email }}</span>
                        </div>
                        <div v-if="selectedContact.phone" class="cp-info-row">
                            <span class="cp-info-row__label">Telephone</span>
                            <span class="cp-info-row__value">{{ selectedContact.phone }}</span>
                        </div>
                        <div v-if="selectedContact.company" class="cp-info-row">
                            <span class="cp-info-row__label">Company</span>
                            <span class="cp-info-row__value">{{ selectedContact.company }}</span>
                        </div>
                        <div v-if="selectedContact.address" class="cp-info-row">
                            <span class="cp-info-row__label">Location</span>
                            <span class="cp-info-row__value">{{ selectedContact.address }}</span>
                        </div>
                    </div>

                    <div class="cp-preview__section">
                        <div class="cp-preview__section-title">Details</div>
                        <div class="cp-spec-card">
                            <div class="cp-info-row">
                                <span class="cp-info-row__label">Added</span>
                                <span class="cp-info-row__value">{{ formatDate(selectedContact.created_at) }}</span>
                            </div>
                            <div class="cp-info-row">
                                <span class="cp-info-row__label">Last Updated</span>
                                <span class="cp-info-row__value">{{ formatDate(selectedContact.updated_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="cp-preview__section">
                        <div class="cp-preview__section-head">
                            <span class="cp-preview__section-title">Internal Desk Notes</span>
                            <span v-show="noteSaved" class="cp-notes-saved">Saved</span>
                        </div>
                        <el-input v-model="noteDraft" type="textarea" :rows="2" placeholder="Add personal notes or trade specifications for this contact..." class="cp-notes-input" />
                        <div class="cp-notes-actions">
                            <button type="button" class="cp-note-save-btn" :disabled="savingNote" @click="saveNote">
                                {{ savingNote ? 'Saving…' : 'Save Notes' }}
                            </button>
                        </div>
                    </div>
                </template>

                <div v-else class="cp-preview-empty">
                    <div class="cp-preview-empty__icon"><el-icon :size="22"><User /></el-icon></div>
                    <p class="cp-preview-empty__title">No contact selected</p>
                </div>
            </aside>
        </div>
    </div>
        <el-dialog
            v-model="dialogOpen"
            width="480px"
            destroy-on-close
            align-center
            :show-close="false"
            class="cp-modal"
        >
            <template #header>
                <div class="cp-modal__head">
                    <div class="cp-modal__head-icon">
                        <el-icon :size="18"><User /></el-icon>
                    </div>
                    <div class="cp-modal__head-text">
                        <div class="cp-modal__eyebrow">Create</div>
                        <div class="cp-modal__title">New Contact</div>
                    </div>
                    <button type="button" class="cp-modal__close" aria-label="Close" @click="dialogOpen = false">
                        <el-icon :size="14"><Close /></el-icon>
                    </button>
                </div>
            </template>

            <div class="cp-modal__body">
                <div class="cp-field">
                    <label class="cp-field__label">Name</label>
                    <el-input v-model="form.name" placeholder="Full name" class="cp-input" :class="{ 'cp-input--error': form.errors.name }" />
                    <span v-if="form.errors.name" class="cp-field__error">{{ form.errors.name }}</span>
                </div>

                <div class="cp-grid">
                    <div class="cp-field">
                        <label class="cp-field__label"><el-icon :size="12"><OfficeBuilding /></el-icon> Company</label>
                        <el-input v-model="form.company" placeholder="Company / organization" class="cp-input" />
                    </div>
                    <div class="cp-field">
                        <label class="cp-field__label">Job Title</label>
                        <el-input v-model="form.job_title" placeholder="e.g. Export Manager" class="cp-input" />
                    </div>
                </div>

                <div class="cp-grid">
                    <div class="cp-field">
                        <label class="cp-field__label"><el-icon :size="12"><Message /></el-icon> Email</label>
                        <el-input v-model="form.email" placeholder="name@example.com" class="cp-input" :class="{ 'cp-input--error': form.errors.email }" />
                        <span v-if="form.errors.email" class="cp-field__error">{{ form.errors.email }}</span>
                    </div>
                    <div class="cp-field">
                        <label class="cp-field__label"><el-icon :size="12"><Phone /></el-icon> Phone</label>
                        <el-input v-model="form.phone" placeholder="+256 700 000 000" class="cp-input" :class="{ 'cp-input--error': form.errors.phone }" />
                        <span v-if="form.errors.phone" class="cp-field__error">{{ form.errors.phone }}</span>
                    </div>
                </div>

                <div class="cp-field">
                    <label class="cp-field__label"><el-icon :size="12"><Location /></el-icon> Address</label>
                    <el-input v-model="form.address" placeholder="City, country" class="cp-input" :class="{ 'cp-input--error': form.errors.address }" />
                    <span v-if="form.errors.address" class="cp-field__error">{{ form.errors.address }}</span>
                </div>

                <div class="cp-field">
                    <label class="cp-field__label">Notes</label>
                    <el-input v-model="form.notes" type="textarea" :rows="3" placeholder="Optional notes about this contact" class="cp-input" />
                </div>

                <div class="cp-field cp-field--switch">
                    <div class="cp-switch-row">
                        <div class="cp-switch-row__text">
                            <label class="cp-field__label"><el-icon :size="12"><Star /></el-icon> Favorite</label>
                            <span class="cp-field__hint">Favorites always appear first in your address book.</span>
                        </div>
                        <el-switch v-model="form.is_favorite" class="cp-switch" />
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="cp-modal__footer">
                    <button type="button" class="cp-btn-outline" @click="dialogOpen = false">Cancel</button>
                    <button type="button" class="cp-btn-primary" :disabled="form.processing" @click="saveContact">
                        <el-icon v-if="!form.processing"><Plus /></el-icon>
                        {{ form.processing ? 'Saving…' : 'Create Contact' }}
                    </button>
                </div>
            </template>
        </el-dialog>
    </MainLayout>
</template>

<style scoped>
/* Contacts — app-wide theme. Tokens source from the shared
   MainLayout --dp-* palette (defined on .dp-shell); literal hex
   fallbacks are the same values so the page reads correctly on its own.
   Spacing follows the 4px base grid used across the app. */
.cp-page {
    --card-border: var(--dp-outline-variant, #E5E7EB);
    --surface: var(--dp-surface-container-lowest, #ffffff);
    --surface-muted: var(--dp-surface-container-low, #F5F6F7);
    --surface-elevated: var(--dp-surface-container, #F1F2F3);
    --border: var(--dp-outline-variant, #E5E7EB);
    --primary: var(--dp-primary, #000000);
    --on-primary: var(--dp-on-primary, #ffffff);
    --text: var(--dp-on-surface, #121516);
    --text-2: var(--dp-on-surface-variant, #4B5457);
    --text-muted: var(--dp-outline, #6F7677);
    --success: #15803D;
    --error: var(--dp-error, #F85149);
    font-family: var(--dp-font-sans, 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
    color: var(--text);
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.cp-muted { color: var(--text-muted); }

/* ── Page header ─────────────────────────────────────────────────────── */
.cp-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.cp-page-header__left { max-width: 640px; }
.cp-page-header__left::after {
    content: '';
    display: block;
    width: 40px;
    height: 2px;
    background: var(--text-muted);
    opacity: 0.2;
    margin-top: 12px;
}
.cp-page-header__actions { display: flex; gap: 8px; flex-wrap: wrap; }

.cp-title {
    font-size: 1.5rem;
    line-height: 1.9rem;
    letter-spacing: -0.015em;
    font-weight: 800;
    margin: 0 0 6px;
}
.cp-subtitle {
    font-size: 0.9375rem;
    line-height: 1.5rem;
    color: var(--text-muted);
    margin: 0;
    max-width: 64ch;
    text-wrap: pretty;
}

/* ── Buttons ─────────────────────────────────────────────────────────── */
.cp-btn-primary {
    height: 36px;
    padding: 0 16px;
    background: var(--primary);
    border: 1px solid transparent;
    color: var(--on-primary);
    border-radius: 6px;
    font-family: inherit;
    font-size: var(--dp-content-font-size);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 120ms ease;
}
.cp-btn-primary:hover:not(:disabled) { opacity: 0.88; }
.cp-btn-primary:disabled { opacity: 0.5; cursor: default; }

.cp-btn-outline {
    height: 36px;
    padding: 0 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    color: var(--text);
    border-radius: 6px;
    font-family: inherit;
    font-size: var(--dp-content-font-size);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    text-decoration: none;
    transition: background 120ms ease, color 120ms ease, border-color 120ms ease;
}
.cp-btn-outline:hover { background: var(--surface-muted); }

/* ── Master–detail layout ────────────────────────────────────────────── */
.cp-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 20px;
    align-items: start;
}

/* ── Search + filter toolbar ─────────────────────────────────────────── */
.cp-toolbar-card {
    background: var(--surface);
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius, 6px);
    box-shadow: var(--dp-card-shadow, none);
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.cp-toolbar-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.cp-toolbar-selects { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.cp-cell-truncate { min-width: 0; }
.cp-ellipsis { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }
.cp-mono { font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace); font-size: 11px; }

/* ── Toolbar search — compact on-theme input. The app's global 48px input
      height is deliberately overridden so the toolbar stays tight while
      the input otherwise inherits the standard on-theme look. */
.cp-search { flex: 1; min-width: 200px; max-width: 420px; }
.cp-search :deep(.el-input__wrapper) {
    height: 36px;
    min-height: 36px !important;
    background: var(--surface);
    border-radius: 6px;
    box-shadow: 0 0 0 1px var(--border) inset !important;
    transition: box-shadow 120ms ease;
}
.cp-search :deep(.el-input__inner) { font-size: var(--dp-content-font-size); color: var(--text); }
.cp-search :deep(.el-input__inner::placeholder) { color: var(--text-muted); }
.cp-search :deep(.el-input__prefix .el-icon) { color: var(--text-muted); }
.cp-search :deep(.el-input__wrapper.is-focus) { box-shadow: 0 0 0 1px var(--primary) inset !important; }

.cp-select { width: 148px; flex-shrink: 0; }
.cp-select :deep(.el-select__wrapper) {
    min-height: 36px !important;
    background: var(--surface-muted);
    border-radius: 6px;
    box-shadow: none !important;
    font-size: var(--dp-content-font-size);
    color: var(--text-2);
}
.cp-select :deep(.el-select__wrapper.is-hovering) { background: var(--surface-elevated); }
.cp-select :deep(.el-select__wrapper.is-focused) { box-shadow: 0 0 0 1px var(--primary) inset !important; }

.cp-clear-btn {
    height: 36px;
    padding: 0 12px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    font-size: var(--dp-content-font-size);
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: background 120ms ease, color 120ms ease;
}
.cp-clear-btn:hover { background: var(--surface-muted); color: var(--text); }

.cp-fav-toggle {
    height: 36px;
    padding: 0 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--surface-muted);
    border: none;
    border-radius: 6px;
    color: var(--text-2);
    font-family: inherit;
    font-size: var(--dp-content-font-size);
    font-weight: 600;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 120ms ease, color 120ms ease;
}
.cp-fav-toggle:hover { background: var(--surface-elevated); color: var(--text); }
.cp-fav-toggle--active { background: #FFFBEB; color: #B45309; }
.cp-fav-toggle--active .el-icon { color: #B45309; }

/* ── Role tabs ────────────────────────────────────────────────────────── */
.cp-role-tabs { display: flex; align-items: center; gap: 6px; overflow-x: auto; padding-top: 10px; border-top: 1px solid var(--border); }
.cp-role-tab {
    flex-shrink: 0;
    height: 30px;
    padding: 0 12px;
    border: none;
    background: transparent;
    border-radius: 999px;
    color: var(--text-2);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background 120ms ease, color 120ms ease;
}
.cp-role-tab:hover { background: var(--surface-muted); color: var(--text); }
.cp-role-tab--active,
.cp-role-tab--active:hover { background: var(--primary); color: var(--on-primary); }

/* ── Roster + preview layout ─────────────────────────────────────────── */
.cp-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
    gap: 20px;
    align-items: start;
}

.cp-roster { display: flex; flex-direction: column; gap: 10px; min-width: 0; }
.cp-roster__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 4px;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
}
.cp-roster__list { display: flex; flex-direction: column; gap: 8px; }

.cp-roster-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: var(--surface);
    border: 1px solid var(--card-border);
    border-radius: 10px;
    cursor: pointer;
    transition: background 120ms ease, border-color 120ms ease;
    flex-wrap: wrap;
}
.cp-roster-row:hover { background: var(--surface-muted); }
.cp-roster-row--active { border-color: var(--primary); background: var(--surface-muted); }

.cp-roster-row__id { position: relative; flex-shrink: 0; }
.cp-roster-row__avatar {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: var(--surface-elevated);
    border: 1px solid var(--border);
    color: var(--text-2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
}
.cp-roster-row__status-dot {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: 2px solid var(--surface);
}
.cp-dot--active { background: #B45309; }

.cp-roster-row__body { flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 2px; }
.cp-roster-row__top { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.cp-roster-row__name { font-size: var(--dp-content-font-size); font-weight: 700; color: var(--text); }
.cp-roster-row__sub { display: flex; align-items: center; gap: 6px; font-size: var(--dp-content-font-size); color: var(--text-muted); flex-wrap: wrap; }
.cp-roster-row__org { max-width: 220px; }
.cp-roster-row__loc { display: inline-flex; align-items: center; gap: 3px; white-space: nowrap; }

.cp-roster-row__meta { display: flex; align-items: center; gap: 8px; margin-left: auto; flex-shrink: 0; }
.cp-roster-row__quick { display: flex; align-items: center; gap: 2px; }
.cp-quick-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    color: var(--text-muted);
    text-decoration: none;
    transition: background 120ms ease, color 120ms ease;
}
.cp-quick-icon:hover { background: var(--surface-elevated); color: var(--primary); }

.cp-role-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 9px;
    border-radius: 6px;
    background: var(--surface-elevated);
    color: var(--text-2);
    font-size: var(--dp-content-font-size);
    font-weight: 600;
    white-space: nowrap;
}
.cp-type-chip {
    display: inline-flex;
    align-items: center;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    border: 1px solid transparent;
}
.cp-type-chip--person { background: var(--surface-muted); color: var(--text-2); }
.cp-type-chip--organization { background: var(--surface); border-color: var(--border); color: var(--text-2); }

.cp-roster-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 48px 16px;
    color: var(--text-muted);
    text-align: center;
    background: var(--surface);
    border: 1px solid var(--card-border);
    border-radius: 10px;
}
.cp-roster-empty p { margin: 0; font-size: var(--dp-content-font-size); }

/* ── Right profile preview panel ─────────────────────────────────────── */
.cp-preview-card {
    position: sticky;
    top: 16px;
    background: var(--surface-muted);
    border-radius: 14px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.cp-preview__head { display: flex; align-items: flex-start; gap: 14px; }
.cp-preview__avatar {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    background: var(--surface-elevated);
    border: 1px solid var(--border);
    color: var(--text);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 700;
    flex-shrink: 0;
}
.cp-preview__name-row { display: flex; align-items: center; gap: 6px; }
.cp-preview__name { margin: 0; font-size: 17px; font-weight: 800; letter-spacing: -0.01em; color: var(--text); }
.cp-preview__verified { color: #B45309; flex-shrink: 0; }
.cp-preview__org { margin: 2px 0 0; font-size: var(--dp-content-font-size); color: var(--text-muted); }
.cp-preview__badges { display: flex; align-items: center; gap: 8px; margin-top: 6px; }

.cp-preview__quick-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.cp-quick-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 34px;
    padding: 0 8px;
    background: var(--surface);
    border: none;
    border-radius: 8px;
    color: var(--text);
    font-family: inherit;
    font-size: var(--dp-content-font-size);
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    transition: background 120ms ease;
}
.cp-quick-btn:hover { background: var(--surface-elevated); }
.cp-quick-btn .el-icon { color: var(--primary); }

.cp-spec-card { background: var(--surface); border-radius: 10px; padding: 14px; display: flex; flex-direction: column; gap: 4px; }
.cp-spec-card__title {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    margin-bottom: 6px;
}

.cp-info-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 5px 0; font-size: var(--dp-content-font-size); }
.cp-info-row__label { color: var(--text-muted); flex-shrink: 0; }
.cp-info-row__value { color: var(--text); font-weight: 600; min-width: 0; text-align: right; overflow-wrap: anywhere; }

.cp-preview__section { display: flex; flex-direction: column; gap: 8px; }
.cp-preview__section-head { display: flex; align-items: center; justify-content: space-between; }
.cp-preview__section-title {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    padding: 0 2px;
}

.cp-pills { display: flex; flex-wrap: wrap; gap: 8px; }
.cp-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    background: var(--surface);
    border-radius: 8px;
    color: var(--text);
    font-size: var(--dp-content-font-size);
    font-weight: 600;
}
.cp-pill .el-icon { color: var(--primary); }

.cp-activity-list { display: flex; flex-direction: column; gap: 6px; }
.cp-activity-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px;
    background: var(--surface);
    border-radius: 8px;
    font-size: var(--dp-content-font-size);
    line-height: 1.5;
}
.cp-activity-row__icon { color: var(--primary); margin-top: 1px; flex-shrink: 0; }
.cp-activity-row strong { color: var(--text); font-weight: 700; }

.cp-notes-saved { font-size: 10.5px; font-weight: 700; color: var(--success); }
.cp-notes-input :deep(.el-textarea__inner) {
    background: var(--surface);
    border-radius: 10px;
    box-shadow: none;
    padding: 12px;
    font-size: var(--dp-content-font-size);
    color: var(--text);
    resize: none;
}
.cp-notes-actions { display: flex; justify-content: flex-end; margin-top: 8px; }
.cp-note-save-btn {
    height: 32px;
    padding: 0 14px;
    border: none;
    background: var(--surface-elevated);
    color: var(--text);
    font-size: var(--dp-content-font-size);
    font-weight: 700;
    border-radius: 8px;
    cursor: pointer;
    transition: background 120ms ease;
}
.cp-note-save-btn:hover { background: var(--surface); }

.cp-preview-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 280px;
    padding: 32px;
    text-align: center;
}
.cp-preview-empty__icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: var(--surface);
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 6px;
}
.cp-preview-empty__title { margin: 0; font-size: 14px; font-weight: 600; color: var(--text); }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1099.98px) {
    .cp-layout { grid-template-columns: 1fr; }
    .cp-preview-card { position: static; }
}

@media (max-width: 767.98px) {
    .cp-toolbar-row { flex-direction: column; align-items: stretch; }
    .cp-toolbar-selects { flex-direction: column; align-items: stretch; }
    .cp-select { width: 100%; }
    .cp-search { width: 100%; max-width: 100%; }
    .cp-grid { grid-template-columns: 1fr; }
    .cp-page-header__left::after { display: none; }
    .cp-preview__quick-actions { grid-template-columns: 1fr; }
    .cp-roster-row { flex-wrap: wrap; }
    .cp-roster-row__meta { margin-left: 0; width: 100%; justify-content: space-between; }
}

/* ── Contact modal ─────────────────────────────────────────────────────
   <el-dialog> teleports to <body>, outside both .dp-shell (--dp-*) and
   .cp-page (page tokens), so literal light-palette hexes from the same
   token set are used here — the standard approach across app modals. */
:deep(.el-dialog.cp-modal) {
    background: #ffffff;
    border: 1px solid #E5E7EB;
    border-radius: var(--el-border-radius-base, 6px);
    padding: 0;
    overflow: hidden;
    box-shadow: var(--el-box-shadow-dark, 0 8px 28px rgba(0, 0, 0, 0.08));
    font-family: var(--dp-font-sans, 'Inter', system-ui, sans-serif);
}

:deep(.el-dialog.cp-modal .el-dialog__header) {
    padding: 0;
    margin: 0;
}

:deep(.el-dialog.cp-modal .el-dialog__body) {
    padding: 0;
}

:deep(.el-dialog.cp-modal .el-dialog__footer) {
    padding: 0;
}

.cp-modal__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    border-bottom: 1px solid #E5E7EB;
}

.cp-modal__head-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    background: #F1F2F3;
    border: 1px solid #E5E7EB;
    color: #121516;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.cp-modal__head-text {
    flex: 1;
    min-width: 0;
}

.cp-modal__eyebrow {
    font-size: 11px;
    line-height: 16px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #6F7677;
    margin-bottom: 2px;
}

.cp-modal__title {
    font-size: 15px;
    line-height: 20px;
    font-weight: 700;
    color: #121516;
}

.cp-modal__close {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: #6F7677;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 120ms, color 120ms;
}

.cp-modal__close:hover {
    background: #F1F2F3;
    color: #121516;
}

/* Form controls otherwise inherit Element's global theme; only the field
   surface and the error state are customized here. */
.cp-modal__body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 66vh;
    overflow-y: auto;
}

.cp-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.cp-field {
    display: flex;
    flex-direction: column;
}

.cp-field__label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    line-height: 16px;
    font-weight: 600;
    color: #121516;
    margin-bottom: 6px;
}

.cp-field__hint {
    font-size: var(--dp-content-font-size);
    line-height: 16px;
    color: #6F7677;
}

.cp-field__error {
    font-size: 12px;
    line-height: 16px;
    font-weight: 500;
    color: #F85149;
    margin-top: 4px;
}

.cp-input--error :deep(.el-input__wrapper),
.cp-input--error :deep(.el-textarea__inner) {
    box-shadow: 0 0 0 1.5px #F85149 inset !important;
}

.cp-input :deep(.el-input__wrapper),
.cp-input :deep(.el-textarea__inner) {
    border-radius: 6px;
    box-shadow: 0 0 0 1px #E5E7EB inset;
}

/* Favorite switch */
.cp-field--switch {
    padding-top: 16px;
    border-top: 1px solid #E5E7EB;
}

.cp-switch-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.cp-switch-row__text {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.cp-switch-row .cp-field__label {
    margin-bottom: 0;
}

.cp-modal__footer {
    /* Button tokens are defined here (not on .cp-page) because the
       <el-dialog> teleports to <body> — .cp-page's custom properties
       don't cascade into it, so the primary save button would otherwise
       render with no background. */
    --primary: #000000;
    --on-primary: #ffffff;
    --surface: #ffffff;
    --surface-muted: #F5F6F7;
    --border: #E5E7EB;
    --text: #121516;
    --text-muted: #6F7677;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}

/* ── Reduced motion ──────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .cp-roster-row,
    .cp-quick-icon,
    .cp-role-tab,
    .cp-clear-btn,
    .cp-btn-primary,
    .cp-btn-outline,
    .cp-modal__close {
        transition: none;
    }
}
</style>
