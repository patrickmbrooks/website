import React, { useState } from 'react';
import { ScrollView, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { colors, spacing } from '../theme';
import { Button, Card, H1, P } from '../components/UI';
import { BackgroundStackParamList } from '../navigation/types';
import { PLANS, purchase, restorePurchases } from '../services/subscriptions';
import { SUBSCRIPTION_TERMS } from '../legal/disclaimers';

type Props = NativeStackScreenProps<BackgroundStackParamList, 'Subscription'>;

export default function SubscriptionScreen({ navigation }: Props) {
  const [busy, setBusy] = useState<string | null>(null);

  const buy = async (planId: string) => {
    setBusy(planId);
    try {
      await purchase(planId);
      navigation.replace('FcraConsent');
    } finally {
      setBusy(null);
    }
  };

  return (
    <ScrollView style={styles.container} contentContainerStyle={{ padding: spacing.md }}>
      <H1>Go Pro</H1>
      <P muted style={{ marginBottom: spacing.md }}>
        Unlock background-check reports. Subscriptions are billed through the App
        Store / Google Play and can be managed or cancelled there.
      </P>

      {PLANS.map((plan) => (
        <Card key={plan.id}>
          <View style={styles.planHeader}>
            <P style={{ fontWeight: '700', fontSize: 18 }}>{plan.title}</P>
            <P style={{ fontWeight: '700', color: colors.primary }}>{plan.priceLabel}</P>
          </View>
          {plan.features.map((f, i) => (
            <P key={i} muted style={{ marginTop: spacing.xs }}>
              • {f}
            </P>
          ))}
          <View style={{ height: spacing.md }} />
          <Button
            title={busy === plan.id ? 'Processing…' : `Subscribe — ${plan.priceLabel}`}
            onPress={() => buy(plan.id)}
            disabled={!!busy}
          />
        </Card>
      ))}

      <Button title="Restore purchases" variant="secondary" onPress={() => restorePurchases()} />
      <P muted style={{ fontSize: 12, marginTop: spacing.md }}>
        {SUBSCRIPTION_TERMS}
      </P>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  planHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
});
