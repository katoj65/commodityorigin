<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { ChatDotRound, Promotion, Edit, Delete, Check, Close } from '@element-plus/icons-vue';

const page = usePage();
const messages = computed(() => page.props.chatMessages ?? []);

const input = ref('');
const sending = ref(false);
const messagesEnd = ref(null);

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

function send() {
    const text = input.value.trim();
    if (!text || sending.value) return;

    input.value = '';
    sending.value = true;

    router.post(route('chat.store'), { message: text }, {
        preserveScroll: true,
        onFinish: () => { sending.value = false; scrollToBottom(); },
    });
}

watch(() => messages.value.length, scrollToBottom, { immediate: true });
</script>

<template>
    <AppLayout title="AI Chat" full-width flush :show-banner="false">
        <Head title="AI Chat" />

        <div class="cpg-page">
            <div class="cpg-header">
                <div class="cpg-header__avatar"><el-icon><ChatDotRound /></el-icon></div>
                <div>
                    <div class="cpg-header__title">AI Assistant</div>
                    <div class="cpg-header__status"><i></i> Online</div>
                </div>
            </div>

            <div class="cpg-messages">
                <div v-if="!messages.length" class="cpg-empty">
                    Ask anything — your subscribed agents are ready to help.
                </div>

                <div
                    v-for="msg in messages"
                    :key="msg.id"
                    class="cpg-row"
                    :class="`cpg-row--${msg.role}`"
                >
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
                            <div class="cpg-msg-actions">
                                <button v-if="msg.role === 'user'" type="button" class="cpg-msg-action" aria-label="Edit message" @click="startEdit(msg)">
                                    <el-icon><Edit /></el-icon>
                                </button>
                                <button type="button" class="cpg-msg-action" aria-label="Delete message" @click="deleteMessage(msg)">
                                    <el-icon><Delete /></el-icon>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div ref="messagesEnd"></div>
            </div>

            <div class="cpg-input">
                <el-input
                    v-model="input"
                    placeholder="Message your AI agents…"
                    :disabled="sending"
                    @keydown.enter="send"
                />
                <el-button type="primary" circle :disabled="!input.trim() || sending" @click="send">
                    <el-icon><Promotion /></el-icon>
                </el-button>
            </div>
        </div>

        <ConfirmDialog
            v-model="confirmOpen"
            title="Delete Message"
            message="Delete this message? This can't be undone."
            confirm-text="Delete"
            @confirm="confirmDeleteMessage"
        />
    </AppLayout>
</template>

<style scoped>
.cpg-page {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 3.5rem);
    background: var(--surface, #f7f9fb);
    font-family: 'Manrope', system-ui, sans-serif;
}

.cpg-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.cpg-header__avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #004532;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.cpg-header__title {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #111827;
}

.cpg-header__status {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.6875rem;
    color: #6b7280;
}

.cpg-header__status i {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #22c55e;
    display: inline-block;
}

.cpg-messages {
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 20px;
    max-width: 720px;
    width: 100%;
    margin: 0 auto;
}

.cpg-empty {
    margin: auto;
    color: #6b7280;
    font-size: 0.875rem;
    text-align: center;
}

.cpg-row {
    display: flex;
    width: 100%;
}

.cpg-row--assistant {
    justify-content: flex-start;
}

.cpg-row--user {
    justify-content: flex-end;
}

.cpg-msg-wrap {
    display: flex;
    flex-direction: column;
    max-width: 80%;
}

.cpg-row--user .cpg-msg-wrap {
    align-items: flex-end;
}

.cpg-row--assistant .cpg-msg-wrap {
    align-items: flex-start;
}

.cpg-msg {
    font-size: 0.875rem;
    line-height: 1.6;
}

.cpg-msg--assistant {
    background: none;
    color: #111827;
    padding: 0;
}

.cpg-msg--user {
    background: #f4f4f5;
    color: #111827;
    padding: 10px 15px;
    border-radius: 18px;
}

.cpg-msg-actions {
    display: flex;
    gap: 4px;
    margin-top: 4px;
    opacity: 0.5;
    transition: opacity 0.12s;
}

.cpg-msg-actions:hover {
    opacity: 1;
}

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
    background: #f4f4f5;
    color: #111827;
}

.cpg-edit-box {
    width: 100%;
}

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
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.cpg-icon-btn:hover {
    background: #f8fafc;
}

.cpg-icon-btn--primary {
    background: #004532;
    border-color: #004532;
    color: #fff;
}

.cpg-icon-btn--primary:hover {
    background: #002e20;
}

.cpg-input {
    display: flex;
    gap: 10px;
    align-items: center;
    padding: 14px 20px;
    border-top: 1px solid #e5e7eb;
    max-width: 720px;
    width: 100%;
    margin: 0 auto;
    flex-shrink: 0;
}
</style>
