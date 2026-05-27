/**
 * Subscription abstraction.
 *
 * In production this should wrap RevenueCat (react-native-purchases), which
 * handles App Store / Play Store in-app purchases, receipt validation, and
 * entitlement state. Apple requires that digital subscriptions use StoreKit
 * IAP — do NOT process these through an external card form.
 *
 * This module is a typed stub with in-memory state so the UI is fully
 * navigable before billing keys are wired up.
 */

export type EntitlementStatus = 'free' | 'subscribed';

export interface SubscriptionPlan {
  id: string;
  title: string;
  priceLabel: string;
  period: 'month' | 'year';
  features: string[];
}

export const PLANS: SubscriptionPlan[] = [
  {
    id: 'pro_monthly',
    title: 'Pro Monthly',
    priceLabel: '$29.99 / mo',
    period: 'month',
    features: [
      'Run background-check reports',
      'Full national court-record coverage (via provider)',
      'Saved searches & report history',
    ],
  },
  {
    id: 'pro_yearly',
    title: 'Pro Yearly',
    priceLabel: '$299.99 / yr',
    period: 'year',
    features: ['Everything in Pro Monthly', '2 months free'],
  },
];

let currentStatus: EntitlementStatus = 'free';
const listeners = new Set<(s: EntitlementStatus) => void>();

export function getEntitlement(): EntitlementStatus {
  return currentStatus;
}

export function onEntitlementChange(fn: (s: EntitlementStatus) => void): () => void {
  listeners.add(fn);
  return () => listeners.delete(fn);
}

/**
 * Stub purchase. Replace with:
 *   const { customerInfo } = await Purchases.purchasePackage(pkg);
 *   currentStatus = customerInfo.entitlements.active['pro'] ? 'subscribed' : 'free';
 */
export async function purchase(planId: string): Promise<EntitlementStatus> {
  await new Promise((r) => setTimeout(r, 600));
  currentStatus = 'subscribed';
  listeners.forEach((fn) => fn(currentStatus));
  return currentStatus;
}

export async function restorePurchases(): Promise<EntitlementStatus> {
  await new Promise((r) => setTimeout(r, 400));
  listeners.forEach((fn) => fn(currentStatus));
  return currentStatus;
}
