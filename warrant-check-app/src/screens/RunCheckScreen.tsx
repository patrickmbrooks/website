import React, { useState } from 'react';
import { ScrollView, StyleSheet, TextInput, View } from 'react-native';

import { colors, radius, spacing } from '../theme';
import { Badge, Button, Card, H1, P } from '../components/UI';
import {
  BackgroundCheckResult,
  runBackgroundCheck,
} from '../services/backgroundCheck';

export default function RunCheckScreen() {
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [busy, setBusy] = useState(false);
  const [result, setResult] = useState<BackgroundCheckResult | null>(null);
  const [error, setError] = useState<string | null>(null);

  const submit = async () => {
    setBusy(true);
    setError(null);
    try {
      const res = await runBackgroundCheck({
        firstName,
        lastName,
        consentConfirmed: true,
        purpose: 'employment',
      });
      setResult(res);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Something went wrong.');
    } finally {
      setBusy(false);
    }
  };

  return (
    <ScrollView style={styles.container} contentContainerStyle={{ padding: spacing.md }}>
      <H1>Run a Report</H1>
      <Card>
        <P style={{ fontWeight: '700', marginBottom: spacing.sm }}>Subject details</P>
        <TextInput
          placeholder="First name"
          placeholderTextColor={colors.textMuted}
          value={firstName}
          onChangeText={setFirstName}
          style={styles.input}
        />
        <TextInput
          placeholder="Last name"
          placeholderTextColor={colors.textMuted}
          value={lastName}
          onChangeText={setLastName}
          style={styles.input}
        />
        <Button
          title={busy ? 'Running…' : 'Run background check'}
          onPress={submit}
          disabled={busy || !firstName || !lastName}
        />
      </Card>

      {error && (
        <Card>
          <Badge label="ERROR" tone="warning" />
          <P style={{ marginTop: spacing.sm }}>{error}</P>
        </Card>
      )}

      {result && (
        <Card>
          <View style={{ flexDirection: 'row', gap: spacing.sm, alignItems: 'center' }}>
            <Badge
              label={result.status.toUpperCase()}
              tone={result.status === 'clear' ? 'success' : 'warning'}
            />
            <P muted style={{ fontSize: 12 }}>#{result.reportId}</P>
          </View>
          {result.records.length === 0 ? (
            <P muted style={{ marginTop: spacing.sm }}>No records returned.</P>
          ) : (
            result.records.map((r, i) => (
              <P key={i} style={{ marginTop: spacing.sm }}>
                {r.type} — {r.jurisdiction}: {r.description}
              </P>
            ))
          )}
          <P muted style={{ fontSize: 12, marginTop: spacing.md }}>
            {result.disclaimer}
          </P>
        </Card>
      )}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  input: {
    backgroundColor: colors.surfaceAlt,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.border,
    color: colors.text,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm + 2,
    fontSize: 16,
    marginBottom: spacing.sm,
  },
});
