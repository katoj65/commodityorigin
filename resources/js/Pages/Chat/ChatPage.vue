<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import DesignPreviewLayout from '@/Layouts/DesignPreviewLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import {
    Promotion, Edit, Delete, Check, Close,
    MagicStick, TrendCharts, DocumentAdd, Coffee,
} from '@element-plus/icons-vue';

const page = usePage();
const messages = computed(() => page.props.chatMessages ?? []);
const subscribedAgents = computed(() => page.props.subscribedAgents ?? []);

const statusLabel = computed(() => (subscribedAgents.value.length
    ? `Connected to ${subscribedAgents.value.length} agent${subscribedAgents.value.length === 1 ? '' : 's'}`
    : 'Online'));

const input = ref('');
const sending = ref(false);
const messagesEnd = ref(null);
const textareaRef = ref(null);

/* ── Optimistic send — echo the user's message and a "thinking" bubble
   immediately instead of waiting on the round trip + prop refresh. ────── */
const localEcho = ref(null);

const displayMessages = computed(() => {
    const list = [...messages.value];
    if (localEcho.value) list.push(localEcho.value);
    return list;
});

/* ── Suggested prompts — shown only on a fresh, empty conversation ─────── */
const suggestions = [
    { icon: TrendCharts, text: "What's today's Arabica and Robusta price?" },
    { icon: Coffee, text: 'Summarize my active bids and their status.' },
    { icon: DocumentAdd, text: 'Draft a message to a supplier about a delayed shipment.' },
];

function useSuggestion(text) {
    input.value = text;
    nextTick(() => {
        textareaRef.value?.focus();
        resizeTextarea();
    });
}

const editingId = ref(null);
const editText = ref('');

function startEdit(msg) {
    editingId.value = msg.id;
    editText.value = msg.message;
}

function cancelEdit() {
    editingId.value = null;
    editText.value = '';
}

function saveEdit(msg) {
    const text = editText.value.trim();
    if (!text) return;

    router.patch(route('chat.update', msg.id), { message: text }, {
        preserveScroll: true,
        onSuccess: () => { editingId.value = null; editText.value = ''; },
    });
}

const confirmOpen = ref(false);
const pendingDelete = ref(null);

function deleteMessage(msg) {
    pendingDelete.value = msg;
    confirmOpen.value = true;
}

function confirmDeleteMessage() {
    if (!pendingDelete.value) return;
    router.delete(route('chat.destroy', pendingDelete.value.id), { preserveScroll: true });
    pendingDelete.value = null;
}

function scrollToBottom() {
    nextTick(() => {
        messagesEnd.value?.scrollIntoView({ behavior: 'smooth' });
    });
}

/* ── Auto-growing textarea, capped so it doesn't swallow the page ──────── */
function resizeTextarea() {
    const el = textareaRef.value;
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = `${Math.min(el.scrollHeight, 160)}px`;
}

function onEnter(event) {
    if (event.shiftKey) return;
    event.preventDefault();
    send();
}

function send() {
    const text = input.value.trim();
    if (!text || sending.value) return;

    localEcho.value = { id: 'pending', role: 'user', message: text, created_at: null, pending: true };
    input.value = '';
    sending.value = true;
    nextTick(resizeTextarea);
    scrollToBottom();

    router.post(route('chat.store'), { message: text }, {
        preserveScroll: true,
        onFinish: () => {
            sending.value = false;
            localEcho.value = null;
            scrollToBottom();
        },
    });
}

