"use client";

import Image from "next/image";
import { useEffect, useState } from "react";
import QRCode from "qrcode";
import { cn } from "@/lib/utils";

type QrCodeBoxProps = {
  value: string;
  label: string;
  className?: string;
};

export function QrCodeBox({ value, label, className }: QrCodeBoxProps) {
  const [dataUrl, setDataUrl] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;

    void QRCode.toDataURL(value, {
      errorCorrectionLevel: "M",
      margin: 1,
      scale: 8,
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
      <div className="inline-flex rounded-[28px] border border-border/70 bg-white p-4 shadow-sm">
        {dataUrl ? (
          <Image
            src={dataUrl}
            alt={label}
            className="size-52 rounded-2xl bg-white object-contain"
            width={208}
            height={208}
            unoptimized
          />
        ) : (
          <div className="size-52 animate-pulse rounded-2xl bg-muted" />
        )}
      </div>
      <p className="text-sm leading-6 text-muted-foreground">{label}</p>
    </div>
  );
}
