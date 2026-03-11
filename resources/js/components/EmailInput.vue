<script setup>
import { computed } from 'vue';
import { useAppConfig } from '../composables/useAppConfig';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'you@example.com' },
    invalid: { type: Boolean, default: false },
    id: { type: String, default: '' },
    label: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    autocomplete: { type: String, default: 'email' },
    required: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const { appEnv } = useAppConfig();

const isPlusInvalid = computed(() => {
    return appEnv === 'production' && props.modelValue && props.modelValue.includes('+');
});

const showInvalid = computed(() => props.invalid || isPlusInvalid.value);

const inputClass = computed(() => {
    const base = 'mt-1 block w-full rounded-lg border bg-slate-800 px-3 py-2 text-site-heading placeholder-slate-500 focus:outline-none focus:ring-1';
    if (showInvalid.value) return base + ' border-red-500 focus:border-red-500 focus:ring-red-500';
    return base + ' border-slate-600 focus:border-cta focus:ring-cta';
});
</script>

<template>
    <div class="block">
        <label v-if="label" :for="id" class="block text-sm font-medium text-site-body">{{ label }}</label>
        <input
            :id="id"
            :value="modelValue"
            type="email"
            :autocomplete="autocomplete"
            :required="required"
            :disabled="disabled"
            :placeholder="placeholder"
            :class="inputClass"
            @input="emit('update:modelValue', ($event.target && $event.target.value) || '')"
        />
        <p v-if="isPlusInvalid" class="mt-1 text-xs text-red-400">
            Email addresses containing "+" are not allowed in production.
        </p>
    </div>
</template>