function formatTime(dateTime) {
    if (!dateTime) return '';
    const d = new Date(dateTime.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return '';
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

watch(() => messages.value.length, scrollToBottom, { immediate: true });
</script>

<template>
    <DesignPreviewLayout title="AI Chat">
        <Head title="AI Chat" />

        <div class="cpg-page">
            <div class="cpg-header">
                <div class="cpg-header__avatar"><el-icon><MagicStick /></el-icon></div>
                <div class="cpg-header__copy">
                    <div class="cpg-header__title">AI Assistant</div>
                    <div class="cpg-header__status"><i></i> {{ statusLabel }}</div>
                </div>
            </div>

            <div class="cpg-messages">
                <div v-if="!displayMessages.length" class="cpg-welcome">
                    <div class="cpg-welcome__avatar"><el-icon :size="26"><MagicStick /></el-icon></div>
                    <h1 class="cpg-welcome__title">How can I help you today?</h1>
                    <p class="cpg-welcome__text">Ask about pricing, lots, bids, or trade operations — your subscribed agents are ready.</p>

                    <div class="cpg-suggestions">
                        <button
                            v-for="s in suggestions"
                            :key="s.text"
                            type="button"
                            class="cpg-suggestion"
                            @click="useSuggestion(s.text)"
                        >
                            <el-icon :size="15"><component :is="s.icon" /></el-icon>
                            <span>{{ s.text }}</span>
                        </button>
                    </div>
                </div>

                <TransitionGroup name="cpg-fade" tag="div" class="cpg-list">
                    <div
                        v-for="msg in displayMessages"
                        :key="msg.id"
                        class="cpg-row"
                        :class="`cpg-row--${msg.role}`"
                    >
                        <div v-if="msg.role === 'assistant'" class="cpg-avatar cpg-avatar--bot">
                            <el-icon :size="14"><MagicStick /></el-icon>
                        </div>

                        <div class="cpg-msg-wrap">
                            <div v-if="editingId === msg.id" class="cpg-edit-box">
                                <el-input v-model="editText" type="textarea" :rows="2" autofocus @keydown.enter.exact.prevent="saveEdit(msg)" />
                                <div class="cpg-edit-actions">
                                    <button type="button" class="cpg-icon-btn" aria-label="Cancel edit" @click="cancelEdit">
                                        <el-icon><Close /></el-icon>
                                    </button>
                                    <button type="button" class="cpg-icon-btn cpg-icon-btn--primary" aria-label="Save edit" @click="saveEdit(msg)">
                                        <el-icon><Check /></el-icon>
                                    </button>
                                </div>
                            </div>

                            <template v-else>
                                <div class="cpg-msg" :class="`cpg-msg--${msg.role}`">{{ msg.message }}</div>
                                <div class="cpg-msg-footer">
                                    <span v-if="msg.created_at" class="cpg-msg-time">{{ formatTime(msg.created_at) }}</span>
                                    <div v-if="!msg.pending" class="cpg-msg-actions">
                                        <button v-if="msg.role === 'user'" type="button" class="cpg-msg-action" aria-label="Edit message" @click="startEdit(msg)">
                                            <el-icon><Edit /></el-icon>
                                        </button>
                                        <button type="button" class="cpg-msg-action" aria-label="Delete message" @click="deleteMessage(msg)">
                                            <el-icon><Delete /></el-icon>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div v-if="sending" key="typing" class="cpg-row cpg-row--assistant">
                        <div class="cpg-avatar cpg-avatar--bot">
                            <el-icon :size="14"><MagicStick /></el-icon>
                        </div>
                        <div class="cpg-msg-wrap">
                            <div class="cpg-typing">
                                <span></span><span></span><span></span>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>

                <div ref="messagesEnd"></div>
            </div>

            <div class="cpg-input-bar">
                <div class="cpg-input">
                    <textarea
                        ref="textareaRef"
                        v-model="input"
                        rows="1"
                        placeholder="Message your AI agents…"
                        :disabled="sending"
                        @input="resizeTextarea"
                        @keydown.enter="onEnter"
                    />
                    <button type="button" class="cpg-send-btn" :disabled="!input.trim() || sending" aria-label="Send message" @click="send">
                        <el-icon :size="16"><Promotion /></el-icon>
                    </button>
                </div>
                <p class="cpg-input-hint">AI responses may be inaccurate — verify anything critical before acting on it.</p>
            </div>
        </div>

        <ConfirmDialog
            v-model="confirmOpen"
            title="Delete Message"
            message="Delete this message? This can't be undone."
            confirm-text="Delete"
            @confirm="confirmDeleteMessage"
        />
    </DesignPreviewLayout>
</template>

<style scoped>
.cpg-page {
    --green: #004532;
    --green-light: #0a7a52;
    --border: #eef2f0;
    --on-surface: #111827;
    --on-surface-var: #6b7280;
    --surface-low: #f8fafc;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 3.5rem);
    background: #ffffff;
    font-family: 'Manrope', system-ui, sans-serif;
}

/* ── Header ──────────────────────────────────────────────────────────── */
.cpg-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 24px;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
    background: #ffffff;
}

.cpg-header__avatar {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--green), var(--green-light));
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 69, 50, 0.25);
}

