import React, { useEffect, useState } from 'react';
import { ScrollView, StyleSheet } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { colors, spacing } from '../theme';
import { Badge, Button, Card, H1, P } from '../components/UI';
import { BackgroundStackParamList } from '../navigation/types';
import { getEntitlement, onEntitlementChange, EntitlementStatus } from '../services/subscriptions';

type Props = NativeStackScreenProps<BackgroundStackParamList, 'BackgroundIntro'>;

export default function BackgroundIntroScreen({ navigation }: Props) {
  const [status, setStatus] = useState<EntitlementStatus>(getEntitlement());

  useEffect(() => onEntitlementChange(setStatus), []);

  const isPro = status === 'subscribed';

  return (
    <ScrollView style={styles.container} contentContainerStyle={{ padding: spacing.md }}>
      <H1>Background Checks</H1>
      <P muted style={{ marginBottom: spacing.md }}>
        Run a comprehensive background and warrant report through a vetted,
        FCRA-compliant data provider. This is a paid feature.
      </P>

      <Card>
        <Badge label={isPro ? 'PRO ACTIVE' : 'PRO REQUIRED'} tone={isPro ? 'success' : 'warning'} />
        <P style={{ fontWeight: '700', marginTop: spacing.sm }}>
          Comprehensive screening report
        </P>
        <P muted style={{ marginVertical: spacing.sm }}>
          National criminal, warrant, and court-record search compiled by a
          consumer reporting agency. Requires a permissible purpose and the
          subject's written authorization.
        </P>
        {isPro ? (
          <Button title="Start a report" onPress={() => navigation.navigate('FcraConsent')} />
        ) : (
          <Button title="Subscribe to unlock" onPress={() => navigation.navigate('Subscription')} />
        )}
      </Card>

      <Card>
        <P style={{ fontWeight: '700' }}>Need a free warrant lookup instead?</P>
        <P muted style={{ marginVertical: spacing.sm }}>
          The Warrants tab links to official government lookups at no cost.
        </P>
      </Card>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
});
