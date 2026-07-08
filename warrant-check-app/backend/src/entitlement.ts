import type { Config } from './config.js';
import { forbidden } from './lib/errors.js';

/**
 * Verify that a caller has the paid 'pro' entitlement before spending money on
 * a CRA report. Server-side verification via the RevenueCat REST v1 API:
 *
 *   GET https://api.revenuecat.com/v1/subscribers/:app_user_id
 *   Authorization: Bearer <REVENUECAT_REST_API_KEY>
 *
 * When REVENUECAT_REST_API_KEY isn't set, this stub is permissive — DO NOT ship
 * to production without configuring it. The `app_user_id` we look up here is
 * whatever the mobile client passed in as X-Requester-Id, so ensure the app
 * sets that to the same id it uses in Purchases.logIn().
 */
export async function requireProEntitlement(cfg: Config, requesterId: string): Promise<void> {
  if (!cfg.REVENUECAT_REST_API_KEY) {
    // Dev-mode: no verification. Log so this doesn't silently ship.
    // eslint-disable-next-line no-console
    console.warn('[entitlement] REVENUECAT_REST_API_KEY not set — skipping verification.');
    return;
  }
  const url = `https://api.revenuecat.com/v1/subscribers/${encodeURIComponent(requesterId)}`;
  const res = await fetch(url, {
    headers: { Authorization: `Bearer ${cfg.REVENUECAT_REST_API_KEY}` },
  });
  if (!res.ok) throw forbidden('Could not verify subscription.');
  const body = (await res.json()) as {
    subscriber?: { entitlements?: Record<string, { expires_date: string | null }> };
  };
  const pro = body.subscriber?.entitlements?.['pro'];
  const active =
    pro && (pro.expires_date === null || new Date(pro.expires_date).getTime() > Date.now());
  if (!active) throw forbidden("Active 'pro' subscription required.");
}
