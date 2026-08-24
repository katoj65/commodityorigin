<script setup>
import { Delete, WarningFilled } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    title: { type: String, default: 'Are you sure?' },
    message: { type: String, default: '' },
    confirmText: { type: String, default: 'Delete' },
    cancelText: { type: String, default: 'Cancel' },
    danger: { type: Boolean, default: true },
    icon: { type: String, default: 'delete' },
});

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);

function close() {
    emit('update:modelValue', false);
    emit('cancel');
}

function confirm() {
    emit('update:modelValue', false);
    emit('confirm');
}
</script>

<template>
    <Teleport to="body">
        <Transition name="cfd-fade">
            <div v-if="props.modelValue" class="cfd-overlay" @click.self="close">
                <Transition name="cfd-pop" appear>
                    <div v-if="props.modelValue" class="cfd-panel" role="alertdialog" aria-modal="true">
                        <div class="cfd-icon" :class="danger ? 'cfd-icon--danger' : 'cfd-icon--warn'">
                            <el-icon :size="24"><component :is="icon === 'warning' ? WarningFilled : Delete" /></el-icon>
                        </div>

                        <h3 class="cfd-title">{{ title }}</h3>
                        <p v-if="message" class="cfd-message">{{ message }}</p>

                        <div class="cfd-actions">
                            <button type="button" class="cfd-btn cfd-btn--ghost" @click="close">{{ cancelText }}</button>
                            <button type="button" class="cfd-btn" :class="danger ? 'cfd-btn--danger' : 'cfd-btn--primary'" @click="confirm">
                                {{ confirmText }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.cfd-overlay {
    position: fixed;
    inset: 0;
    z-index: 2000;
    background: rgba(17, 24, 39, 0.55);
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.cfd-panel {
    width: 100%;
    max-width: 380px;
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.25), 0 4px 16px rgba(0, 0, 0, 0.08);
    padding: 28px 28px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    font-family: 'Manrope', system-ui, sans-serif;
}

.cfd-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}

.cfd-icon--danger {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #dc2626;
    box-shadow: 0 0 0 6px rgba(220, 38, 38, 0.06);
}

.cfd-icon--warn {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #b45309;
    box-shadow: 0 0 0 6px rgba(180, 83, 9, 0.06);
}

.cfd-title {
    font-size: 1.0625rem;
    font-weight: 800;
    color: #111827;
    letter-spacing: -0.01em;
    margin: 0 0 6px;
}

.cfd-message {
    font-size: 0.8125rem;
    color: #6b7280;
    line-height: 1.55;
    margin: 0;
}

.cfd-actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
    width: 100%;
}

.cfd-btn {
    flex: 1;
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 0.8125rem;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid transparent;
    transition: transform 0.08s ease, background 0.12s ease, box-shadow 0.12s ease;
}

.cfd-btn:active {
    transform: scale(0.97);
}

.cfd-btn--ghost {
    background: #f8fafc;
    border-color: #e5e7eb;
    color: #374151;
}

.cfd-btn--ghost:hover {
    background: #f1f5f9;
}

.cfd-btn--danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3);
}

.cfd-btn--danger:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.cfd-btn--primary {
    background: linear-gradient(135deg, #065f46, #004532);
    color: #fff;
    box-shadow: 0 6px 16px rgba(0, 69, 50, 0.3);
}

.cfd-btn--primary:hover {
    background: linear-gradient(135deg, #004532, #002e20);
}

/* ── Transitions ──────────────────────────────────────────────────────── */
.cfd-fade-enter-active,
.cfd-fade-leave-active {
    transition: opacity 0.18s ease;
}

.cfd-fade-enter-from,
.cfd-fade-leave-to {
    opacity: 0;
}

.cfd-pop-enter-active {
    transition: opacity 0.2s ease, transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.cfd-pop-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.cfd-pop-enter-from,
.cfd-pop-leave-to {
    opacity: 0;
    transform: scale(0.92) translateY(6px);
}
</style>
