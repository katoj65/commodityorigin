<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { ChatDotRound, Promotion, FullScreen, Edit, Delete, Check, Close } from '@element-plus/icons-vue';

const page = usePage();
const aiChatMessages = computed(() => page.props.chatMessages ?? []);

const AI_CHAT_OPEN_STORAGE_KEY = 'aiChatOpen';

const aiChatOpen = ref(localStorage.getItem(AI_CHAT_OPEN_STORAGE_KEY) === 'true');
const aiChatInput = ref('');
const aiChatSending = ref(false);
const aiChatMessagesEnd = ref(null);

watch(aiChatOpen, (isOpen) => {
    localStorage.setItem(AI_CHAT_OPEN_STORAGE_KEY, isOpen ? 'true' : 'false');
});

function scrollAiChatToBottom() {
    nextTick(() => {
        aiChatMessagesEnd.value?.scrollIntoView({ behavior: 'auto' });
    });
}

function sendAiChatMessage() {
    const text = aiChatInput.value.trim();
    if (!text || aiChatSending.value) return;

    aiChatInput.value = '';
    aiChatSending.value = true;

    router.post(route('chat.store'), { message: text }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => { aiChatSending.value = false; scrollAiChatToBottom(); },
    });
}

watch(() => aiChatMessages.value.length, scrollAiChatToBottom);

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
        preserveState: true,
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
    router.delete(route('chat.destroy', pendingDelete.value.id), { preserveScroll: true, preserveState: true });
    pendingDelete.value = null;
}

onMounted(() => {
    if (aiChatOpen.value) {
        scrollAiChatToBottom();
    }
});
</script>

<template>
    <div class="ai-chat-wrap">
        <Transition name="ai-chat-pop">
            <div v-if="aiChatOpen" class="ai-chat-window">
                <div class="ai-chat-window__head">
                    <div class="ai-chat-window__identity">
                        <div class="ai-chat-window__avatar"><el-icon><ChatDotRound /></el-icon></div>
                        <div>
                            <div class="ai-chat-window__name">AI Assistant</div>
                            <div class="ai-chat-window__status"><i></i> Online</div>
                        </div>
                    </div>
                    <div class="ai-chat-window__actions">
                        <Link :href="route('chat.index')" class="ai-chat-window__icon-btn" aria-label="Open full chat page" title="Open full chat page">
                            <el-icon :size="15"><FullScreen /></el-icon>
                        </Link>
                        <button class="ai-chat-window__icon-btn" aria-label="Close chat" @click="aiChatOpen = false">×</button>
                    </div>
                </div>

                <div class="ai-chat-panel__messages">
                    <p v-if="!aiChatMessages.length" class="ai-chat-panel__intro">Chat with your subscribed AI agents.</p>

                    <div
                        v-for="msg in aiChatMessages"
                        :key="msg.id"
                        class="ai-chat-row"
                        :class="`ai-chat-row--${msg.role}`"
                    >
                        <div class="ai-chat-msg-wrap">
                            <div v-if="editingId === msg.id" class="ai-chat-edit-box">
                                <el-input v-model="editText" type="textarea" :rows="2" autofocus @keydown.enter.exact.prevent="saveEdit(msg)" />
                                <div class="ai-chat-edit-actions">
                                    <button type="button" class="ai-chat-mini-btn" aria-label="Cancel edit" @click="cancelEdit">
                                        <el-icon :size="12"><Close /></el-icon>
                                    </button>
                                    <button type="button" class="ai-chat-mini-btn ai-chat-mini-btn--primary" aria-label="Save edit" @click="saveEdit(msg)">
                                        <el-icon :size="12"><Check /></el-icon>
                                    </button>
                                </div>
                            </div>

                            <template v-else>
                                <div class="ai-chat-msg" :class="`ai-chat-msg--${msg.role}`">{{ msg.message }}</div>
                                <div class="ai-chat-msg-actions">
                                    <button v-if="msg.role === 'user'" type="button" class="ai-chat-msg-action" aria-label="Edit message" @click="startEdit(msg)">
                                        <el-icon :size="11"><Edit /></el-icon>
                                    </button>
                                    <button type="button" class="ai-chat-msg-action" aria-label="Delete message" @click="deleteMessage(msg)">
                                        <el-icon :size="11"><Delete /></el-icon>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div ref="aiChatMessagesEnd"></div>
                </div>

                <div class="ai-chat-panel__input">
                    <el-input
                        v-model="aiChatInput"
                        placeholder="Ask your AI agent…"
                        :disabled="aiChatSending"
                        @keydown.enter="sendAiChatMessage"
                    />
                    <el-button type="primary" circle :disabled="!aiChatInput.trim() || aiChatSending" @click="sendAiChatMessage">
                        <el-icon><Promotion /></el-icon>
                    </el-button>
                </div>
            </div>
        </Transition>

        <Transition name="ai-chat-fab-pop">
            <el-button
                v-if="!aiChatOpen"
                type="primary"
                circle
                class="ai-chat-fab"
                aria-label="Open AI chat"
                @click="aiChatOpen = true"
            >
                <el-icon :size="22"><ChatDotRound /></el-icon>
            </el-button>
        </Transition>
    </div>

    <ConfirmDialog
        v-model="confirmOpen"
        title="Delete Message"
        message="Delete this message? This can't be undone."
        confirm-text="Delete"
        @confirm="confirmDeleteMessage"
    />
