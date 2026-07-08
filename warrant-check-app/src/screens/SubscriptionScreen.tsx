import React, { useEffect, useState } from 'react';
import { ActivityIndicator, ScrollView, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { colors, spacing } from '../theme';
import { Badge, Button, Card, H1, P } from '../components/UI';
import { BackgroundStackParamList } from '../navigation/types';
import {
  SubscriptionPlan,
  getPlans,
  isSubscriptionsConfigured,
  purchase,
  restorePurchases,
} from '../services/subscriptions';
import { SUBSCRIPTION_TERMS } from '../legal/disclaimers';

type Props = NativeStackScreenProps<BackgroundStackParamList, 'Subscription'>;

export default function SubscriptionScreen({ navigation }: Props) {
  const [plans, setPlans] = useState<SubscriptionPlan[]>([]);
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const configured = isSubscriptionsConfigured();

  useEffect(() => {
    let mounted = true;
    (async () => {
      const p = await getPlans();
      if (mounted) {
        setPlans(p);
        setLoading(false);
      }
    })();
    return () => {
      mounted = false;
    };
  }, []);

  const buy = async (plan: SubscriptionPlan) => {
    setBusy(plan.id);
    setError(null);
    try {
      const status = await purchase(plan);
      if (status === 'subscribed') navigation.replace('FcraConsent');
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Purchase failed.');
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

      {!configured && (
        <Card>
          <Badge label="DEV MODE" tone="warning" />
          <P style={{ marginTop: spacing.sm }}>
            RevenueCat isn't configured yet. Add your public SDK keys in
            <P style={{ fontWeight: '700' }}> app.json → expo.extra </P>
            (or set EXPO_PUBLIC_REVENUECAT_API_KEY_IOS / _ANDROID) to enable real purchases.
          </P>
        </Card>
      )}

      {loading ? (
        <ActivityIndicator color={colors.primary} style={{ marginVertical: spacing.lg }} />
      ) : (
        plans.map((plan) => (
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
              onPress={() => buy(plan)}
              disabled={!!busy}
            />
          </Card>
        ))
      )}

      {error && (
        <Card>
          <Badge label="ERROR" tone="warning" />
          <P style={{ marginTop: spacing.sm }}>{error}</P>
        </Card>
      )}

      <Button
        title="Restore purchases"
        variant="secondary"
        onPress={() => restorePurchases()}
      />
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
