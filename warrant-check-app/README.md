# Warrant & Background Check (mobile app)

A standalone Expo (React Native + TypeScript) app, separate from the website.
Targets the **iOS App Store** and **Google Play** from one codebase.

## What it does

- **Free — Warrant Jurisdiction Directory:** searchable list of all 50 states +
  DC (plus a seed set of major counties), each linking to official court/warrant
  lookup resources with a web-search fallback. Honest about incomplete coverage.
- **Paid — Background Checks:** subscription paywall gating an FCRA-compliant
  background-check flow (disclosure + written-consent gate → run report).

## Run it

```bash
cd warrant-check-app
npm install
npm start        # then press i (iOS sim), a (Android), or w (web)
npm run typecheck
```

## Status: foundation / scaffold

This is a working, navigable foundation. Three things are **stubbed** and must be
wired up before launch:

1. **Subscriptions** — `src/services/subscriptions.ts` is an in-memory stub.
   Replace with [RevenueCat](https://www.revenuecat.com/) (`react-native-purchases`).
   Apple requires StoreKit IAP for digital subscriptions.
2. **Background-check provider** — `src/services/backgroundCheck.ts` returns mock
   data. Wire it to a **secure backend** that calls an FCRA-compliant Consumer
   Reporting Agency (Checkr, Sterling, Accurate). Never put provider keys in the
   client.
3. **Jurisdiction URLs** — every entry in `src/data/` is marked `verified: false`.
   The seeded URLs are official state-judiciary domains, but warrant lookups are
   usually county/sheriff-level. **Verify each URL and coverage before launch.**

## ⚠️ Legal (needs attorney review)

- **FCRA:** the background-check feature produces "consumer reports." You must
  provide standalone written disclosure, obtain written authorization, and follow
  adverse-action procedures. Copy in `src/legal/disclaimers.ts` is placeholder.
- **Not legal advice / no warranty of completeness:** surfaced throughout the UI.
- **App Store review:** apps surfacing criminal/warrant data face extra scrutiny;
  be ready to document data sources and accuracy.
