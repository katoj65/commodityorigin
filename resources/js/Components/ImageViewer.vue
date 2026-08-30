<script setup>
import { computed, ref, watch, onBeforeUnmount } from 'vue';
import { ArrowLeft, ArrowRight, Close, Download } from '@element-plus/icons-vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    images: {
        // Array of { url: String, alt?: String, caption?: String }
        type: Array,
        default: () => [],
    },
    index: { type: Number, default: 0 },
    downloadable: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'update:index']);

const visible = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const activeIndex = ref(props.index);
const hasMultiple = computed(() => props.images.length > 1);
const current = computed(() => props.images[activeIndex.value] || null);

watch(() => props.modelValue, (open) => {
    if (open) {
        activeIndex.value = Math.min(Math.max(props.index, 0), Math.max(props.images.length - 1, 0));
        window.addEventListener('keydown', onKeydown);
    } else {
        window.removeEventListener('keydown', onKeydown);
    }
});

onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));

function onKeydown(e) {
    if (e.key === 'Escape') close();
    else if (e.key === 'ArrowLeft') prev();
    else if (e.key === 'ArrowRight') next();
}

function close() {
    visible.value = false;
}

function prev() {
    if (!hasMultiple.value) return;
    activeIndex.value = (activeIndex.value - 1 + props.images.length) % props.images.length;
    emit('update:index', activeIndex.value);
}

function next() {
    if (!hasMultiple.value) return;
    activeIndex.value = (activeIndex.value + 1) % props.images.length;
    emit('update:index', activeIndex.value);
}

function goTo(i) {
    activeIndex.value = i;
    emit('update:index', i);
}
</script>

<template>
    <Teleport to="body">
        <Transition name="iv-fade">
            <div v-if="visible" class="iv-overlay" role="dialog" aria-modal="true" @click.self="close">
                <div class="iv-topbar">
                    <span v-if="hasMultiple" class="iv-counter">{{ activeIndex + 1 }} / {{ images.length }}</span>
                    <span v-else class="iv-counter"></span>
                    <div class="iv-topbar__actions">
                        <a
                            v-if="downloadable && current"
                            :href="current.url"
                            :download="current.alt || 'photo'"
                            class="iv-icon-btn"
                            target="_blank"
                            rel="noopener"
                            @click.stop
                        >
                            <el-icon :size="16"><Download /></el-icon>
                        </a>
                        <button type="button" class="iv-icon-btn" aria-label="Close" @click="close">
                            <el-icon :size="18"><Close /></el-icon>
                        </button>
                    </div>
                </div>

                <button
                    v-if="hasMultiple"
                    type="button"
                    class="iv-nav iv-nav--prev"
                    aria-label="Previous image"
                    @click.stop="prev"
                >
                    <el-icon :size="20"><ArrowLeft /></el-icon>
                </button>

                <Transition name="iv-swap" mode="out-in">
                    <figure v-if="current" :key="activeIndex" class="iv-stage" @click.self="close">
                        <img :src="current.url" :alt="current.alt || ''" class="iv-image" @click.stop />
                        <figcaption v-if="current.caption" class="iv-caption">{{ current.caption }}</figcaption>
                    </figure>
                </Transition>

                <button
                    v-if="hasMultiple"
                    type="button"
                    class="iv-nav iv-nav--next"
                    aria-label="Next image"
                    @click.stop="next"
                >
                    <el-icon :size="20"><ArrowRight /></el-icon>
                </button>

                <div v-if="hasMultiple" class="iv-strip" @click.self="close">
                    <button
                        v-for="(img, i) in images"
                        :key="i"
                        type="button"
                        class="iv-thumb"
                        :class="{ 'iv-thumb--active': i === activeIndex }"
                        @click.stop="goTo(i)"
                    >
                        <img :src="img.url" :alt="img.alt || ''" />
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* An independent, professional full-screen lightbox — dark backdrop,
   minimal chrome, keyboard + click navigation. Font stack and radii match
   the rest of the app (Inter, 6-10px radii); dark theme is intentional
   here since it's the conventional treatment for photo viewers and keeps
   focus on the image regardless of which page's palette invoked it. */
.iv-overlay {
    position: fixed;
    inset: 0;
    z-index: 3000;
    background: rgba(9, 9, 11, 0.94);
    backdrop-filter: blur(6px);
    display: flex;
    flex-direction: column;
    font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.iv-topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; flex-shrink: 0;
}
.iv-counter {
    font-size: 12.5px; font-weight: 600; color: rgba(255, 255, 255, 0.65);
    font-variant-numeric: tabular-nums; letter-spacing: 0.02em;
}
.iv-topbar__actions { display: flex; align-items: center; gap: 8px; }
.iv-icon-btn {
    width: 34px; height: 34px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.12);
    color: rgba(255, 255, 255, 0.85); cursor: pointer;
    transition: background 120ms ease, color 120ms ease;
    text-decoration: none;
}
.iv-icon-btn:hover { background: rgba(255, 255, 255, 0.14); color: #fff; }

.iv-stage {
    flex: 1; min-height: 0; margin: 0;
    display: flex; align-items: center; justify-content: center;
    padding: 0 76px;
}
.iv-image {
    max-width: 100%; max-height: 100%; object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.5);
    cursor: default;
}
.iv-caption {
    position: absolute; bottom: 96px; left: 0; right: 0;
    text-align: center; font-size: 12.5px; color: rgba(255, 255, 255, 0.7);
}

.iv-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.12);
    color: #fff; cursor: pointer; z-index: 1;
    transition: background 120ms ease, transform 120ms ease;
}
.iv-nav:hover { background: rgba(255, 255, 255, 0.16); }
.iv-nav--prev { left: 20px; }
.iv-nav--next { right: 20px; }

.iv-strip {
    display: flex; justify-content: center; gap: 8px;
    padding: 14px 20px 20px; flex-shrink: 0;
    overflow-x: auto;
}
.iv-thumb {
    width: 52px; height: 52px; border-radius: 7px; overflow: hidden; flex-shrink: 0;
    border: 2px solid transparent; padding: 0; cursor: pointer;
    opacity: 0.5; transition: opacity 120ms ease, border-color 120ms ease;
}
.iv-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.iv-thumb:hover { opacity: 0.85; }
.iv-thumb--active { opacity: 1; border-color: #fff; }

/* ── Transitions ──────────────────────────────────────────────────────── */
.iv-fade-enter-active, .iv-fade-leave-active { transition: opacity 0.2s ease; }
.iv-fade-enter-from, .iv-fade-leave-to { opacity: 0; }

.iv-swap-enter-active, .iv-swap-leave-active { transition: opacity 0.15s ease; }
.iv-swap-enter-from, .iv-swap-leave-to { opacity: 0; }

@media (max-width: 640px) {
    .iv-stage { padding: 0 16px; }
    .iv-nav { width: 38px; height: 38px; }
    .iv-nav--prev { left: 6px; }
    .iv-nav--next { right: 6px; }
    .iv-strip { display: none; }
}
</style>
