import type { DB } from '../db.js';
import { randomUUID } from 'node:crypto';

export interface ConsentInput {
  requesterId: string;
  subjectFirstName: string;
  subjectLastName: string;
  subjectEmail?: string;
  purpose: string;
  disclosureText: string;
  signedAt: string;
  ipAddress?: string | null;
}

export interface ConsentRecord extends ConsentInput {
  id: string;
  createdAt: string;
}

export class ConsentRepo {
  constructor(private db: DB) {}

  create(input: ConsentInput): ConsentRecord {
    const id = randomUUID();
    this.db
      .prepare(
        `INSERT INTO consents
         (id, requester_id, subject_first_name, subject_last_name, subject_email,
          purpose, disclosure_text, signed_at, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      )
      .run(
        id,
        input.requesterId,
        input.subjectFirstName,
        input.subjectLastName,
        input.subjectEmail ?? null,
        input.purpose,
        input.disclosureText,
        input.signedAt,
        input.ipAddress ?? null,
      );
    const row = this.db.prepare(`SELECT * FROM consents WHERE id = ?`).get(id) as {
      created_at: string;
    };
    return { id, createdAt: row.created_at, ...input };
  }
}