.cpg-header__copy { min-width: 0; }

.cpg-header__title {
    font-size: 0.9375rem;
    font-weight: 800;
    color: var(--on-surface);
    letter-spacing: -0.01em;
}

.cpg-header__status {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.6875rem;
    font-weight: 600;
    color: var(--on-surface-var);
    margin-top: 1px;
}

.cpg-header__status i {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
    display: inline-block;
}

/* ── Messages ────────────────────────────────────────────────────────── */
.cpg-messages {
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    padding: 24px 20px 8px;
    max-width: 760px;
    width: 100%;
    margin: 0 auto;
}

.cpg-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

/* ── Empty / welcome state ───────────────────────────────────────────── */
.cpg-welcome {
    margin: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    max-width: 460px;
    padding: 2rem 1rem;
}

.cpg-welcome__avatar {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--green), var(--green-light));
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 20px rgba(0, 69, 50, 0.22);
    margin-bottom: 16px;
}

.cpg-welcome__title {
    font-size: 1.375rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--on-surface);
    margin: 0 0 6px;
}

.cpg-welcome__text {
    font-size: 0.8125rem;
    color: var(--on-surface-var);
    line-height: 1.6;
    margin: 0 0 22px;
}

.cpg-suggestions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
}

.cpg-suggestion {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 11px 14px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: #fff;
    color: var(--on-surface);
    font-size: 0.8125rem;
    font-weight: 600;
    text-align: left;
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease, transform 0.1s ease;
}

.cpg-suggestion:hover {
    border-color: var(--green);
    background: #f0f7f4;
    transform: translateY(-1px);
}

.cpg-suggestion :deep(.el-icon) { color: var(--green); flex-shrink: 0; }

/* ── Message rows ────────────────────────────────────────────────────── */
.cpg-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    width: 100%;
}

.cpg-row--user {
    justify-content: flex-end;
}

.cpg-avatar {
    width: 28px;
    height: 28px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
}

.cpg-avatar--bot {
    background: linear-gradient(135deg, var(--green), var(--green-light));
    color: #fff;
}

.cpg-msg-wrap {
    display: flex;
    flex-direction: column;
    max-width: 78%;
}

.cpg-row--user .cpg-msg-wrap { align-items: flex-end; }
.cpg-row--assistant .cpg-msg-wrap { align-items: flex-start; }

.cpg-msg {
    font-size: 0.875rem;
    line-height: 1.65;
    white-space: pre-wrap;
    word-break: break-word;
}

.cpg-msg--assistant {
    background: var(--surface-low);
    color: var(--on-surface);
    padding: 12px 16px;
    border-radius: 4px 16px 16px 16px;
    border: 1px solid var(--border);
}

.cpg-msg--user {
    background: linear-gradient(135deg, var(--green), var(--green-light));
    color: #fff;
    padding: 11px 16px;
    border-radius: 16px 4px 16px 16px;
    box-shadow: 0 4px 12px rgba(0, 69, 50, 0.18);
}

