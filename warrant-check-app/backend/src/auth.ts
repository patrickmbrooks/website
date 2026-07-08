import type { FastifyRequest } from 'fastify';
import { unauthorized } from './lib/errors.js';
import type { Config } from './config.js';

/**
 * Simple shared-secret auth. The mobile app sends `X-Api-Key` on every request.
 * The `X-Requester-Id` header identifies the calling app user for audit rows
 * (opaque — your app's user id, RevenueCat app-user-id, or similar).
 *
 * Rotate API_KEY periodically. For higher security, replace with per-user
 * short-lived tokens signed by your login backend.
 */
export interface Caller {
  requesterId: string;
}

export function requireAuth(cfg: Config, req: FastifyRequest): Caller {
  const key = String(req.headers['x-api-key'] ?? '');
  if (!key || key !== cfg.API_KEY) throw unauthorized('Invalid or missing X-Api-Key.');
  const requesterId = String(req.headers['x-requester-id'] ?? '').trim();
  if (!requesterId) throw unauthorized('Missing X-Requester-Id header.');
  return { requesterId };
}
