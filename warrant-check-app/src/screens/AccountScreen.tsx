import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { colors, spacing } from '../theme';
import { Badge, Button, Card, H1, P } from '../components/UI';
import {
  EntitlementStatus,
  getEntitlement,
  onEntitlementChange,
  restorePurchases,
} from '../services/subscriptions';

export default function AccountScreen() {
  const [status, setStatus] = useState<EntitlementStatus>(getEntitlement());
  useEffect(() => onEntitlementChange(setStatus), []);

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <ScrollView contentContainerStyle={{ padding: spacing.md }}>
        <H1>Account</H1>
        <Card>
          <Badge
            label={status === 'subscribed' ? 'PRO' : 'FREE'}
            tone={status === 'subscribed' ? 'success' : 'warning'}
          />
          <P style={{ marginTop: spacing.sm }}>
            {status === 'subscribed'
              ? 'You have an active Pro subscription.'
              : 'You are on the free plan. The warrant directory is always free.'}
          </P>
        </Card>

        <Card>
          <P style={{ fontWeight: '700' }}>Manage subscription</P>
          <P muted style={{ marginVertical: spacing.sm }}>
            Subscriptions are billed and cancelled through your App Store / Google
            Play account settings.
          </P>
          <Button title="Restore purchases" variant="secondary" onPress={() => restorePurchases()} />
        </Card>

        <Card>
          <P style={{ fontWeight: '700' }}>Legal</P>
          <P muted style={{ marginTop: spacing.sm }}>
            This app is not legal advice. Warrant and record data may be
            incomplete or out of date. Background-check reports are consumer
            reports governed by the FCRA and must be used accordingly.
          </P>
        </Card>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.bg },
});
