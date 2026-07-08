import { loadConfig } from './config.js';
import { buildServer } from './server.js';

const cfg = loadConfig();

buildServer(cfg)
  .then(async (app) => {
    await app.listen({ port: cfg.PORT, host: '0.0.0.0' });
  })
  .catch((err) => {
    // eslint-disable-next-line no-console
    console.error('Failed to start server:', err);
    process.exit(1);
  });
