<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: { type: [Number, String], default: null },
    countries: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Select country' },
    disabled: { type: Boolean, default: false },
    invalid: { type: Boolean, default: false },
    id: { type: String, default: '' },
    label: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const triggerRef = ref(null);
const listRef = ref(null);

const selectedCountry = computed(() => {
    if (props.modelValue == null) return null;
    return props.countries.find((c) => Number(c.id) === Number(props.modelValue)) ?? null;
});

const triggerLabel = computed(() => {
    if (selectedCountry.value) return selectedCountry.value.name;
    return props.placeholder;
});

function hasFlagSvg(c) {
    return c && typeof c.flag_svg === 'string' && c.flag_svg.trim() !== '';
}

function select(c) {
    emit('update:modelValue', c != null ? c.id : null);
    open.value = false;
}

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
    document.addEventListener('click', onClickOutside);
});
onUnmounted(() => {
    document.removeEventListener('click', onClickOutside);
});

watch(open, (isOpen) => {
    if (!isOpen) listRef.value?.blur();
});
</script>

<template>
    <div class="block">
        <label v-if="label" :for="id" class="block text-sm font-medium text-site-body">{{ label }}</label>
        <div class="mt-1 relative">
            <button
                :id="id"
                ref="triggerRef"
                type="button"
                :disabled="disabled"
                :aria-haspopup="'listbox'"
                :aria-expanded="open"
                :aria-label="triggerLabel"
                class="flex w-full items-center gap-2 rounded-lg border bg-slate-800 pl-3 pr-8 py-2 text-left text-site-heading focus:outline-none focus:ring-1"
                :class="invalid ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-600 focus:border-cta focus:ring-cta'"
                @keydown="onTriggerKeydown"
                @click.prevent="open = !open"
            >
                <span
                    v-if="hasFlagSvg(selectedCountry)"
                    class="country-flag-svg h-5 w-5 shrink-0 rounded overflow-hidden flex"
                    v-html="selectedCountry.flag_svg"
                />
                <span v-else class="h-5 w-5 shrink-0 rounded bg-slate-600" />
                <span class="min-w-0 flex-1 truncate">{{ triggerLabel }}</span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-slate-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </button>

            <div
                v-show="open"
                ref="listRef"
                role="listbox"
                tabindex="-1"
                :aria-label="'Country list'"
                class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-slate-600 bg-slate-800 py-1 shadow-lg focus:outline-none"
                @keydown="onListKeydown"
            >
                <button
                    v-for="c in countries"
                    :key="c.id"
                    type="button"
                    role="option"
                    :aria-selected="Number(c.id) === Number(modelValue)"
                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-site-heading hover:bg-slate-700 focus:bg-slate-700 focus:outline-none"
                    @click="select(c)"
                >
                    <span
                        v-if="hasFlagSvg(c)"
                        class="country-flag-svg h-5 w-5 shrink-0 rounded overflow-hidden flex"
                        v-html="c.flag_svg"
                    />
                    <span v-else class="h-5 w-5 shrink-0 rounded bg-slate-600" />
                    <span class="min-w-0 flex-1 truncate">{{ c.name }}</span>
                </button>
                <p v-if="!countries.length" class="px-3 py-2 text-sm text-slate-400">Loading countries…</p>
            </div>
        </div>
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
