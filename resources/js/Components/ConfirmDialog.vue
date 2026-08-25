<script setup>
import { Delete, WarningFilled } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    eyebrow: { type: String, default: '' },
    title: { type: String, default: 'Are you sure?' },
    message: { type: String, default: '' },
    confirmText: { type: String, default: 'Delete' },
    cancelText: { type: String, default: 'Cancel' },
    danger: { type: Boolean, default: true },
    icon: { type: String, default: 'delete' },
    loading: { type: Boolean, default: false },
    loadingText: { type: String, default: 'Deleting…' },
    // Every existing caller fires-and-forgets: @confirm fires, the dialog
    // closes immediately, the request runs in the background. Callers that
    // need the dialog to stay open with a loading state (e.g. a delete that
    // must finish before navigating away) pass :auto-close="false" and close
    // the v-model themselves once their async work settles.
    autoClose: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);

function close() {
    if (props.loading) return;
    emit('update:modelValue', false);
    emit('cancel');
}

function confirm() {
    if (props.autoClose) {
        emit('update:modelValue', false);
    }
    emit('confirm');
}
</script>

<template>
    <Teleport to="body">
        <Transition name="cfd-fade">
            <div v-if="props.modelValue" class="cfd-overlay" @click.self="close">
                <Transition name="cfd-pop" appear>
                    <div v-if="props.modelValue" class="cfd-panel" role="alertdialog" aria-modal="true">
                        <div class="cfd-head">
                            <div class="cfd-icon" :class="danger ? 'cfd-icon--danger' : 'cfd-icon--warn'">
                                <el-icon :size="18"><component :is="icon === 'warning' ? WarningFilled : Delete" /></el-icon>
                            </div>
                            <div class="cfd-head-text">
                                <div v-if="eyebrow" class="cfd-eyebrow">{{ eyebrow }}</div>
                                <h3 class="cfd-title">{{ title }}</h3>
                            </div>
                        </div>

                        <p v-if="message" class="cfd-message">{{ message }}</p>

                        <div class="cfd-actions">
                            <button type="button" class="cfd-btn cfd-btn--ghost" :disabled="loading" @click="close">{{ cancelText }}</button>
                            <button type="button" class="cfd-btn" :class="danger ? 'cfd-btn--danger' : 'cfd-btn--primary'" :disabled="loading" @click="confirm">
                                {{ loading ? loadingText : confirmText }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* Standing app-wide delete-confirmation style (2026-08-25) — icon-box +
   eyebrow/title header row, confirm-text body, Cancel/Delete footer.
   See reference_ui_md_design_system / feedback_inventory_creation_modals
   memory: literal UI.md hex tokens, matching every other modal's chrome. */
.cfd-overlay {
    position: fixed;
    inset: 0;
    z-index: 2000;
    background: rgba(17, 24, 39, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.cfd-panel {
    width: 100%;
    max-width: 420px;
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
    overflow: hidden;
    text-align: left;
    font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.cfd-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    background: #fff;
    border-bottom: 1px solid #E5E7EB;
}
.cfd-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.cfd-icon--danger { background: #FEEDED; color: #C6413A; }
.cfd-icon--warn { background: #fef3c7; color: #92400e; }
.cfd-head-text { flex: 1; min-width: 0; }
.cfd-eyebrow {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6F7677;
    margin-bottom: 1px;
}
.cfd-title { font-size: 1.0625rem; font-weight: 700; color: #121516; letter-spacing: -0.01em; margin: 0; }

.cfd-message { font-size: 0.875rem; color: #4B5457; line-height: 1.6; margin: 0; padding: 22px 24px; }

.cfd-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    background: #F5F6F7;
    border-top: 1px solid #E5E7EB;
}
.cfd-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    padding: 0 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid transparent;
    transition: opacity 0.15s ease, background 0.15s ease;
}
.cfd-btn:disabled { opacity: 0.5; cursor: default; }

.cfd-btn--ghost { background: #fff; border-color: #E5E7EB; color: #121516; }
.cfd-btn--ghost:hover:not(:disabled) { background: #F5F6F7; }

.cfd-btn--danger { background: #F85149; color: #fff; }
.cfd-btn--danger:hover:not(:disabled) { opacity: 0.88; }

.cfd-btn--primary { background: #000000; color: #fff; }
.cfd-btn--primary:hover:not(:disabled) { opacity: 0.88; }

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
    transform: scale(0.96) translateY(4px);
}
</style>
