import Database from 'better-sqlite3';
import path from 'node:path';
import fs from 'node:fs';

export type DB = Database.Database;

export function openDb(filePath: string): DB {
  const dir = path.dirname(filePath);
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
  const db = new Database(filePath);
  db.pragma('journal_mode = WAL');
  db.pragma('foreign_keys = ON');
  migrate(db);
  return db;
}

function migrate(db: DB): void {
  db.exec(`
    CREATE TABLE IF NOT EXISTS consents (
      id TEXT PRIMARY KEY,
      requester_id TEXT NOT NULL,
      subject_first_name TEXT NOT NULL,
      subject_last_name TEXT NOT NULL,
      subject_email TEXT,
      purpose TEXT NOT NULL,
      disclosure_text TEXT NOT NULL,
      signed_at TEXT NOT NULL,
      ip_address TEXT,
      created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS reports (
      id TEXT PRIMARY KEY,
      consent_id TEXT NOT NULL REFERENCES consents(id),
      requester_id TEXT NOT NULL,
      provider TEXT NOT NULL,
      provider_report_id TEXT NOT NULL,
      status TEXT NOT NULL,
      records_json TEXT NOT NULL DEFAULT '[]',
      created_at TEXT NOT NULL DEFAULT (datetime('now')),
      updated_at TEXT NOT NULL DEFAULT (datetime('now')),
      completed_at TEXT
    );

    CREATE INDEX IF NOT EXISTS idx_reports_requester ON reports(requester_id);
    CREATE INDEX IF NOT EXISTS idx_reports_provider_report ON reports(provider, provider_report_id);
  `);
}
