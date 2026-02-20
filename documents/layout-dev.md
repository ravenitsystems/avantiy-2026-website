
## How to access the data brought back from /session/status in other components
```
Summary of what’s in place:
1. resources/js/composables/useSession.js
   Shared composable that keeps session in a single reactive ref and exposes:
   session – Raw API response (reactive).
   loading – true while the request is in progress.
   isLoggedIn – Computed: true when user_id is present and not 0.
   userId – Computed: current user id or 0.
   user – Computed: { id, firstName, email } or null when not logged in.
   fetchSession() – Calls /api/session/status and updates session.
   clearSession() – Sets session to null (e.g. after logout).
   All of this is backed by module-level refs, so every useSession() call sees the same state.
2. Router
   The router guard now uses the composable: it calls fetchSession() and then uses userId for redirects. The same session data is then available to any component that uses useSession().
3. Using it in components
   Any component can do:
   import { useSession } from "../composables/useSession";const { session, user, userId, isLoggedIn, loading, fetchSession } = useSession();
   Example:
<script setup>import { useSession } from "../composables/useSession";const { user, isLoggedIn } = useSession();</script><template>  <div v-if="isLoggedIn">Hello, {{ user?.firstName ?? user?.email }}</div></template>
Session is loaded on the first navigation (in the router guard), and any component that uses useSession() will react to the same session / user / userId / isLoggedIn without calling the API again. Call fetchSession() again only when you need to refresh (e.g. after login or logout).
```
