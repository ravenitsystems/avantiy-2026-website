<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { isValidPhoneNumber } from 'libphonenumber-js/max';

const props = defineProps({
    modelValue: { type: String, default: '' },
    countries: { type: Array, default: () => [] },
    syncCountryId: { type: [Number, String], default: null },
    placeholder: { type: String, default: 'Phone number' },
    invalid: { type: Boolean, default: false },
    id: { type: String, default: '' },
    label: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const triggerRef = ref(null);
const listRef = ref(null);
const nationalNumber = ref('');

// Match dial code from E.164: +44 -> 44, +1 -> 1. Longest match from countries first.
function parseE164(value) {
    if (!value || typeof value !== 'string') return { countryId: null, national: '' };
    const digits = value.replace(/\D/g, '');
    if (!digits.length) return { countryId: null, national: '' };
    const withPlus = '+' + digits;
    let best = { countryId: null, national: value.replace(/^\+/, '') };
    for (const c of props.countries) {
        const code = (c.dial_code || '').replace(/\D/g, '');
        if (!code || !withPlus.startsWith('+' + code)) continue;
        if (code.length > (best.dialCodeLength || 0)) {
            best = {
                countryId: c.id,
                national: digits.slice(code.length).replace(/\D/g, ''),
                dialCodeLength: code.length,
            };
        }
    }
    if (best.countryId != null) return { countryId: best.countryId, national: best.national };
    return { countryId: null, national: digits };
}

const selectedCountryId = ref(null);
const selectedCountry = computed(() => {
    if (selectedCountryId.value == null) return null;
    return props.countries.find((c) => Number(c.id) === Number(selectedCountryId.value)) ?? null;
});

function initFromModel() {
    const { countryId, national } = parseE164(props.modelValue);
    selectedCountryId.value = countryId;
    nationalNumber.value = national;
    if (selectedCountryId.value == null && props.countries.length > 0) {
        const us = props.countries.find((c) => c.alpha_2 === 'US') || props.countries[0];
        if (us) selectedCountryId.value = us.id;
    }
}

function hasFlagSvg(c) {
    return c && typeof c.flag_svg === 'string' && c.flag_svg.trim() !== '';
}

function buildE164() {
    const c = selectedCountry.value;
    const national = (nationalNumber.value || '').replace(/\D/g, '');
    if (!national) return '';
    if (!c || !c.dial_code) return '+' + national;
    const dialDigits = (c.dial_code || '').replace(/\D/g, '');
    return '+' + dialDigits + national;
}

function selectCountry(c) {
    selectedCountryId.value = c != null ? c.id : null;
    open.value = false;
    emit('update:modelValue', buildE164());
}

function onNationalInput(e) {
    const v = (e.target && e.target.value) || '';
    nationalNumber.value = v.replace(/\D/g, '');
    emit('update:modelValue', buildE164());
}

const isValidPhone = computed(() => {
    const full = buildE164();
    if (!full || full.length < 10) return false;
    if (!/^\+[0-9]{10,15}$/.test(full)) return false;
    try {
        return isValidPhoneNumber(full);
    } catch {
        return false;
    }
});

const showInvalid = computed(() => props.invalid || (buildE164() && !isValidPhone.value));

function onTriggerKeydown(e) {
    if (props.disabled) return;
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        open.value = !open.value;
        if (open.value) setTimeout(() => listRef.value?.focus(), 0);
    }
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!open.value) open.value = true;
        setTimeout(() => listRef.value?.focus(), 0);
    }
}

function onListKeydown(e) {
    if (e.key === 'Escape') {
        open.value = false;
        triggerRef.value?.focus();
    }
}

function onClickOutside(event) {
    if (open.value && triggerRef.value && listRef.value && !triggerRef.value.contains(event.target) && !listRef.value.contains(event.target)) {
        open.value = false;
    }
}

