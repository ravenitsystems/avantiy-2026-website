import { ref, computed } from "vue";
import axios from "axios";

const session = ref(null);
const loading = ref(false);

/** Payload from /api/session/status: { status, data: { user_id?, first_name?, email? } } */
function sessionPayload() {
    const res = session.value;
    if (!res) return null;
    return res.data !== undefined ? res.data : res;
}

export function useSession() {
    const isLoggedIn = computed(() => {
        const id = sessionPayload()?.user_id ?? 0;
        return id != null && id !== 0;
    });

    const userId = computed(() => {
        return sessionPayload()?.user_id ?? 0;
    });

    const user = computed(() => {
        const raw = sessionPayload();
        if (!raw || (raw.user_id ?? 0) === 0) return null;
        return {
            id: raw.user_id ?? 0,
            firstName: raw.first_name ?? "",
            email: raw.email ?? "",
        };
    });

    async function fetchSession() {
        loading.value = true;
        try {
            const { data } = await axios.get("/api/session/status", {
                params: { _t: Date.now() },
                headers: { "Cache-Control": "no-cache", Pragma: "no-cache" },
            });
            session.value = data;
            return data;
        } catch {
            session.value = { status: "FAIL", data: { user_id: 0 } };
            return session.value;
        } finally {
            loading.value = false;
        }
    }

    function clearSession() {
        session.value = null;
    }

    return {
        session,
        loading,
        isLoggedIn,
        userId,
        user,
        fetchSession,
        clearSession,
    };
}
