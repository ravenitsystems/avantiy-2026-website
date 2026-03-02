import { ref } from 'vue';

const toasts = ref([]);
let nextId = 1;
const DEFAULT_DURATION = 5000;

/**
 * Show a toast for action success or failure (not field validation).
 * @param {string} message - Message to display
 * @param {'success'|'error'} type - Toast type
 * @param {number} [duration] - Ms before auto-dismiss (default 5000, 0 = no auto-dismiss)
 */
export function useToast() {
    function show(message, type = 'success', duration = DEFAULT_DURATION) {
        const id = nextId++;
        const toast = { id, message, type };
        toasts.value = [...toasts.value, toast];
        if (duration > 0) {
            setTimeout(() => dismiss(id), duration);
        }
        return id;
    }

    function success(message, duration = DEFAULT_DURATION) {
        return show(message, 'success', duration);
    }

    function error(message, duration = 0) {
        return show(message, 'error', duration);
    }

    function dismiss(id) {
        toasts.value = toasts.value.filter((t) => t.id !== id);
    }

    return { toasts, show, success, error, dismiss };
}
