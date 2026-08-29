<script setup>
import { computed, ref, watchEffect } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    Plus, Delete, Edit, Star, StarFilled, Search, User, Message, Phone,
    OfficeBuilding, Location, Close,
} from '@element-plus/icons-vue';

const props = defineProps({
    contacts: { type: Array, default: () => [] },
});

/* ── Master–detail selection ─────────────────────────────────────────── */
const selectedId = ref(null);
const selectedContact = computed(() => props.contacts.find((c) => c.id === selectedId.value) || null);

function selectContact(contact) {
    selectedId.value = contact.id;
}

/* ── Search + filters ────────────────────────────────────────────────── */
const activeFilter = ref('all');
const filters = [
    { key: 'all', label: 'All' },
    { key: 'favorites', label: 'Favorites' },
];

const search = ref('');

function matchesSearch(c) {
    const q = search.value.trim().toLowerCase();
    if (!q) return true;
    return [c.name, c.company, c.job_title, c.email, c.phone, c.address]
        .filter(Boolean)
        .some((field) => field.toLowerCase().includes(q));
}

function matchesFilter(key, c) {
    if (key === 'favorites') return c.is_favorite;
    return true;
}

const filteredContacts = computed(() => props.contacts
    .filter((c) => matchesFilter(activeFilter.value, c) && matchesSearch(c))
    .slice()
    .sort((a, b) => Number(b.is_favorite) - Number(a.is_favorite) || String(a.name ?? '').localeCompare(String(b.name ?? ''))));

function tabCount(key) {
    return props.contacts.filter((c) => matchesFilter(key, c)).length;
}

/* Keep a selection available: auto-pick the first listed contact on load
   and after a delete so the detail panel never goes stale. */
watchEffect(() => {
    if (!selectedContact.value && filteredContacts.value.length) {
        selectedId.value = filteredContacts.value[0].id;
    }
});

/* ── Display helpers ─────────────────────────────────────────────────── */
function initials(name) {
    return (name || '').split(' ').filter(Boolean).slice(0, 2).map((p) => p[0]?.toUpperCase()).join('') || '?';
}

function detailFor(c) {
    return c.job_title && c.company ? `${c.job_title} · ${c.company}` : (c.company || c.job_title || '');
}

/* ── Create / edit dialog ────────────────────────────────────────────── */
const dialogOpen = ref(false);
const editingContact = ref(null);

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
    editingContact.value = null;
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
}

function openEditDialog(contact) {
    editingContact.value = contact;
    form.clearErrors();
    form.name = contact.name ?? '';
    form.company = contact.company ?? '';
    form.job_title = contact.job_title ?? '';
    form.email = contact.email ?? '';
    form.phone = contact.phone ?? '';
    form.address = contact.address ?? '';
    form.notes = contact.notes ?? '';
    form.is_favorite = !!contact.is_favorite;
    dialogOpen.value = true;
}

function saveContact() {
    form.clearErrors();

    if (!form.name.trim()) form.setError('name', 'Name is required.');
    if (form.errors.name) return;

    const options = {
        preserveScroll: true,
        onSuccess: () => { dialogOpen.value = false; },
    };

    if (editingContact.value) {
        form.patch(route('contact.update', editingContact.value.id), options);
    } else {
        form.post(route('contact.store'), options);
    }
}

/* ── Favorite toggle / delete ────────────────────────────────────────── */
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

const confirmOpen = ref(false);
const pendingDelete = ref(null);

function deleteContact(contact) {
    pendingDelete.value = contact;
    confirmOpen.value = true;
}

function confirmDeleteContact() {
    if (!pendingDelete.value) return;
    router.delete(route('contact.destroy', pendingDelete.value.id), { preserveScroll: true });
    pendingDelete.value = null;
}
</script>

