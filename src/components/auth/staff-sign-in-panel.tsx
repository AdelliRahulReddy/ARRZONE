"use client";

import { startTransition, useRef, useState, type FormEvent } from "react";
import { FirebaseError } from "firebase/app";
import {
  GoogleAuthProvider,
  linkWithCredential,
  signInWithEmailAndPassword,
  signInWithPopup,
  type AuthCredential,
  type UserCredential,
} from "firebase/auth";
import { useRouter } from "next/navigation";
import { Globe, LoaderCircle, LockKeyhole } from "lucide-react";
import {
  getAuthSurfaceDefinition,
  sanitizeRedirectTarget,
  type AuthSurface,
} from "@/lib/auth/presentation";
import { getFirebaseClientAuth, createGoogleAuthProvider } from "@/lib/firebase/client";
import { SignOutButton } from "@/components/auth/sign-out-button";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

type SessionIssue = {
  title: string;
  message: string;
  showSignOut: boolean;
};

type StaffSignInPanelProps = {
  surface: AuthSurface;
  redirectTo: string;
  sessionIssue?: SessionIssue | null;
};

type SignInFieldErrors = {
  email: string;
  password: string;
};

function getFirebaseErrorMessage(error: unknown) {
  if (!(error instanceof FirebaseError)) {
    return error instanceof Error ? error.message : "Something went wrong.";
  }

  switch (error.code) {
    case "auth/invalid-credential":
    case "auth/invalid-login-credentials":
    case "auth/wrong-password":
    case "auth/user-not-found":
      return "The email or password is incorrect.";
    case "auth/popup-blocked":
      return "The Google popup was blocked. Allow popups for this site and try again.";
    case "auth/popup-closed-by-user":
      return "The Google sign-in popup was closed before completion.";
    case "auth/too-many-requests":
      return "Too many attempts. Try again in a few minutes.";
    default:
      return error.message || "Authentication failed.";
  }
}

function validateFields(email: string, password: string): SignInFieldErrors {
  return {
    email: email.trim()
      ? /\S+@\S+\.\S+/.test(email.trim())
        ? ""
        : "Enter a valid email address."
      : "Email is required.",
    password: password ? "" : "Password is required.",
  };
}

async function exchangeIdToken(idToken: string, redirectTo: string) {
  const response = await fetch("/api/auth/session", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ idToken, redirectTo }),
  });

  const payload = (await response.json().catch(() => null)) as
    | {
        data?: { redirectTo?: string };
        error?: { message?: string };
      }
    | null;

  if (!response.ok) {
    throw new Error(payload?.error?.message || "Failed to start the session.");
  }

  return sanitizeRedirectTarget(payload?.data?.redirectTo) ?? redirectTo;
}

async function clearClientAuthState() {
  try {
    const auth = await getFirebaseClientAuth();
    await auth.signOut();
  } catch {
    // Ignore clean-up failures and keep the UI focused on the original error.
  }
}

