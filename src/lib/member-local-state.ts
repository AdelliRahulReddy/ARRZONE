export type EnrollmentDraft = {
  fullName: string;
  phone: string;
  email: string;
  planId: string;
};

export type SavedMemberPass = {
  branchCode: string;
  passUrl: string;
  savedAt: string;
};

const STORAGE_PREFIX = "loyalty-saas";

function canUseStorage() {
  return typeof window !== "undefined" && typeof window.localStorage !== "undefined";
}

function draftKey(branchCode: string) {
  return `${STORAGE_PREFIX}:enrollment-draft:${branchCode}`;
}

function passKey(branchCode: string) {
  return `${STORAGE_PREFIX}:saved-pass:${branchCode}`;
}

function readJson<T>(key: string) {
  if (!canUseStorage()) {
    return null;
  }

  try {
    const raw = window.localStorage.getItem(key);
    return raw ? (JSON.parse(raw) as T) : null;
  } catch {
    return null;
  }
}

function writeJson(key: string, value: unknown) {
  if (!canUseStorage()) {
    return;
  }

  try {
    window.localStorage.setItem(key, JSON.stringify(value));
  } catch {
    // Ignore storage failures and keep the core flow working.
  }
}

function removeKey(key: string) {
  if (!canUseStorage()) {
    return;
  }

  try {
    window.localStorage.removeItem(key);
  } catch {
    // Ignore storage failures and keep the core flow working.
  }
}

export function getEnrollmentDraft(branchCode: string) {
  return readJson<EnrollmentDraft>(draftKey(branchCode));
}

export function saveEnrollmentDraft(branchCode: string, draft: EnrollmentDraft) {
  writeJson(draftKey(branchCode), draft);
}

export function clearEnrollmentDraft(branchCode: string) {
  removeKey(draftKey(branchCode));
}

export function getSavedMemberPass(branchCode: string) {
  return readJson<SavedMemberPass>(passKey(branchCode));
}

export function saveMemberPass(branchCode: string, passUrl: string) {
  writeJson(passKey(branchCode), {
    branchCode,
    passUrl,
    savedAt: new Date().toISOString(),
  } satisfies SavedMemberPass);
}

export function clearSavedMemberPass(branchCode: string) {
  removeKey(passKey(branchCode));
}