<template>
    <DesignPreviewLayout title="Contacts">
        <Head title="Contacts" />

        <div class="cp-page">
            <!-- ── Header ──────────────────────────────────────────────── -->
            <div class="cp-page-header">
                <div class="cp-page-header__left">
                    <h1 class="cp-title">Contacts</h1>
                    <p class="cp-subtitle">Your personal address book for the farmers, exporters, buyers, and partners you work with every day, always one click away.</p>
                    <div class="cp-page-header__summary">
                        <span class="cp-page-header__summary-item">{{ contacts.length }} contacts</span>
                        <span class="cp-page-header__summary-dot" />
                        <span class="cp-page-header__summary-item">{{ tabCount('favorites') }} favorites</span>
                    </div>
                </div>
                <div class="cp-page-header__actions">
                    <button type="button" class="cp-btn-primary" @click="openCreateDialog">
                        <el-icon><Plus /></el-icon> New Contact
                    </button>
                </div>
            </div>

            <!-- ── Master–detail layout ────────────────────────────────── -->
            <div class="cp-layout">
                <div class="cp-list-card">

                    <div class="cp-panel__toolbar">
                        <div class="cp-filters">
                            <button
                                v-for="f in filters"
                                :key="f.key"
                                type="button"
                                class="cp-filter"
                                :class="{ 'cp-filter--active': activeFilter === f.key }"
                                @click="activeFilter = f.key"
                            >
                                {{ f.label }}
                                <span class="cp-filter__count">{{ tabCount(f.key) }}</span>
                            </button>
                        </div>

                        <el-input
                            v-model="search"
                            placeholder="Search name, company, email, phone…"
                            class="cp-search"
                            :prefix-icon="Search"
                            clearable
                        />
                    </div>

                        <div class="cp-list">
                            <button
                                v-for="c in filteredContacts"
                                :key="c.id"
                                type="button"
                                class="cp-list-row"
                                :class="{ 'cp-list-row--active': c.id === selectedId }"
                                @click="selectContact(c)"
                            >
                                <el-avatar :size="36" class="cp-list-row__avatar">{{ initials(c.name) }}</el-avatar>
                                <span class="cp-list-row__text">
                                    <span class="cp-list-row__name">{{ c.name }}</span>
                                    <span v-if="detailFor(c)" class="cp-list-row__detail">{{ detailFor(c) }}</span>
                                </span>
                                <el-icon v-if="c.is_favorite" class="cp-list-row__star" :size="14"><StarFilled /></el-icon>
                            </button>

                            <div v-if="!filteredContacts.length" class="cp-list-empty">
                                <el-icon :size="22"><User /></el-icon>
                                <p>{{ search || activeFilter !== 'all' ? 'No contacts match your search.' : 'No contacts yet. Add your first one.' }}</p>
                            </div>
                        </div>

                        <div class="cp-list-foot">
                            Showing <strong>{{ filteredContacts.length }}</strong> of {{ contacts.length }}
                        </div>
                </div>
                <!-- ── Detail panel ────────────────────────────────────── -->
                <aside class="cp-detail-card">
                    <template v-if="selectedContact">
                        <div class="cp-detail__head">
                            <el-avatar :size="52" class="cp-detail__avatar">{{ initials(selectedContact.name) }}</el-avatar>
                            <div class="cp-detail__identity">
                                <h2 class="cp-detail__name">{{ selectedContact.name }}</h2>
                                <p v-if="detailFor(selectedContact)" class="cp-detail__role">{{ detailFor(selectedContact) }}</p>
                            </div>
                            <button
                                type="button"
                                class="cp-detail__star"
                                :class="{ 'cp-detail__star--on': selectedContact.is_favorite }"
                                :aria-label="selectedContact.is_favorite ? 'Remove from favorites' : 'Add to favorites'"
                                :title="selectedContact.is_favorite ? 'Remove from favorites' : 'Add to favorites'"
                                @click="toggleFavorite(selectedContact)"
                            >
                                <el-icon :size="16"><component :is="selectedContact.is_favorite ? StarFilled : Star" /></el-icon>
                            </button>
                        </div>

                        <div class="cp-detail__info">
                            <div v-if="selectedContact.email" class="cp-info-row">
                                <span class="cp-info-row__icon"><el-icon :size="14"><Message /></el-icon></span>
                                <span class="cp-info-row__label">Email</span>
                                <a :href="`mailto:${selectedContact.email}`" class="cp-info-row__value cp-info-row__value--link">{{ selectedContact.email }}</a>
                            </div>
                            <div v-if="selectedContact.phone" class="cp-info-row">
                                <span class="cp-info-row__icon"><el-icon :size="14"><Phone /></el-icon></span>
                                <span class="cp-info-row__label">Phone</span>
                                <a :href="`tel:${selectedContact.phone}`" class="cp-info-row__value cp-info-row__value--link">{{ selectedContact.phone }}</a>
                            </div>
                            <div v-if="selectedContact.company" class="cp-info-row">
                                <span class="cp-info-row__icon"><el-icon :size="14"><OfficeBuilding /></el-icon></span>
                                <span class="cp-info-row__label">Company</span>
                                <span class="cp-info-row__value">{{ selectedContact.company }}</span>
                            </div>
                            <div v-if="selectedContact.job_title" class="cp-info-row">
                                <span class="cp-info-row__icon"><el-icon :size="14"><User /></el-icon></span>
                                <span class="cp-info-row__label">Role</span>
                                <span class="cp-info-row__value">{{ selectedContact.job_title }}</span>
                            </div>
                            <div v-if="selectedContact.address" class="cp-info-row">
                                <span class="cp-info-row__icon"><el-icon :size="14"><Location /></el-icon></span>
                                <span class="cp-info-row__label">Address</span>
                                <span class="cp-info-row__value">{{ selectedContact.address }}</span>
                            </div>
                        </div>

                        <div v-if="selectedContact.notes" class="cp-detail__notes">
                            <div class="cp-detail__notes-label">Notes</div>
                            <p class="cp-detail__notes-text">{{ selectedContact.notes }}</p>
                        </div>

                        <div class="cp-detail__actions">
                            <button type="button" class="cp-btn-primary" @click="openEditDialog(selectedContact)">
                                <el-icon><Edit /></el-icon> Edit Contact
                            </button>
                            <button type="button" class="cp-btn-outline cp-btn-danger" @click="deleteContact(selectedContact)">
                                <el-icon><Delete /></el-icon> Delete
                            </button>
                        </div>
                    </template>

                    <div v-else class="cp-detail-empty">
                        <div class="cp-detail-empty__icon"><el-icon :size="22"><User /></el-icon></div>
                        <p class="cp-detail-empty__title">No contact selected</p>
                        <p class="cp-detail-empty__hint">Pick someone from the list to see their details here.</p>
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
                        <div class="cp-modal__eyebrow">{{ editingContact ? 'Edit' : 'Create' }}</div>
                        <div class="cp-modal__title">{{ editingContact ? 'Edit Contact' : 'New Contact' }}</div>
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
                        {{ form.processing ? 'Saving…' : (editingContact ? 'Save Changes' : 'Create Contact') }}
                    </button>
                </div>
            </template>
        </el-dialog>

        <ConfirmDialog
            v-model="confirmOpen"
            title="Delete Contact"
            :message="pendingDelete ? `Delete “${pendingDelete.name}”? This can't be undone.` : ''"
            confirm-text="Delete"
            @confirm="confirmDeleteContact"
        />
    </DesignPreviewLayout>
