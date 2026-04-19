"use client";

import { getApp, getApps, initializeApp, type FirebaseApp } from "firebase/app";
import {
  browserLocalPersistence,
  getAuth,
  GoogleAuthProvider,
  setPersistence,
  type Auth,
} from "firebase/auth";
import { requireConfiguredValue } from "@/lib/env";

const firebaseWebConfig = {
  apiKey: requireConfiguredValue(
    process.env.NEXT_PUBLIC_FIREBASE_API_KEY ?? "",
    "NEXT_PUBLIC_FIREBASE_API_KEY",
  ),
  authDomain: requireConfiguredValue(
    process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN ?? "",
    "NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN",
  ),
  projectId: requireConfiguredValue(
    process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID ?? "",
    "NEXT_PUBLIC_FIREBASE_PROJECT_ID",
  ),
  appId: requireConfiguredValue(
    process.env.NEXT_PUBLIC_FIREBASE_APP_ID ?? "",
    "NEXT_PUBLIC_FIREBASE_APP_ID",
  ),
};

let authPromise: Promise<Auth> | null = null;

function getFirebaseApp(): FirebaseApp {
  if (getApps().length > 0) {
    return getApp();
  }

  return initializeApp(firebaseWebConfig);
}

export function getFirebaseClientAuth() {
  if (!authPromise) {
    const auth = getAuth(getFirebaseApp());
    authPromise = setPersistence(auth, browserLocalPersistence).then(() => auth);
  }

  return authPromise;
}

export function createGoogleAuthProvider() {
  const provider = new GoogleAuthProvider();
  provider.setCustomParameters({ prompt: "select_account" });
  return provider;
}

