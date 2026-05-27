import React from 'react';
import { ScrollView, StyleSheet, View } from 'react-native';
import * as WebBrowser from 'expo-web-browser';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { colors, spacing } from '../theme';
import { Badge, Button, Card, H1, P } from '../components/UI';
import {
  buildSearchFallbackUrl,
  getCountiesForState,
  getJurisdictionById,
} from '../data/jurisdictions';
import { DirectoryStackParamList } from '../navigation/types';

type Props = NativeStackScreenProps<DirectoryStackParamList, 'StateDetail'>;

export default function StateDetailScreen({ route, navigation }: Props) {
  const jurisdiction = getJurisdictionById(route.params.jurisdictionId);

  if (!jurisdiction) {
    return (
      <View style={styles.container}>
        <P>Jurisdiction not found.</P>
      </View>
    );
  }

  const counties =
    jurisdiction.level === 'state' ? getCountiesForState(jurisdiction.state) : [];

  const open = (url: string) => WebBrowser.openBrowserAsync(url);

  return (
    <ScrollView style={styles.container} contentContainerStyle={{ padding: spacing.md }}>
      <H1>{jurisdiction.name}</H1>
      {jurisdiction.note ? (
        <P muted style={{ marginBottom: spacing.md }}>
          {jurisdiction.note}
        </P>
      ) : null}

      {jurisdiction.resources.length === 0 ? (
        <Card>
          <P>No curated official lookup yet for this jurisdiction.</P>
          <View style={{ height: spacing.md }} />
          <Button
            title="Search for the official site"
            variant="secondary"
            onPress={() => open(buildSearchFallbackUrl(jurisdiction.name))}
          />
        </Card>
      ) : (
        jurisdiction.resources.map((r, i) => (
          <Card key={i}>
            <View style={styles.cardHeader}>
              <P style={{ fontWeight: '700', flex: 1 }}>{r.label}</P>
              {!jurisdiction.verified && <Badge label="VERIFY" tone="warning" />}
            </View>
            <P muted style={{ marginVertical: spacing.sm }}>
              {r.description}
            </P>
            <Button title="Open official site" onPress={() => open(r.url)} />
          </Card>
        ))
      )}

      <Card>
        <P style={{ fontWeight: '700' }}>Still can't find it?</P>
        <P muted style={{ marginVertical: spacing.sm }}>
          Warrant lists are often published by the local sheriff or municipal court.
          Try a targeted search.
        </P>
        <Button
          title="Search the web"
          variant="secondary"
          onPress={() => open(buildSearchFallbackUrl(jurisdiction.name))}
        />
      </Card>

      {counties.length > 0 && (
        <>
          <H1 style={{ fontSize: 18, marginTop: spacing.md }}>Counties</H1>
          {counties.map((c) => (
            <Card key={c.id}>
              <View style={styles.cardHeader}>
                <P style={{ fontWeight: '700', flex: 1 }}>{c.name}</P>
                {!c.verified && <Badge label="VERIFY" tone="warning" />}
              </View>
              <Button
                title={`Open ${c.name}`}
                variant="secondary"
                onPress={() =>
                  navigation.push('StateDetail', { jurisdictionId: c.id })
                }
              />
            </Card>
          ))}
        </>
      )}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  cardHeader: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
});
