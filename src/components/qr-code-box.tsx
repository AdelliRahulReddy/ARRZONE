"use client";

import Image from "next/image";
import { useEffect, useState } from "react";
import QRCode from "qrcode";
import { cn } from "@/lib/utils";

type QrCodeBoxProps = {
  value: string;
  label: string;
  className?: string;
  size?: number;
};

export function QrCodeBox({
  value,
  label,
  className,
  size = 224,
}: QrCodeBoxProps) {
  const [dataUrl, setDataUrl] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;

    void QRCode.toDataURL(value, {
      errorCorrectionLevel: "H",
      margin: 2,
      width: Math.max(size * 2, 384),
    }).then((url: string) => {
      if (!cancelled) {
        setDataUrl(url);
      }
    });

    return () => {
      cancelled = true;
    };
  }, [value]);

  return (
    <div className={cn("space-y-3", className)}>
      <div className="inline-flex rounded-[32px] border border-slate-200/80 bg-white p-4 shadow-lg shadow-slate-900/10">
        {dataUrl ? (
          <Image
            src={dataUrl}
            alt={label}
            className="rounded-[24px] bg-white object-contain"
            width={size}
            height={size}
            unoptimized
          />
        ) : (
          <div
            className="animate-pulse rounded-[24px] bg-muted"
            style={{ width: size, height: size }}
          />
        )}
      </div>
      <p className="text-sm leading-6 text-muted-foreground">{label}</p>
    </div>
  );
}
