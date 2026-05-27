import React from 'react';
import { NavigationContainer, DefaultTheme } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Text } from 'react-native';

import { colors } from '../theme';
import { DirectoryStackParamList, BackgroundStackParamList } from './types';
import DirectoryScreen from '../screens/DirectoryScreen';
import StateDetailScreen from '../screens/StateDetailScreen';
import BackgroundIntroScreen from '../screens/BackgroundIntroScreen';
import SubscriptionScreen from '../screens/SubscriptionScreen';
import FcraConsentScreen from '../screens/FcraConsentScreen';
import RunCheckScreen from '../screens/RunCheckScreen';
import AccountScreen from '../screens/AccountScreen';

const DirectoryStack = createNativeStackNavigator<DirectoryStackParamList>();
const BackgroundStack = createNativeStackNavigator<BackgroundStackParamList>();
const Tab = createBottomTabNavigator();

const navTheme = {
  ...DefaultTheme,
  colors: {
    ...DefaultTheme.colors,
    background: colors.bg,
    card: colors.surface,
    text: colors.text,
    border: colors.border,
    primary: colors.primary,
  },
};

const screenOptions = {
  headerStyle: { backgroundColor: colors.surface },
  headerTitleStyle: { color: colors.text },
  headerTintColor: colors.primary,
  contentStyle: { backgroundColor: colors.bg },
};

function DirectoryNavigator() {
  return (
    <DirectoryStack.Navigator screenOptions={screenOptions}>
      <DirectoryStack.Screen
        name="Directory"
        component={DirectoryScreen}
        options={{ title: 'Warrant Directory' }}
      />
      <DirectoryStack.Screen
        name="StateDetail"
        component={StateDetailScreen}
        options={{ title: 'Jurisdiction' }}
      />
    </DirectoryStack.Navigator>
  );
}

function BackgroundNavigator() {
  return (
    <BackgroundStack.Navigator screenOptions={screenOptions}>
      <BackgroundStack.Screen
        name="BackgroundIntro"
        component={BackgroundIntroScreen}
        options={{ title: 'Background Checks' }}
      />
      <BackgroundStack.Screen
        name="Subscription"
        component={SubscriptionScreen}
        options={{ title: 'Go Pro' }}
      />
      <BackgroundStack.Screen
        name="FcraConsent"
        component={FcraConsentScreen}
        options={{ title: 'Authorization' }}
      />
      <BackgroundStack.Screen
        name="RunCheck"
        component={RunCheckScreen}
        options={{ title: 'Run a Report' }}
      />
    </BackgroundStack.Navigator>
  );
}

const tabIcon = (label: string) => () => <Text style={{ fontSize: 18 }}>{label}</Text>;

export default function RootNavigator() {
  return (
    <NavigationContainer theme={navTheme}>
      <Tab.Navigator
        screenOptions={{
          headerShown: false,
          tabBarStyle: { backgroundColor: colors.surface, borderTopColor: colors.border },
          tabBarActiveTintColor: colors.primary,
          tabBarInactiveTintColor: colors.textMuted,
        }}
      >
        <Tab.Screen
          name="DirectoryTab"
          component={DirectoryNavigator}
          options={{ title: 'Warrants', tabBarIcon: tabIcon('🔎') }}
        />
        <Tab.Screen
          name="BackgroundTab"
          component={BackgroundNavigator}
          options={{ title: 'Background', tabBarIcon: tabIcon('📋') }}
        />
        <Tab.Screen
          name="AccountTab"
          component={AccountScreen}
          options={{ title: 'Account', tabBarIcon: tabIcon('👤') }}
        />
      </Tab.Navigator>
    </NavigationContainer>
  );
}
