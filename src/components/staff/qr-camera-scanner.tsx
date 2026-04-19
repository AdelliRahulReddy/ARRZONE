"use client";

import dynamic from "next/dynamic";

const Scanner = dynamic(
  () => import("@yudiel/react-qr-scanner").then((mod) => mod.Scanner),
  {
    ssr: false,
    loading: () => (
      <div className="flex aspect-square w-full items-center justify-center rounded-[1.75rem] border border-border/70 bg-muted/40 px-6 text-center text-sm text-muted-foreground">
        Opening the camera scanner...
      </div>
    ),
  },
);

type StaffQrCameraScannerProps = {
  paused?: boolean;
  onDetected: (rawValue: string) => void;
  onError?: (message: string) => void;
};

export function StaffQrCameraScanner({
  paused = false,
  onDetected,
  onError,
}: StaffQrCameraScannerProps) {
  return (
    <Scanner
      allowMultiple={false}
      classNames={{
        container:
          "aspect-square w-full overflow-hidden rounded-[1.75rem] border border-border/70 bg-black",
        video: "h-full w-full object-cover",
      }}
      components={{
        finder: true,
        onOff: true,
        torch: true,
        zoom: true,
      }}
      constraints={{ facingMode: "environment" }}
      formats={["qr_code"]}
      paused={paused}
      scanDelay={1200}
      sound
      onError={(error) => {
        const message =
          error instanceof Error
            ? error.message
            : "Camera access is unavailable on this device.";
        onError?.(message);
      }}
      onScan={(detectedCodes) => {
        const firstDetectedCode = detectedCodes.find((code) => code.rawValue.trim());
        if (firstDetectedCode?.rawValue) {
          onDetected(firstDetectedCode.rawValue);
        }
      }}
    />
  );
}
