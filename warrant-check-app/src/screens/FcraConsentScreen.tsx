import React, { useState } from 'react';
import { Pressable, ScrollView, StyleSheet, View } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { colors, radius, spacing } from '../theme';
import { Button, Card, H1, P } from '../components/UI';
import { BackgroundStackParamList } from '../navigation/types';
import { FCRA_CONSENT_BODY, FCRA_CONSENT_TITLE } from '../legal/disclaimers';

type Props = NativeStackScreenProps<BackgroundStackParamList, 'FcraConsent'>;

export default function FcraConsentScreen({ navigation }: Props) {
  const [checked, setChecked] = useState(false);

  return (
    <ScrollView style={styles.container} contentContainerStyle={{ padding: spacing.md }}>
      <H1>{FCRA_CONSENT_TITLE}</H1>
      <Card>
        <P>{FCRA_CONSENT_BODY}</P>
      </Card>

      <Pressable style={styles.checkRow} onPress={() => setChecked((c) => !c)}>
        <View style={[styles.checkbox, checked && styles.checkboxOn]}>
          {checked ? <P style={{ color: '#fff' }}>✓</P> : null}
        </View>
        <P style={{ flex: 1 }}>
          I confirm I have a permissible purpose and the subject's written
          authorization on file.
        </P>
      </Pressable>

      <Button
        title="Continue"
        onPress={() => navigation.navigate('RunCheck')}
        disabled={!checked}
      />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  checkRow: { flexDirection: 'row', alignItems: 'center', gap: spacing.md, marginVertical: spacing.md },
  checkbox: {
    width: 28,
    height: 28,
    borderRadius: radius.sm,
    borderWidth: 2,
    borderColor: colors.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  checkboxOn: { backgroundColor: colors.primary, borderColor: colors.primary },
});
