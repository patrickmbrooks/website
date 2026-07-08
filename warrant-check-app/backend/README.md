# Warrant Check backend — CRA proxy

Fastify + TypeScript + SQLite backend that sits between the mobile app and a
consumer reporting agency (CRA). The mobile app never touches CRA API keys
directly; every background-check request is authenticated, entitlement-checked,
audited, and forwarded through here.

## What it does

- **Auth:** shared secret via `X-Api-Key` + `X-Requester-Id` (RevenueCat app
  user id).
- **Entitlement:** verifies the caller's `pro` entitlement server-side against
  the RevenueCat REST API (permissive in dev when the key isn't set).
- **FCRA audit trail:** every report is preceded by a consent row in SQLite
  containing the disclosure text, subject, purpose, IP, and signed timestamp.
- **CRA provider abstraction:** swap Checkr / Sterling / Accurate / anything
  else by implementing one interface (`src/providers/types.ts`).
- **Webhook receiver:** verifies provider HMAC signatures against the raw body
  and updates reports as they complete; a polling fallback catches missed events.

## Run it

```bash
cd warrant-check-app/backend
cp .env.example .env    # edit values
npm install
npm run dev             # tsx watch
# or:
npm run build && npm start
```

Health check:

```bash
curl http://localhost:8080/v1/health
```

End-to-end mock flow (CRA_PROVIDER=mock, no real network calls):

```bash
curl -X POST http://localhost:8080/v1/background-checks \
  -H 'Content-Type: application/json' \
  -H 'X-Api-Key: <API_KEY>' \
  -H 'X-Requester-Id: user_123' \
  -d '{
    "subject": { "firstName": "Jane", "lastName": "Doe" },
    "purpose": "employment",
    "consent": {
      "signedAt": "2026-07-08T12:00:00Z",
      "disclosureText": "FCRA disclosure text shown to Jane."
    }
  }'
```

Poll for the result:

```bash
curl http://localhost:8080/v1/background-checks/<reportId> \
  -H 'X-Api-Key: <API_KEY>' \
  -H 'X-Requester-Id: user_123'
```

The mock provider returns `pending` immediately and flips to `clear` after ~5s.

## API surface

| Method | Path                              | Purpose                                        |
| ------ | --------------------------------- | ---------------------------------------------- |
| GET    | `/v1/health`                      | Liveness check.                                |
| POST   | `/v1/background-checks`           | Create a report + consent row. Returns 202.    |
| GET    | `/v1/background-checks/:id`       | Fetch a report; polls provider if pending.     |
| POST   | `/v1/webhooks/:providerName`      | Provider webhook (HMAC-signed body).           |

## Switching to Checkr

1. Register with Checkr, complete their credentialing / data-use paperwork.
2. Set `CRA_PROVIDER=checkr`, `CHECKR_API_KEY=<test key>`, and
   `CHECKR_WEBHOOK_SECRET=<value>` in `.env`.
3. Configure a Checkr webhook pointing at
   `POST https://<your-host>/v1/webhooks/checkr`.
4. Adjust the "package" mapping in `src/providers/checkr.ts` to whichever
   screening packages you actually purchase from Checkr (e.g. `tasker_standard`,
   `driver_pro`). Flatten the report's screening arrays into `CraRecord[]` in
   `getReport()` and `parseWebhookEvent()` — kept minimal in this skeleton
   because it depends on your package selection.

## Deployment notes

- SQLite is fine for MVP but pin the file to durable storage (a mounted volume
  on Fly.io / Railway / Render, or move to Postgres behind the same repository
  interfaces).
- Always terminate TLS in front of this service — it does not run its own
  HTTPS.
- Rotate `API_KEY` on a schedule; treat it like a database password.
- Keep the SQLite audit trail for at least the FCRA retention window.
