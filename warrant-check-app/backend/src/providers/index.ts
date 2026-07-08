import type { Config } from '../config.js';
import type { CraProvider } from './types.js';
import { CheckrProvider } from './checkr.js';
import { MockCraProvider } from './mock.js';

export function makeProvider(cfg: Config): CraProvider {
  switch (cfg.CRA_PROVIDER) {
    case 'checkr':
      return new CheckrProvider(cfg.CHECKR_API_KEY, cfg.CHECKR_WEBHOOK_SECRET, cfg.CHECKR_BASE_URL);
    case 'mock':
    default:
      return new MockCraProvider();
  }
}

export type { CraProvider } from './types.js';
