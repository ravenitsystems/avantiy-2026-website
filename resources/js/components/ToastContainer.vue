<script setup>
import { computed } from 'vue';
import { useToast } from '../composables/useToast';

const { toasts, dismiss } = useToast();

const errorToasts = computed(() => toasts.value.filter((t) => t.type === 'error'));
const successToasts = computed(() => toasts.value.filter((t) => t.type !== 'error'));
</script>

<template>
    <!-- Prominent error toasts: centered overlay -->
    <TransitionGroup
        v-if="errorToasts.length > 0"
        name="toast-modal"
        tag="div"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        aria-live="assertive"
        aria-label="Error notifications"
    >
        <div
            v-for="t in errorToasts"
            :key="t.id"
            class="fixed inset-0 flex items-center justify-center bg-black/80 p-4"
            role="alertdialog"
            :aria-label="t.message"
        >
            <div
                class="flex max-w-lg items-start gap-4 rounded-xl border-2 border-red-600 bg-slate-900 p-6 shadow-2xl"
            >
                <span class="flex shrink-0">
                    <svg class="h-8 w-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="text-lg font-semibold text-red-200">Error</h3>
                    <p class="mt-2 text-base text-slate-200">{{ t.message }}</p>
                    <button
                        type="button"
                        class="mt-4 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500"
                        @click="dismiss(t.id)"
                    >
                        Dismiss
                    </button>
                </div>
                <button
                    type="button"
                    class="shrink-0 rounded p-1 text-slate-400 hover:bg-slate-700 hover:text-slate-200 focus:outline-none focus:ring-2 focus:ring-white/50"
                    aria-label="Dismiss"
                    @click="dismiss(t.id)"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </TransitionGroup>

    <!-- Success toasts: corner -->
    <div
        class="pointer-events-none fixed bottom-0 right-0 z-[90] flex max-h-[100dvh] w-full max-w-sm flex-col gap-2 p-4 sm:bottom-4 sm:right-4 sm:w-auto sm:p-0"
        aria-live="polite"
        aria-label="Notifications"
    >
        <TransitionGroup
            name="toast"
            tag="div"
            class="flex flex-col gap-2"
        >
            <div
                v-for="t in successToasts"
                :key="t.id"
                class="pointer-events-auto flex items-start gap-3 rounded-lg border border-emerald-800/80 bg-emerald-950/95 px-4 py-3 text-emerald-100 shadow-lg"
            >
                <span class="flex shrink-0 items-center pt-0.5">
                    <svg class="h-5 w-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <p class="min-w-0 flex-1 text-sm">{{ t.message }}</p>
                <button
                    type="button"
                    class="shrink-0 rounded p-1 opacity-70 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-white/50"
                    aria-label="Dismiss"
                    @click="dismiss(t.id)"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.25s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
.toast-move {
    transition: transform 0.25s ease;
}

.toast-modal-enter-active,
.toast-modal-leave-active {
    transition: opacity 0.25s ease;
}
.toast-modal-enter-from,
.toast-modal-leave-to {
    opacity: 0;
}
</style>
