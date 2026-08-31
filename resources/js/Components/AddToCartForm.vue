<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { ElNotification } from 'element-plus';
import { ShoppingBag, Minus, Plus } from '@element-plus/icons-vue';

/* ── A self-contained "add N of this to the cart" widget — the same
   checkout.items.store endpoint the market grid uses, just wrapped in a
   real e-commerce style form (quantity stepper + submit) instead of a
   single fixed-quantity button. Drop it anywhere a Market or
   AgriculturalInput listing needs a buy widget. ─────────────────────── */
const props = defineProps({
    cartableType: { type: String, default: 'market' },
    cartableId: { type: [Number, String], required: true },
    itemName: { type: String, default: 'this item' },
    unit: { type: String, default: 'unit' },
    max: { type: Number, default: null },
});

const emit = defineEmits(['added']);

const quantity = ref(1);
const submitting = ref(false);

const atMax = computed(() => props.max != null && quantity.value >= props.max);

function decrement() {
    if (quantity.value > 1) quantity.value -= 1;
}

function increment() {
    if (atMax.value) return;
    quantity.value += 1;
}

function clampInput() {
    let value = Math.floor(Number(quantity.value)) || 1;
    if (value < 1) value = 1;
    if (props.max != null && value > props.max) value = props.max;
    quantity.value = value;
}

function submit() {
    clampInput();
    submitting.value = true;

    router.post(route('checkout.items.store'), {
        cartable_type: props.cartableType,
        cartable_id: props.cartableId,
        quantity: quantity.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            ElNotification({
                title: 'Added to Cart',
                message: `Added ${quantity.value} ${props.unit} of ${props.itemName} to your cart.`,
                type: 'success',
                duration: 3200,
                offset: 84,
            });
            emit('added', quantity.value);
            quantity.value = 1;
        },
        onError: (errors) => {
            ElNotification({
                title: 'Could Not Add to Cart',
                message: Object.values(errors)[0] || 'Please try again.',
                type: 'error',
                duration: 3600,
                offset: 84,
            });
        },
        onFinish: () => { submitting.value = false; },
    });
}
</script>

<template>
    <form class="atc-form" @submit.prevent="submit">
        <div class="atc-form__stepper">
            <button
                type="button"
                class="atc-form__step-btn"
                :disabled="quantity <= 1"
                aria-label="Decrease quantity"
                @click="decrement"
            >
                <el-icon :size="13"><Minus /></el-icon>
            </button>
            <div class="atc-form__input-wrap">
                <input
                    v-model.number="quantity"
                    type="number"
                    min="1"
                    :max="max ?? undefined"
                    class="atc-form__input"
                    aria-label="Quantity"
                    @change="clampInput"
                >
                <span class="atc-form__unit">{{ unit }}</span>
            </div>
            <button
                type="button"
                class="atc-form__step-btn"
                :disabled="atMax"
                aria-label="Increase quantity"
                @click="increment"
            >
                <el-icon :size="13"><Plus /></el-icon>
            </button>
        </div>

        <button type="submit" class="atc-form__submit" :disabled="submitting">
            <el-icon :size="15"><ShoppingBag /></el-icon>
            <span>{{ submitting ? 'Adding…' : 'Add to Cart' }}</span>
        </button>

        <p v-if="max != null" class="atc-form__availability" :class="{ 'atc-form__availability--low': max <= 10 }">
            {{ max }} {{ unit }} available
        </p>
    </form>
</template>

<style scoped>
.atc-form {
    display: flex; flex-direction: column; gap: 12px; width: 100%;
}

.atc-form__stepper {
    display: flex; flex-wrap: nowrap; align-items: stretch; width: 100%;
    height: 44px; border: 1px solid var(--card-border, var(--dp-outline-variant));
    border-radius: 10px; overflow: hidden; background: var(--dp-surface-container);
    transition: border-color .15s ease, box-shadow .15s ease;
}
.atc-form__stepper:focus-within { border-color: var(--dp-primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--dp-primary) 16%, transparent); }

.atc-form__step-btn {
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    width: 44px; border: none; background: transparent;
    color: var(--dp-on-surface-variant); cursor: pointer; transition: background .15s ease, color .15s ease;
}
.atc-form__step-btn:hover:not(:disabled) { background: var(--dp-surface-container-high); color: var(--dp-primary); }
.atc-form__step-btn:active:not(:disabled) { transform: scale(.9); }
.atc-form__step-btn:disabled { opacity: .3; cursor: default; }

.atc-form__input-wrap {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px;
    border-left: 1px solid var(--card-border, var(--dp-outline-variant));
    border-right: 1px solid var(--card-border, var(--dp-outline-variant));
    min-width: 0;
}

.atc-form__input {
    width: 40px; flex-shrink: 0; border: none;
    background: transparent; color: var(--dp-on-surface); font-size: .9375rem; font-weight: 800;
    text-align: right; -moz-appearance: textfield; padding: 0;
}
.atc-form__input::-webkit-outer-spin-button,
.atc-form__input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.atc-form__input:focus { outline: none; }

.atc-form__unit {
    flex-shrink: 0; font-size: .75rem; font-weight: 700; color: var(--dp-on-surface-variant);
}

.atc-form__submit {
    width: 100%; min-width: 0; display: inline-flex; align-items: center; justify-content: center; gap: 7px;
    height: 46px; padding: 0 14px; border: none; border-radius: 999px; background: var(--dp-primary); color: var(--dp-on-primary);
    font-size: .875rem; font-weight: 800; letter-spacing: .01em; cursor: pointer;
    box-shadow: 0 6px 16px -8px rgba(39, 19, 16, .45);
    transition: opacity .15s ease, transform .15s ease, box-shadow .15s ease;
}
.atc-form__submit span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.atc-form__submit:hover:not(:disabled) { opacity: .92; transform: translateY(-1px); box-shadow: 0 8px 18px -8px rgba(39, 19, 16, .55); }
.atc-form__submit:active:not(:disabled) { transform: translateY(0); }
.atc-form__submit:disabled { opacity: .6; cursor: default; transform: none; }

.atc-form__availability {
    margin: 0 !important; text-align: center; font-size: .75rem; font-weight: 600; color: var(--dp-outline);
}
.atc-form__availability--low { color: var(--dp-error, #b3261e); }
</style>