onMounted(() => {
    initFromModel();
    document.addEventListener('click', onClickOutside);
});
onUnmounted(() => {
    document.removeEventListener('click', onClickOutside);
});
watch(() => props.modelValue, () => initFromModel(), { immediate: false });
watch(() => props.countries, () => {
    if (props.countries.length > 0 && selectedCountryId.value == null && !nationalNumber.value) {
        const us = props.countries.find((c) => c.alpha_2 === 'US') || props.countries[0];
        if (us) selectedCountryId.value = us.id;
    } else {
        initFromModel();
    }
}, { immediate: false });
watch(() => props.syncCountryId, (newId) => {
    if (newId == null || !props.countries.length) return;
    const id = Number(newId);
    const found = props.countries.some((c) => Number(c.id) === id);
    if (found && Number(selectedCountryId.value) !== id) {
        selectedCountryId.value = id;
        emit('update:modelValue', buildE164());
    }
}, { immediate: false });
</script>

<template>
    <div class="block">
        <label v-if="label" :for="id" class="block text-sm font-medium text-site-body">{{ label }}</label>
        <div class="mt-1 flex rounded-lg border bg-slate-800 focus-within:ring-1" :class="showInvalid ? 'border-red-500 focus-within:border-red-500 focus-within:ring-red-500' : 'border-slate-600 focus-within:border-cta focus-within:ring-cta'">
            <div class="relative shrink-0">
                <button
                    :id="id"
                    ref="triggerRef"
                    type="button"
                    :disabled="disabled"
                    :aria-haspopup="'listbox'"
                    :aria-expanded="open"
                    class="flex h-full min-w-[4rem] items-center gap-1.5 rounded-l-lg border-r border-slate-600 px-2 py-2 text-left focus:outline-none focus:ring-0"
                    @keydown="onTriggerKeydown"
                    @click.prevent="open = !open"
                >
                    <span
                        v-if="selectedCountry && hasFlagSvg(selectedCountry)"
                        class="country-flag-svg h-5 w-5 shrink-0 rounded overflow-hidden flex"
                        v-html="selectedCountry.flag_svg"
                    />
                    <span v-else class="h-5 w-5 shrink-0 rounded bg-slate-600" />
                    <span class="truncate text-sm text-site-heading">{{ selectedCountry?.dial_code ?? '+?' }}</span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div
                    v-show="open"
                    ref="listRef"
                    role="listbox"
                    tabindex="-1"
                    class="absolute left-0 top-full z-50 mt-1 max-h-60 w-64 overflow-auto rounded-lg border border-slate-600 bg-slate-800 py-1 shadow-lg"
                    @keydown="onListKeydown"
                >
                    <button
                        v-for="c in countries"
                        :key="c.id"
                        type="button"
                        role="option"
                        :aria-selected="Number(c.id) === Number(selectedCountryId)"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-site-heading hover:bg-slate-700 focus:bg-slate-700 focus:outline-none"
                        @click="selectCountry(c)"
                    >
                        <span
                            v-if="hasFlagSvg(c)"
                            class="country-flag-svg h-5 w-5 shrink-0 rounded overflow-hidden flex"
                            v-html="c.flag_svg"
                        />
                        <span v-else class="h-5 w-5 shrink-0 rounded bg-slate-600" />
                        <span class="truncate text-sm">{{ c.dial_code }} {{ c.name }}</span>
                    </button>
                </div>
            </div>
            <input
                type="tel"
                :value="nationalNumber"
                :placeholder="placeholder"
                :disabled="disabled"
                autocomplete="tel-national"
                class="min-w-0 flex-1 rounded-r-lg border-0 bg-transparent px-3 py-2 text-site-heading placeholder-slate-500 focus:ring-0"
                @input="onNationalInput"
            />
        </div>
        <p v-if="showInvalid && buildE164()" class="mt-1 text-xs text-red-400">Please enter a valid phone number.</p>
    </div>
</template>

<style scoped>
.country-flag-svg :deep(svg) {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}
</style>
