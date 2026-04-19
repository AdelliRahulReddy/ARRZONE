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

  try {
    const url = new URL(raw);
    const match = url.pathname.match(/\/pass\/([^/]+)/);
    if (match?.[1]) {
      return {
        type: "PASS",
        raw,
        token: decodeURIComponent(match[1]),
        href: url.toString(),
      };
    }
  } catch {
    // Ignore invalid URLs and fall through to unknown payload.
  }

  return { type: "UNKNOWN", raw };
}