</template>

<style scoped>
/* Contacts — app-wide theme. Tokens source from the shared
   DesignPreviewLayout --dp-* palette (defined on .dp-shell); literal hex
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
.cp-page-header__summary {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-2);
}
.cp-page-header__summary-dot {
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: var(--text-muted);
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
    font-size: 13px;
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
    font-size: 13px;
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

.cp-list-card,
.cp-detail-card {
    background: var(--surface);
    border: 1px solid var(--card-border);
    border-radius: var(--dp-card-radius, 6px);
    box-shadow: var(--dp-card-shadow, none);
    overflow: hidden;
}

/* ── Contact list (master) ───────────────────────────────────────────── */
.cp-list {
    display: flex;
    flex-direction: column;
    max-height: 560px;
    overflow-y: auto;
}
.cp-list-row {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 10px 16px;
    border: none;
    border-bottom: 1px solid var(--border);
    background: transparent;
    font-family: inherit;
    text-align: left;
    cursor: pointer;
    transition: background 120ms ease;
}
.cp-list-row:last-child { border-bottom: none; }
.cp-list-row:hover { background: var(--surface-muted); }
.cp-list-row--active,
.cp-list-row--active:hover {
    background: var(--surface-muted);
    box-shadow: inset 3px 0 0 var(--primary);
}
.cp-list-row__avatar {
    background: var(--surface-elevated);
    border: 1px solid var(--border);
    color: var(--text-2);
    font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace);
    font-size: 11px;
    font-weight: 500;
    flex-shrink: 0;
}
.cp-list-row__text { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1; }
.cp-list-row__name {
    font-size: 14px;
    line-height: 20px;
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cp-list-row__detail {
    font-size: 12px;
    line-height: 16px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cp-list-row__star { color: #B45309; flex-shrink: 0; }

.cp-list-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 40px 16px;
    color: var(--text-muted);
    text-align: center;
}
.cp-list-empty p { margin: 0; font-size: 12.5px; line-height: 18px; }

.cp-list-foot {
    padding: 10px 16px;
    border-top: 1px solid var(--border);
    background: var(--surface-muted);
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
}
.cp-list-foot strong {
    font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace);
    font-weight: 600;
    color: var(--text-2);
}

/* ── Detail panel ────────────────────────────────────────────────────── */
.cp-detail-card { position: sticky; top: 16px; }
.cp-detail__head {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 20px;
    border-bottom: 1px solid var(--border);
}
.cp-detail__avatar {
    background: var(--surface-elevated);
    border: 1px solid var(--border);
    color: var(--text-2);
    font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace);
    font-size: 14px;
    font-weight: 600;
    flex-shrink: 0;
}
.cp-detail__identity { flex: 1; min-width: 0; }
.cp-detail__name {
    margin: 0 0 2px;
    font-size: 16px;
    line-height: 22px;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: var(--text);
}
.cp-detail__role { margin: 0; font-size: 12.5px; line-height: 18px; color: var(--text-muted); }
.cp-detail__star {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: color 120ms ease, background 120ms ease;
}
.cp-detail__star:hover { color: #B45309; background: var(--surface-elevated); }
.cp-detail__star--on { color: #B45309; }

.cp-detail__info { padding: 8px 20px; }
.cp-info-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 0;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}
.cp-info-row:last-child { border-bottom: none; }
.cp-info-row__icon {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: var(--surface-muted);
    color: var(--text-2);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.cp-info-row__label {
    width: 64px;
    flex-shrink: 0;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
}
.cp-info-row__value {
    color: var(--text);
    font-weight: 500;
    min-width: 0;
    overflow-wrap: anywhere;
}
.cp-info-row__value--link {
    font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace);
    font-size: 12.5px;
    color: var(--text-2);
    text-decoration: none;
}
.cp-info-row__value--link:hover { color: var(--primary); text-decoration: underline; }

.cp-detail__notes {
    margin: 8px 20px;
    background: var(--surface-muted);
    border-left: 3px solid var(--primary);
    border-radius: 6px;
    padding: 12px 14px;
}
.cp-detail__notes-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    margin-bottom: 6px;
}
.cp-detail__notes-text {
    margin: 0;
    font-size: 13px;
    line-height: 1.6;
    color: var(--text);
    white-space: pre-line;
}

