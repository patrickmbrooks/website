import type { FastifyInstance } from 'fastify';

export function registerHealthRoute(app: FastifyInstance): void {
  app.get('/v1/health', async () => ({ ok: true, ts: new Date().toISOString() }));
}
