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
            lastName: raw.last_name ?? "",
            email: raw.email ?? "",
            marketing: raw.marketing ?? false,
            twoFactorEnabled: raw.two_factor_enabled ?? false,
            adminCode: (raw.admin_code ?? "") !== "" ? raw.admin_code : null,
            accountType: raw.account_type ?? null,
            canAddClientAssociation: raw.can_add_client_association ?? false,
            clientLimit: raw.client_limit ?? null,
            clientCountPersonal: raw.client_count_personal ?? 0,
            canCreateTeam: raw.can_create_team ?? false,
            teamCount: raw.team_count ?? 0,
            teamLimit: raw.team_limit ?? null,
            hasAnyTeam: raw.has_any_team ?? false,
            activeTeamId: raw.active_team_id ?? null,
            activeTeamName: raw.active_team_name ?? null,
            pendingInvitationCount: raw.pending_invitation_count ?? 0,
            teamsList: raw.teams_list ?? [],
        };
    });

    /** True when the user has a non-empty admin_code and can access admin pages */
    const isAdmin = computed(() => {
        const code = sessionPayload()?.admin_code;
        return code != null && String(code).trim() !== "";
    });

    /** CUSTOMER ADMIN: Registered Users, Invoices, Activity Log = A or C */
    const hasCustomerAdmin = computed(() => {
        const code = String(sessionPayload()?.admin_code ?? "");
        return code.includes("A") || code.includes("C");
    });

    /** SITE ADMIN: Categories, Templates = A or S */
    const hasSiteAdmin = computed(() => {
        const code = String(sessionPayload()?.admin_code ?? "");
        return code.includes("A") || code.includes("S");
    });

    /** DEBUG TOOLS: Duda API Log = A or D */
    const hasDudaApiLogs = computed(() => {
        const code = String(sessionPayload()?.admin_code ?? "");
        return code.includes("A") || code.includes("D");
    });

    /** EPICURUS: Customers, Activity Log = A or E */
    const hasEpicurusCustomers = computed(() => {
        const code = String(sessionPayload()?.admin_code ?? "");
        return code.includes("A") || code.includes("E");
    });

    const hasEpicurusActivityLogs = computed(() => {
        const code = String(sessionPayload()?.admin_code ?? "");
        return code.includes("A") || code.includes("E");
    });

    /** EPICURUS: Manifest = A only */
    const hasEpicurusManifest = computed(() => {
        const code = String(sessionPayload()?.admin_code ?? "");
        return code.includes("A");
    });

    const hasAnyEpicurusAccess = computed(() => {
        return (
            hasEpicurusCustomers.value ||
            hasEpicurusActivityLogs.value ||
            hasEpicurusManifest.value
        );
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

    async function switchTeam(teamId) {
        try {
            await axios.post("/api/session/switchteam", { team_id: teamId ?? null });
        } catch {
            // will be reflected on next fetchSession
        }
        await fetchSession();
    }

    async function clearTeamContext() {
        await switchTeam(null);
    }

    return {
        session,
        loading,
        isLoggedIn,
        userId,
        user,
        isAdmin,
        hasCustomerAdmin,
        hasSiteAdmin,
        hasDudaApiLogs,
        hasEpicurusCustomers,
        hasEpicurusActivityLogs,
        hasEpicurusManifest,
        hasAnyEpicurusAccess,
        fetchSession,
        clearSession,
        switchTeam,
        clearTeamContext,
    };
}