</template>

<style scoped>
.ai-chat-wrap {
    position: relative;
    z-index: 200;
}

.ai-chat-fab {
    position: fixed;
    right: 1.5rem;
    bottom: 1.5rem;
    width: 52px;
    height: 52px;
    flex-shrink: 0;
    background: #004532;
    border-color: #004532;
    box-shadow: 0 8px 24px rgba(0, 69, 50, 0.35);
}

.ai-chat-fab:hover,
.ai-chat-fab:focus {
    background: #002e20;
    border-color: #002e20;
}

.ai-chat-window {
    position: fixed;
    right: 1.5rem;
    bottom: 0;
    width: 340px;
    max-width: calc(100vw - 3rem);
    height: min(547px, calc((100vh - 4rem) * 0.855));
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 18px 18px 0 0;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    border-bottom: none;
    box-shadow: 0 20px 50px rgba(17, 24, 39, 0.18);
}

.ai-chat-window__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    background: #004532;
    color: #fff;
    flex-shrink: 0;
}

.ai-chat-window__identity {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ai-chat-window__avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}

.ai-chat-window__name {
    font-size: 0.875rem;
    font-weight: 700;
}

.ai-chat-window__status {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.625rem;
    opacity: 0.85;
}

.ai-chat-window__status i {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #4ade80;
    display: inline-block;
}

.ai-chat-window__actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ai-chat-window__icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: none;
    color: rgba(255, 255, 255, 0.85);
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    padding: 2px 4px;
    text-decoration: none;
}

.ai-chat-window__icon-btn:hover {
    color: #fff;
}

.ai-chat-panel__intro {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0;
}

.ai-chat-panel__messages {
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 10px 14px;
}

.ai-chat-row {
    display: flex;
    width: 100%;
}

.ai-chat-row--assistant {
    justify-content: flex-start;
}

.ai-chat-row--user {
    justify-content: flex-end;
}

.ai-chat-msg-wrap {
    display: flex;
    flex-direction: column;
    max-width: 85%;
}

.ai-chat-row--user .ai-chat-msg-wrap {
    align-items: flex-end;
}

.ai-chat-row--assistant .ai-chat-msg-wrap {
    align-items: flex-start;
}

.ai-chat-msg {
    font-size: 0.8125rem;
    line-height: 1.6;
}

.ai-chat-msg--assistant {
    background: none;
    color: #111827;
    padding: 0;
}

.ai-chat-msg--user {
    background: #f4f4f5;
    color: #111827;
    padding: 9px 14px;
    border-radius: 18px;
}

.ai-chat-msg-actions {
    display: flex;
    gap: 3px;
    margin-top: 3px;
    opacity: 0.5;
    transition: opacity 0.12s;
}

.ai-chat-msg-actions:hover {
    opacity: 1;
}

.ai-chat-msg-action {
    width: 18px;
    height: 18px;
    border-radius: 5px;
    border: none;
    background: none;
    color: #9ca3af;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.ai-chat-msg-action:hover {
    background: #f4f4f5;
    color: #111827;
}

.ai-chat-edit-box {
    width: 100%;
}

.ai-chat-edit-actions {
    display: flex;
    justify-content: flex-end;
    gap: 5px;
    margin-top: 5px;
}

.ai-chat-mini-btn {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.ai-chat-mini-btn:hover {
    background: #f8fafc;
}

.ai-chat-mini-btn--primary {
    background: #004532;
    border-color: #004532;
    color: #fff;
}

.ai-chat-mini-btn--primary:hover {
    background: #002e20;
}

.ai-chat-panel__input {
    display: flex;
    gap: 8px;
    align-items: center;
    padding: 10px 14px;
    border-top: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.ai-chat-pop-enter-active,
.ai-chat-pop-leave-active {
    transition: opacity 0.22s ease, transform 0.22s ease;
    transform-origin: bottom right;
}

.ai-chat-pop-enter-from,
.ai-chat-pop-leave-to {
    opacity: 0;
    transform: translateY(32px) scale(0.96);
}

.ai-chat-fab-pop-enter-active,
.ai-chat-fab-pop-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.ai-chat-fab-pop-enter-from,
.ai-chat-fab-pop-leave-to {
    opacity: 0;
    transform: scale(0.8);
}
</style>
