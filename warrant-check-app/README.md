# Warrant & Background Check (mobile app)

A standalone Expo (React Native + TypeScript) app, separate from the website.
Targets the **iOS App Store** and **Google Play** from one codebase.

## What it does

- **Free — Warrant Jurisdiction Directory:** searchable list of all 50 states +
  DC (plus a seed set of major counties), each linking to official court/warrant
  lookup resources with a web-search fallback. Honest about incomplete coverage.
- **Paid — Background Checks:** subscription paywall gating an FCRA-compliant
  background-check flow (disclosure + written-consent gate → run report).

## Try it (no store account needed)

`react-native-purchases` is **lazy-loaded** — it is only required once RevenueCat
keys are set. Until then the app runs everywhere in dev mode (mock paywall, mock
background checks, full free directory):

- **Browser:** `npx expo start` then press `w`. Or export a static build with
  `npx expo export --platform web` (output in `dist/`).
- **Your phone (closest to the real experience):** install **Expo Go** from the
  App Store / Play Store, run `npx expo start --tunnel` on your computer, and
  scan the QR code.

## Run it (full native, once RevenueCat keys are set)

Real in-app purchases need a native build — Expo Go can't load the StoreKit /
Play Billing module. Use a custom dev client (already added as a dep):

```bash
cd warrant-check-app
npm install
npx expo prebuild            # generates ios/ and android/ (first run only)
npx expo run:ios             # or run:android — builds + boots the dev client
npm run typecheck
```

Before subscriptions work you need RevenueCat keys — see step 1 below. Until they
are set the paywall runs in "DEV MODE" with placeholder plans.

## Status: subscriptions + backend wired, jurisdiction URLs to verify

1. **Subscriptions (RevenueCat)** — `src/services/subscriptions.ts` wraps
   `react-native-purchases` (Apple requires StoreKit IAP for digital subs; Play
   handles Android). To activate:
   1. Create a RevenueCat project, add your App Store / Play Store products, and
      define an entitlement with identifier **`pro`**.
   2. Copy the **public SDK keys** (one iOS, one Android) into
      `app.json → expo.extra.revenuecatApiKeyIos` / `revenuecatApiKeyAndroid`,
      OR set `EXPO_PUBLIC_REVENUECAT_API_KEY_IOS` / `_ANDROID` at build time.
   3. Rebuild the dev client (`npx expo run:ios` / `run:android`).
   Plans, prices, and periods are pulled from your current RevenueCat Offering
   — do not hardcode prices.
2. **Background-check backend** — `src/services/backgroundCheck.ts` calls the
   Fastify proxy in `backend/` (see `backend/README.md`). The proxy authenticates
   requests, verifies the `pro` entitlement, records FCRA consent, and forwards
   to a CRA driver (mock by default; Checkr driver included). To activate:
   1. Deploy the backend and set `CRA_PROVIDER=checkr` with your Checkr keys.
   2. Set `backendUrl` and `backendApiKey` in `app.json → expo.extra`, OR
      `EXPO_PUBLIC_BACKEND_URL` / `EXPO_PUBLIC_BACKEND_API_KEY` at build time.
   3. Rebuild the dev client.
   Until then the app runs a local mock so the flow stays testable.
3. **Jurisdiction URLs** — all 50 states + DC have a researched official
   resource (`src/data/curatedResources.ts`), plus ~210 counties across every
   state (`src/data/counties.ts`) and a Federal category (U.S. Marshals, FBI,
   PACER — `src/data/federal.ts`). Each resource carries a `confidence` level;
   the UI shows a **VERIFY** badge on anything below `high`. Most states have
   no public "active warrant" search — warrants appear within court dockets or
   county sheriff lists, which is reflected in each description. Re-confirm
   URLs periodically (last research pass: 2026-05); government portals move.

## ⚠️ Legal (needs attorney review)

- **FCRA:** the background-check feature produces "consumer reports." You must
  provide standalone written disclosure, obtain written authorization, and follow
  adverse-action procedures. Copy in `src/legal/disclaimers.ts` is placeholder.
- **Not legal advice / no warranty of completeness:** surfaced throughout the UI.
- **App Store review:** apps surfacing criminal/warrant data face extra scrutiny;
  be ready to document data sources and accuracy.
