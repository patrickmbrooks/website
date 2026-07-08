import Constants from 'expo-constants';

/**
 * URL + shared API key of the CRA backend proxy (see `backend/`).
 *
 * Set them in one of two places:
 *   1. app.json  →  "expo": { "extra": { "backendUrl": "...", "backendApiKey": "..." } }
 *   2. Environment (EAS builds):  EXPO_PUBLIC_BACKEND_URL / EXPO_PUBLIC_BACKEND_API_KEY
 *
 * When either is missing the app falls back to a local mock so the UI is still
 * navigable in development.
 */

const extra = (Constants.expoConfig?.extra ?? {}) as Record<string, string | undefined>;

export const BACKEND_URL =
  extra.backendUrl ?? process.env.EXPO_PUBLIC_BACKEND_URL ?? '';

export const BACKEND_API_KEY =
  extra.backendApiKey ?? process.env.EXPO_PUBLIC_BACKEND_API_KEY ?? '';

export function isBackendConfigured(): boolean {
  return BACKEND_URL.length > 0 && BACKEND_API_KEY.length > 0;
}