.cpg-msg-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 5px;
    min-height: 20px;
}

.cpg-row--user .cpg-msg-footer { flex-direction: row-reverse; }

.cpg-msg-time {
    font-size: 0.6875rem;
    color: var(--on-surface-var);
    opacity: 0.8;
}

.cpg-msg-actions {
    display: flex;
    gap: 2px;
    opacity: 0;
    transition: opacity 0.12s;
}

.cpg-row:hover .cpg-msg-actions { opacity: 1; }

.cpg-msg-action {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    border: none;
    background: none;
    color: #9ca3af;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
}

.cpg-msg-action:hover {
    background: var(--surface-low);
    color: var(--on-surface);
}

/* ── Typing indicator ────────────────────────────────────────────────── */
.cpg-typing {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 14px 16px;
    background: var(--surface-low);
    border: 1px solid var(--border);
    border-radius: 4px 16px 16px 16px;
    width: fit-content;
}

.cpg-typing span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--on-surface-var);
    opacity: 0.5;
    animation: cpg-bounce 1.2s infinite ease-in-out;
}

.cpg-typing span:nth-child(2) { animation-delay: 0.15s; }
.cpg-typing span:nth-child(3) { animation-delay: 0.3s; }

@keyframes cpg-bounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-4px); opacity: 1; }
}

/* ── Edit box ────────────────────────────────────────────────────────── */
.cpg-edit-box { width: 100%; min-width: 260px; }

.cpg-edit-actions {
    display: flex;
    justify-content: flex-end;
    gap: 6px;
    margin-top: 6px;
}

.cpg-icon-btn {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: #fff;
    color: var(--on-surface-var);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.cpg-icon-btn:hover { background: var(--surface-low); }

.cpg-icon-btn--primary {
    background: var(--green);
    border-color: var(--green);
    color: #fff;
}

.cpg-icon-btn--primary:hover { background: #002e20; }

/* ── Input bar ───────────────────────────────────────────────────────── */
.cpg-input-bar {
    flex-shrink: 0;
    padding: 12px 20px 18px;
    max-width: 760px;
    width: 100%;
    margin: 0 auto;
}

.cpg-input {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    padding: 8px 8px 8px 18px;
    background: #fff;
    border: 1.5px solid var(--border);
    border-radius: 22px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.cpg-input:focus-within {
    border-color: var(--green);
    box-shadow: 0 4px 20px rgba(0, 69, 50, 0.12);
}

.cpg-input textarea {
    flex: 1;
    resize: none;
    border: none;
    outline: none;
    background: none;
    font-family: inherit;
    font-size: 0.875rem;
    line-height: 1.5;
    color: var(--on-surface);
    padding: 8px 0;
    max-height: 160px;
}

.cpg-input textarea::placeholder { color: #9ca3af; }
.cpg-input textarea:disabled { opacity: 0.6; }

.cpg-send-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, var(--green), var(--green-light));
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: opacity 0.15s ease, transform 0.1s ease;
}

.cpg-send-btn:hover:not(:disabled) { transform: scale(1.05); }
.cpg-send-btn:disabled { opacity: 0.35; cursor: default; }

.cpg-input-hint {
    text-align: center;
    font-size: 0.6875rem;
    color: var(--on-surface-var);
    opacity: 0.75;
    margin: 8px 0 0;
}

/* ── Transitions ─────────────────────────────────────────────────────── */
.cpg-fade-enter-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.cpg-fade-enter-from { opacity: 0; transform: translateY(6px); }
.cpg-fade-leave-active { transition: opacity 0.12s ease; position: absolute; }
.cpg-fade-leave-to { opacity: 0; }

/* ── Responsive ──────────────────────────────────────────────────────── */
@media (max-width: 640px) {
    .cpg-messages { padding: 18px 14px 8px; }
    .cpg-input-bar { padding: 10px 14px 14px; }
    .cpg-msg-wrap { max-width: 88%; }
}
</style>