.cp-detail__actions {
    display: flex;
    gap: 8px;
    padding: 16px 20px;
    border-top: 1px solid var(--border);
    background: var(--surface-muted);
}
.cp-detail__actions .cp-btn-primary,
.cp-detail__actions .cp-btn-outline { flex: 1; justify-content: center; }
.cp-btn-danger:hover {
    background: var(--dp-error-container, #FEEDED);
    border-color: var(--dp-error-container, #FEEDED);
    color: var(--error);
}

.cp-detail-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 280px;
    padding: 32px;
    text-align: center;
}
.cp-detail-empty__icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: var(--surface-muted);
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 6px;
}
.cp-detail-empty__title { margin: 0; font-size: 14px; font-weight: 600; color: var(--text); }
.cp-detail-empty__hint { margin: 0; font-size: 12.5px; color: var(--text-muted); }

/* ── Panel toolbar ───────────────────────────────────────────────────── */
.cp-panel__toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
}
.cp-filters { display: flex; gap: 6px; }
.cp-filter {
    height: 32px;
    padding: 0 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: 999px;
    color: var(--text-2);
    font-family: inherit;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background 120ms ease, color 120ms ease, border-color 120ms ease;
}
.cp-filter:hover { background: var(--surface-muted); color: var(--text); }
.cp-filter--active { background: var(--primary); border-color: var(--primary); color: var(--on-primary); }
.cp-filter__count {
    font-family: var(--dp-font-mono, 'JetBrains Mono', ui-monospace, 'SF Mono', Consolas, monospace);
    font-size: 11px;
    line-height: 16px;
    color: var(--text-muted);
}
.cp-filter--active .cp-filter__count { color: var(--on-primary); opacity: 0.78; }

/* ── Toolbar search — compact on-theme input. The app's global 48px input
      height is deliberately overridden so the toolbar stays tight while
      the input otherwise inherits the standard on-theme look. */
.cp-search { width: 280px; max-width: 100%; }
.cp-search :deep(.el-input__wrapper) {
    height: 36px;
    min-height: 36px !important;
    background: var(--surface);
    border-radius: 6px;
    box-shadow: 0 0 0 1px var(--border) inset !important;
    transition: box-shadow 120ms ease;
}
.cp-search :deep(.el-input__inner) { font-size: 13px; color: var(--text); }
.cp-search :deep(.el-input__inner::placeholder) { color: var(--text-muted); }
.cp-search :deep(.el-input__prefix .el-icon) { color: var(--text-muted); }
.cp-search :deep(.el-input__wrapper.is-focus) { box-shadow: 0 0 0 1px var(--primary) inset !important; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 1099.98px) {
    .cp-layout { grid-template-columns: 1fr; }
    .cp-detail-card { position: static; }
}

@media (max-width: 767.98px) {
    .cp-panel__toolbar { flex-direction: column; align-items: stretch; }
    .cp-search { width: 100%; }
    .cp-grid { grid-template-columns: 1fr; }
    .cp-page-header__left::after { display: none; }
    .cp-detail__actions { flex-direction: column; }
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
    font-size: 12px;
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
    .cp-list-row,
    .cp-detail__star,
    .cp-btn-primary,
    .cp-btn-outline,
    .cp-filter,
    .cp-modal__close {
        transition: none;
    }
}
</style>
