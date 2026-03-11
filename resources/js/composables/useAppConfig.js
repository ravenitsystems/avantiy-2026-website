/**
 * App config exposed from the server (e.g. via window.__APP_ENV in dashboard-app.blade.php).
 */
export function useAppConfig() {
    return {
        appEnv: typeof window !== 'undefined' ? window.__APP_ENV ?? 'production' : 'production',
    };
}
