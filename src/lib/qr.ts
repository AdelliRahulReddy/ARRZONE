const REDEEM_PREFIX = "LOYALTY_REDEEM:";

export type ParsedScanPayload =
  | {
      type: "PASS";
      raw: string;
      token: string;
      href: string;
    }
  | {
      type: "REDEEM";
      raw: string;
      token: string;
    }
  | {
      type: "UNKNOWN";
      raw: string;
    };

export function buildRedeemQrPayload(token: string) {
  return `${REDEEM_PREFIX}${token}`;
}

export function parseScanPayload(rawValue: string): ParsedScanPayload {
  const raw = rawValue.trim();
  if (!raw) {
    return { type: "UNKNOWN", raw };
  }

  if (raw.startsWith(REDEEM_PREFIX)) {
    return {
      type: "REDEEM",
      raw,
      token: raw.slice(REDEEM_PREFIX.length),
    };
  }

  const passPathMatch = raw.match(/(?:https?:\/\/[^/\s]+)?\/pass\/([^/?#\s]+)/i);
  if (passPathMatch?.[1]) {
    const token = decodeURIComponent(passPathMatch[1]);
    return {
      type: "PASS",
      raw,
      token,
      href: raw,
    };
  }

  if (/^[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/.test(raw)) {
    return {
      type: "PASS",
      raw,
      token: raw,
      href: `/pass/${encodeURIComponent(raw)}`,
    };
  }

  return { type: "UNKNOWN", raw };
}
