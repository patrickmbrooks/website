import Constants from 'expo-constants';
import { Platform } from 'react-native';

/**
 * RevenueCat public SDK keys. These are safe to ship in the client bundle —
 * they are DIFFERENT from your RevenueCat SECRET keys (never embed those).
 *
 * Set them in one of two places:
 *   1. app.json  →  "expo": { "extra": { "revenuecatApiKeyIos": "...", "revenuecatApiKeyAndroid": "..." } }
 *   2. Environment (EAS builds):  EXPO_PUBLIC_REVENUECAT_API_KEY_IOS / _ANDROID
 *
 * When no key is configured for the current platform, the subscription service
 * silently degrades to a "not configured" state so the paywall UI is still
 * navigable in development.
 */

const extra = (Constants.expoConfig?.extra ?? {}) as Record<string, string | undefined>;

export const REVENUECAT_API_KEY_IOS =
  extra.revenuecatApiKeyIos ?? process.env.EXPO_PUBLIC_REVENUECAT_API_KEY_IOS ?? '';

export const REVENUECAT_API_KEY_ANDROID =
  extra.revenuecatApiKeyAndroid ?? process.env.EXPO_PUBLIC_REVENUECAT_API_KEY_ANDROID ?? '';

/** RevenueCat entitlement identifier gating paid features. */
export const REVENUECAT_ENTITLEMENT_ID = 'pro';

/** RevenueCat Offering id to display in the paywall. `null` = current offering. */
export const REVENUECAT_OFFERING_ID: string | null = null;

export function getPlatformKey(): string {
  return Platform.OS === 'ios' ? REVENUECAT_API_KEY_IOS : REVENUECAT_API_KEY_ANDROID;
}

export function isConfigured(): boolean {
  return getPlatformKey().length > 0;
}
