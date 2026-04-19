import { appEnv } from "@/lib/env";

export function normalizePhoneNumber(input: string) {
  const trimmed = input.trim();
  if (!trimmed) {
    throw new Error("Phone number is required.");
  }

  const digitsOnly = trimmed.replace(/[^\d+]/g, "");
  if (digitsOnly.startsWith("+")) {
    const normalized = `+${digitsOnly.slice(1).replace(/\D/g, "")}`;
    if (normalized.length < 8) {
      throw new Error("Phone number is invalid.");
    }

    return normalized;
  }

  const countryCode = appEnv.defaultPhoneCountryCode.replace(/[^\d+]/g, "");
  const localDigits = digitsOnly.replace(/\D/g, "").replace(/^0+/, "");
  const normalized = `${countryCode}${localDigits}`;
  if (normalized.length < 8) {
    throw new Error("Phone number is invalid.");
  }

  return normalized;
}

export function maskPhoneNumber(phone: string) {
  if (phone.length <= 4) {
    return phone;
  }

  return `${phone.slice(0, 3)}••••${phone.slice(-3)}`;
}
