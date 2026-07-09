import type {
  CustomerInfo,
  PurchasesOffering,
  PurchasesPackage,
} from 'react-native-purchases';

import {
  REVENUECAT_ENTITLEMENT_ID,
  REVENUECAT_OFFERING_ID,
  getPlatformKey,
  isConfigured,
} from '../config/revenuecat';

type PurchasesModule = typeof import('react-native-purchases').default;

let purchasesModule: PurchasesModule | null = null;

/**
 * Lazy-load react-native-purchases only when API keys are configured. The
 * native module doesn't exist in Expo Go or web builds, so importing it
 * eagerly would crash those environments — and dev mode never needs it.
 */
function getPurchases(): PurchasesModule | null {
  if (!isConfigured()) return null;
  if (!purchasesModule) {
    // eslint-disable-next-line @typescript-eslint/no-var-requires
    purchasesModule = (require('react-native-purchases') as { default: PurchasesModule }).default;
  }
  return purchasesModule;
}

export type EntitlementStatus = 'free' | 'subscribed';

export interface SubscriptionPlan {
  id: string;
  title: string;
  priceLabel: string;
  period: 'month' | 'year' | 'other';
  features: string[];
  /** RevenueCat package to purchase. Null when the SDK isn't configured yet. */
  pkg: PurchasesPackage | null;
}

/**
 * Static fallback shown when RevenueCat isn't configured, so the paywall stays
 * navigable in development. Real prices come from RevenueCat Offerings, which
 * mirror the App Store / Play Store products (the stores are the source of
 * truth for pricing).
 */
const FALLBACK_PLANS: SubscriptionPlan[] = [
  {
    id: 'pro_monthly',
    title: 'Pro Monthly',
    priceLabel: 'Set in App Store',
    period: 'month',
    features: [
      'Run background-check reports',
      'Full national court-record coverage (via provider)',
      'Saved searches & report history',
    ],
    pkg: null,
  },
  {
    id: 'pro_yearly',
    title: 'Pro Yearly',
    priceLabel: 'Set in App Store',
    period: 'year',
    features: ['Everything in Pro Monthly', '2 months free'],
    pkg: null,
  },
];

let currentStatus: EntitlementStatus = 'free';
let configured = false;
const listeners = new Set<(s: EntitlementStatus) => void>();

function customerToStatus(info: CustomerInfo | null | undefined): EntitlementStatus {
  return info?.entitlements?.active?.[REVENUECAT_ENTITLEMENT_ID] ? 'subscribed' : 'free';
}

function updateStatus(info: CustomerInfo | null | undefined): void {
  const next = customerToStatus(info);
  if (next !== currentStatus) {
    currentStatus = next;
    listeners.forEach((fn) => fn(currentStatus));
  }
}

/** Call once at app start. Safe to call before keys are configured. */
export async function configureSubscriptions(): Promise<void> {
  const Purchases = getPurchases();
  if (configured || !Purchases) return;
  Purchases.configure({ apiKey: getPlatformKey() });
  Purchases.addCustomerInfoUpdateListener(updateStatus);
  try {
    const info = await Purchases.getCustomerInfo();
    updateStatus(info);
  } catch {
    // Boot fetch failed; keep the default 'free' state until the SDK recovers.
  }
  configured = true;
}

export function isSubscriptionsConfigured(): boolean {
  return isConfigured();
}

export function getEntitlement(): EntitlementStatus {
  return currentStatus;
}

export function onEntitlementChange(fn: (s: EntitlementStatus) => void): () => void {
  listeners.add(fn);
  return () => {
    listeners.delete(fn);
  };
}

function packageToPlan(pkg: PurchasesPackage): SubscriptionPlan {
  const description = pkg.product.description?.trim();
  return {
    id: pkg.identifier,
    title: pkg.product.title || pkg.identifier,
    priceLabel: pkg.product.priceString,
    period:
      pkg.packageType === 'MONTHLY'
        ? 'month'
        : pkg.packageType === 'ANNUAL'
        ? 'year'
        : 'other',
    features: description ? [description] : [],
    pkg,
  };
}

export async function getPlans(): Promise<SubscriptionPlan[]> {
  const Purchases = getPurchases();
  if (!Purchases) return FALLBACK_PLANS;
  try {
    const offerings = await Purchases.getOfferings();
    const offering: PurchasesOffering | null =
      (REVENUECAT_OFFERING_ID && offerings.all[REVENUECAT_OFFERING_ID]) ||
      offerings.current;
    if (!offering) return FALLBACK_PLANS;
    return offering.availablePackages.map(packageToPlan);
  } catch {
    return FALLBACK_PLANS;
  }
}

export async function purchase(plan: SubscriptionPlan): Promise<EntitlementStatus> {
  const Purchases = getPurchases();
  if (!plan.pkg || !Purchases) {
    throw new Error(
      'Subscriptions are not configured yet. Add your RevenueCat API key in app.json to enable purchases.',
    );
  }
  try {
    const { customerInfo } = await Purchases.purchasePackage(plan.pkg);
    updateStatus(customerInfo);
    return currentStatus;
  } catch (e) {
    // RevenueCat surfaces user-cancelled as an error with userCancelled:true —
    // treat that as a no-op rather than surfacing a scary error.
    if ((e as { userCancelled?: boolean }).userCancelled) {
      return currentStatus;
    }
    throw e;
  }
}

/** Stable id for this app user, used as the requesterId in backend calls. */
export async function getRequesterId(): Promise<string> {
  const Purchases = getPurchases();
  if (!Purchases) return 'anonymous';
  try {
    return await Purchases.getAppUserID();
  } catch {
    return 'anonymous';
  }
}

export async function restorePurchases(): Promise<EntitlementStatus> {
  const Purchases = getPurchases();
  if (!Purchases) return currentStatus;
  try {
    const info = await Purchases.restorePurchases();
    updateStatus(info);
  } catch {
    // Ignore; caller shows current state.
  }
  return currentStatus;
}