export function StaffSignInPanel({
  surface,
  redirectTo,
  sessionIssue,
}: StaffSignInPanelProps) {
  const router = useRouter();
  const definition = getAuthSurfaceDefinition(surface);
  const emailRef = useRef<HTMLInputElement>(null);
  const passwordRef = useRef<HTMLInputElement>(null);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [errorMessage, setErrorMessage] = useState("");
  const [fieldErrors, setFieldErrors] = useState<SignInFieldErrors>({
    email: "",
    password: "",
  });
  const [pendingProviderLink, setPendingProviderLink] =
    useState<AuthCredential | null>(null);
  const [passwordPending, setPasswordPending] = useState(false);
  const [googlePending, setGooglePending] = useState(false);

  const activeIssue = errorMessage
    ? {
        title: "Sign-In Failed",
        message: errorMessage,
        showSignOut: false,
      }
    : sessionIssue;

  function focusFirstError(nextErrors: SignInFieldErrors) {
    if (nextErrors.email) {
      emailRef.current?.focus();
      return;
    }

    if (nextErrors.password) {
      passwordRef.current?.focus();
    }
  }

  function validateBeforeSubmit() {
    const nextErrors = validateFields(email, password);
    setFieldErrors(nextErrors);
    focusFirstError(nextErrors);
    return !nextErrors.email && !nextErrors.password;
  }

  async function finalizeSignIn(userCredential: UserCredential) {
    if (pendingProviderLink) {
      try {
        await linkWithCredential(userCredential.user, pendingProviderLink);
      } catch (error) {
        if (
          !(error instanceof FirebaseError) ||
          error.code !== "auth/provider-already-linked"
        ) {
          throw error;
        }
      } finally {
        setPendingProviderLink(null);
      }
    }

    const idToken = await userCredential.user.getIdToken(true);
    const nextRedirect = await exchangeIdToken(idToken, redirectTo);
    startTransition(() => {
      router.replace(nextRedirect);
      router.refresh();
    });
  }

  async function handlePasswordSignIn(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setErrorMessage("");

    if (!validateBeforeSubmit()) {
      return;
    }

    setPasswordPending(true);

    try {
      const auth = await getFirebaseClientAuth();
      const userCredential = await signInWithEmailAndPassword(auth, email, password);
      await finalizeSignIn(userCredential);
    } catch (error) {
      await clearClientAuthState();
      setErrorMessage(getFirebaseErrorMessage(error));
    } finally {
      setPasswordPending(false);
    }
  }

  async function handleGoogleSignIn() {
    setErrorMessage("");
    setGooglePending(true);

    try {
      const auth = await getFirebaseClientAuth();
      const result = await signInWithPopup(auth, createGoogleAuthProvider());
      await finalizeSignIn(result);
    } catch (error) {
      if (
        error instanceof FirebaseError &&
        error.code === "auth/account-exists-with-different-credential"
      ) {
        const pendingCredential = GoogleAuthProvider.credentialFromError(error);
        const pendingEmail =
          typeof error.customData?.email === "string" ? error.customData.email : "";
        if (pendingEmail) {
          setEmail(pendingEmail);
        }
        setPendingProviderLink(pendingCredential);
        setErrorMessage(
          "This email already has a password-based account. Sign in with that password once to link Google.",
        );
      } else {
        await clearClientAuthState();
        setErrorMessage(getFirebaseErrorMessage(error));
      }
    } finally {
      setGooglePending(false);
    }
  }

  return (
    <Card className="border-border/70 bg-card/92 shadow-xl shadow-black/5">
      <CardHeader className="space-y-3">
        <div className="inline-flex w-fit rounded-full border border-border/60 bg-background/80 px-3 py-1 text-xs uppercase tracking-[0.24em] text-muted-foreground">
          {definition.panelBadge}
        </div>
        <div className="space-y-2">
          <CardTitle className="text-3xl tracking-tight text-balance">
            {definition.panelTitle}
          </CardTitle>
          <p className="text-sm leading-6 text-muted-foreground">
            {definition.panelDescription}
          </p>
        </div>
      </CardHeader>
      <CardContent className="space-y-5">
        {activeIssue ? (
          <div className="space-y-3" aria-live="polite">
            <Alert variant="destructive">
              <AlertTitle>{activeIssue.title}</AlertTitle>
              <AlertDescription>{activeIssue.message}</AlertDescription>
            </Alert>
            {activeIssue.showSignOut ? <SignOutButton /> : null}
          </div>
        ) : null}

        <form className="space-y-4" onSubmit={handlePasswordSignIn} noValidate>
          <div className="space-y-2">
            <Label htmlFor="staff-email">Email</Label>
            <Input
              ref={emailRef}
              id="staff-email"
              name="email"
              type="email"
              value={email}
              onChange={(event) => {
                setEmail(event.target.value);
                setErrorMessage("");
                if (fieldErrors.email) {
                  setFieldErrors((current) => ({ ...current, email: "" }));
                }
              }}
              autoComplete="email"
              autoCapitalize="none"
              inputMode="email"
              spellCheck={false}
              required
              aria-invalid={Boolean(fieldErrors.email)}
              aria-describedby={fieldErrors.email ? "staff-email-error" : undefined}
            />
            {fieldErrors.email ? (
              <p
                id="staff-email-error"
                className="text-sm text-destructive"
                aria-live="polite"
              >
                {fieldErrors.email}
              </p>
            ) : null}
          </div>
          <div className="space-y-2">
            <Label htmlFor="staff-password">Password</Label>
            <Input
              ref={passwordRef}
              id="staff-password"
              name="password"
              type="password"
              value={password}
              onChange={(event) => {
                setPassword(event.target.value);
                setErrorMessage("");
                if (fieldErrors.password) {
                  setFieldErrors((current) => ({ ...current, password: "" }));
                }
              }}
              autoComplete="current-password"
              required
              aria-invalid={Boolean(fieldErrors.password)}
              aria-describedby={fieldErrors.password ? "staff-password-error" : undefined}
            />
            {fieldErrors.password ? (
              <p
                id="staff-password-error"
                className="text-sm text-destructive"
                aria-live="polite"
              >
                {fieldErrors.password}
              </p>
            ) : null}
          </div>
          <Button
            type="submit"
            className="w-full rounded-full"
            disabled={passwordPending || googlePending}
          >
            {passwordPending ? (
              <LoaderCircle aria-hidden="true" className="animate-spin" />
            ) : (
              <LockKeyhole aria-hidden="true" />
            )}
            {passwordPending ? "Signing in…" : "Continue with Password"}
          </Button>
        </form>

        <div className="relative py-1">
          <div className="absolute inset-0 flex items-center">
            <div className="w-full border-t border-border/70" />
          </div>
          <div className="relative flex justify-center">
            <span className="bg-card px-3 text-xs uppercase tracking-[0.24em] text-muted-foreground">
              Or
            </span>
          </div>
        </div>

        <Button
          type="button"
          variant="outline"
          className="w-full rounded-full"
          onClick={handleGoogleSignIn}
          disabled={passwordPending || googlePending}
        >
          {googlePending ? (
            <LoaderCircle aria-hidden="true" className="animate-spin" />
          ) : (
            <Globe aria-hidden="true" />
          )}
          {googlePending ? "Connecting Google…" : "Continue with Google"}
        </Button>

        <div className="rounded-2xl border border-border/70 bg-background/70 px-4 py-3 text-sm leading-6 text-muted-foreground">
          After sign-in, you&apos;ll return to{" "}
          <span className="font-mono text-foreground" translate="no">
            {redirectTo}
          </span>
          .
        </div>
      </CardContent>
    </Card>
  );
}
