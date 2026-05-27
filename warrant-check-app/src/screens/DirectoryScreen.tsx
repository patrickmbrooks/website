import React, { useMemo, useState } from 'react';
import {
  FlatList,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { NativeStackScreenProps } from '@react-navigation/native-stack';

import { colors, radius, spacing } from '../theme';
import { Badge, P } from '../components/UI';
import { DIRECTORY_DISCLAIMER } from '../legal/disclaimers';
import { searchJurisdictions } from '../data/jurisdictions';
import { DirectoryStackParamList } from '../navigation/types';
import { Jurisdiction } from '../data/types';

type Props = NativeStackScreenProps<DirectoryStackParamList, 'Directory'>;

export default function DirectoryScreen({ navigation }: Props) {
  const [query, setQuery] = useState('');
  const results = useMemo(() => searchJurisdictions(query), [query]);

  const renderItem = ({ item }: { item: Jurisdiction }) => (
    <Pressable
      style={({ pressed }) => [styles.row, pressed && { opacity: 0.7 }]}
      onPress={() => navigation.navigate('StateDetail', { jurisdictionId: item.id })}
    >
      <View style={{ flex: 1 }}>
        <Text style={styles.rowTitle}>{item.name}</Text>
        <Text style={styles.rowSub}>
          {item.level === 'state' ? 'State' : `County · ${item.state}`}
        </Text>
      </View>
      {!item.verified && <Badge label="VERIFY" tone="warning" />}
    </Pressable>
  );

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <View style={styles.header}>
        <TextInput
          placeholder="Search a state or county…"
          placeholderTextColor={colors.textMuted}
          value={query}
          onChangeText={setQuery}
          style={styles.input}
          autoCorrect={false}
        />
        <P muted style={styles.disclaimer}>
          {DIRECTORY_DISCLAIMER}
        </P>
      </View>
      <FlatList
        data={results}
        keyExtractor={(j) => j.id}
        renderItem={renderItem}
        contentContainerStyle={{ padding: spacing.md, paddingBottom: spacing.xl }}
        ListEmptyComponent={<P muted>No jurisdictions match “{query}”.</P>}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.bg },
  header: { padding: spacing.md, borderBottomWidth: 1, borderBottomColor: colors.border },
  input: {
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.border,
    color: colors.text,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm + 2,
    fontSize: 16,
  },
  disclaimer: { fontSize: 12, marginTop: spacing.sm },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.border,
    padding: spacing.md,
    marginBottom: spacing.sm,
  },
  rowTitle: { color: colors.text, fontSize: 16, fontWeight: '600' },
  rowSub: { color: colors.textMuted, fontSize: 13, marginTop: 2 },
});
