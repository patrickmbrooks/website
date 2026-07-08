import { z } from 'zod';

const EnvSchema = z.object({
  PORT: z.coerce.number().int().positive().default(8080),
  NODE_ENV: z.enum(['development', 'test', 'production']).default('development'),
  LOG_LEVEL: z.enum(['fatal', 'error', 'warn', 'info', 'debug', 'trace']).default('info'),
  API_KEY: z.string().min(8, 'API_KEY must be at least 8 chars'),
  DATABASE_PATH: z.string().default('./data/app.db'),
  CRA_PROVIDER: z.enum(['mock', 'checkr']).default('mock'),
  CHECKR_API_KEY: z.string().optional().default(''),
  CHECKR_WEBHOOK_SECRET: z.string().optional().default(''),
  CHECKR_BASE_URL: z.string().url().default('https://api.checkr.com'),
  REVENUECAT_REST_API_KEY: z.string().optional().default(''),
});

export type Config = z.infer<typeof EnvSchema>;

export function loadConfig(): Config {
  const parsed = EnvSchema.safeParse(process.env);
  if (!parsed.success) {
    // eslint-disable-next-line no-console
    console.error('Invalid environment configuration:', parsed.error.flatten().fieldErrors);
    process.exit(1);
  }
  const cfg = parsed.data;
  if (cfg.CRA_PROVIDER === 'checkr' && (!cfg.CHECKR_API_KEY || !cfg.CHECKR_WEBHOOK_SECRET)) {
    // eslint-disable-next-line no-console
    console.error('CRA_PROVIDER=checkr requires CHECKR_API_KEY and CHECKR_WEBHOOK_SECRET.');
    process.exit(1);
  }
  return cfg;
}
