import Fastify from 'fastify';
import helmet from '@fastify/helmet';
import type { Config } from './config.js';
import { openDb } from './db.js';
import { ConsentRepo } from './repositories/consentRepo.js';
import { ReportsRepo } from './repositories/reportsRepo.js';
import { makeProvider } from './providers/index.js';
import { registerHealthRoute } from './routes/health.js';
import { registerBackgroundCheckRoutes } from './routes/backgroundChecks.js';
import { registerWebhookRoutes } from './routes/webhooks.js';
import { HttpError } from './lib/errors.js';

export async function buildServer(cfg: Config) {
  const app = Fastify({ logger: { level: cfg.LOG_LEVEL } });
  await app.register(helmet, { contentSecurityPolicy: false });

  // Webhook route needs the raw body for signature verification.
  app.addContentTypeParser(
    'application/json',
    { parseAs: 'buffer' },
    (req, body: Buffer, done) => {
      if (req.url?.startsWith('/v1/webhooks/')) {
        done(null, body);
        return;
      }
      try {
        done(null, JSON.parse(body.toString('utf8') || '{}'));
      } catch (e) {
        done(e as Error, undefined);
      }
    },
  );

  const db = openDb(cfg.DATABASE_PATH);
  const consents = new ConsentRepo(db);
  const reports = new ReportsRepo(db);
  const provider = makeProvider(cfg);

  registerHealthRoute(app);
  registerBackgroundCheckRoutes(app, { cfg, consents, reports, provider });
  registerWebhookRoutes(app, { provider, reports });

  app.setErrorHandler((err, _req, reply) => {
    if (err instanceof HttpError) {
      reply.code(err.statusCode).send({ error: { code: err.code, message: err.message } });
      return;
    }
    app.log.error(err);
    reply.code(500).send({ error: { code: 'internal_error', message: 'Something went wrong.' } });
  });

  return app;
}
