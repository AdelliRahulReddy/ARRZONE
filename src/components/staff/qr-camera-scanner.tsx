"use client";

import dynamic from "next/dynamic";
import { useEffect, useId, useState, type ChangeEvent } from "react";
import { Camera, RefreshCcw, ShieldAlert, Upload } from "lucide-react";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

const Scanner = dynamic(
  () => import("@yudiel/react-qr-scanner").then((mod) => mod.Scanner),
  {
    ssr: false,
    loading: () => (
      <div className="flex aspect-square w-full items-center justify-center rounded-[1.75rem] border border-border/70 bg-muted/40 px-6 text-center text-sm text-muted-foreground">
        Preparing the scanner...
      </div>
    ),
  },
);

type StaffQrCameraScannerProps = {
  active: boolean;
  onDetected: (rawValue: string) => void;
  onError?: (message: string) => void;
};

function isRearCamera(device: MediaDeviceInfo) {
  return /back|rear|environment/i.test(device.label);
}

function formatCameraLabel(device: MediaDeviceInfo, index: number) {
  return device.label || `Camera ${index + 1}`;
}

function getScannerErrorMessage(error: unknown) {
  if (error instanceof DOMException) {
    switch (error.name) {
      case "NotAllowedError":
      case "PermissionDeniedError":
        return "Camera permission was denied. Allow camera access in the browser settings or use the screenshot/manual fallback.";
      case "NotFoundError":
        return "No camera was found on this device. Use the screenshot fallback or paste the code manually.";
      case "NotReadableError":
      case "TrackStartError":
        return "The camera is busy in another app. Close other camera apps and try again.";
      case "OverconstrainedError":
        return "This device could not start the selected camera. Switch cameras and try again.";
      case "SecurityError":
        return "Camera access requires a secure HTTPS page.";
      case "AbortError":
        return "The camera was interrupted. Start it again when you are ready.";
      default:
        break;
    }
  }

  if (error instanceof Error) {
    const message = error.message.toLowerCase();
    if (message.includes("permission")) {
      return "Camera permission was denied. Allow camera access in the browser settings or use the screenshot/manual fallback.";
    }
    if (message.includes("secure")) {
      return "Camera access requires a secure HTTPS page.";
    }
    if (message.includes("not found")) {
      return "No camera was found on this device. Use the screenshot fallback or paste the code manually.";
    }
  }

  return "The camera could not start. Retry the scanner or use the screenshot/manual fallback.";
}

export function StaffQrCameraScanner({
  active,
  onDetected,
  onError,
}: StaffQrCameraScannerProps) {
  const fileInputId = useId();
  const [availableDevices, setAvailableDevices] = useState<MediaDeviceInfo[]>([]);
  const [selectedDeviceId, setSelectedDeviceId] = useState("");
  const [isCameraRunning, setIsCameraRunning] = useState(false);
  const [cameraError, setCameraError] = useState<string | null>(null);
  const [uploadError, setUploadError] = useState<string | null>(null);
  const [isReadingImage, setIsReadingImage] = useState(false);

  useEffect(() => {
    async function warmScannerRuntime() {
      try {
        const { prepareZXingModule } = await import("barcode-detector");
        await prepareZXingModule();
      } catch {
        // Ignore pre-warm failures. The detector can still fall back to lazy startup.
      }
    }

    void warmScannerRuntime();
  }, []);

  useEffect(() => {
    if (typeof navigator === "undefined" || !navigator.mediaDevices?.enumerateDevices) {
      return;
    }

    let cancelled = false;

    async function syncDevices() {
      try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        if (cancelled) {
          return;
        }

        const videoInputs = devices.filter((device) => device.kind === "videoinput");
        setAvailableDevices(videoInputs);
        setSelectedDeviceId((current) => {
          if (current && videoInputs.some((device) => device.deviceId === current)) {
            return current;
          }

          const preferredDevice =
            videoInputs.find(isRearCamera) ?? videoInputs[0] ?? null;
          return preferredDevice?.deviceId ?? "";
        });
      } catch {
        // Ignore device-enumeration failures. The live scanner can still request camera access directly.
      }
    }

    void syncDevices();

    const handleDeviceChange = () => {
      void syncDevices();
    };

    navigator.mediaDevices.addEventListener?.("devicechange", handleDeviceChange);

    return () => {
      cancelled = true;
      navigator.mediaDevices.removeEventListener?.("devicechange", handleDeviceChange);
    };
  }, []);

  useEffect(() => {
    if (!active) {
      setIsCameraRunning(false);
      setCameraError(null);
    }
  }, [active]);

  async function handleImageSelection(event: ChangeEvent<HTMLInputElement>) {
    const file = event.target.files?.[0];
    event.target.value = "";

    if (!file) {
      return;
    }

    setIsReadingImage(true);
    setUploadError(null);
    setCameraError(null);

    try {
      const { BarcodeDetector, prepareZXingModule } = await import("barcode-detector");
      try {
        await prepareZXingModule();
      } catch {
        // Ignore runtime warm-up failures and let detection attempt proceed.
      }
      const detector = new BarcodeDetector({ formats: ["qr_code"] });
      const results = await detector.detect(file);
      const matchingCode = results.find((result) => result.rawValue.trim());

      if (!matchingCode?.rawValue) {
        throw new Error(
          "No QR code was found in that image. Capture a clearer screenshot or use the live camera.",
        );
      }

      onDetected(matchingCode.rawValue);
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "The image could not be scanned. Use the live camera or paste the code manually.";
      setUploadError(message);
      onError?.(message);
    } finally {
      setIsReadingImage(false);
    }
  }

  const shouldRenderLiveCamera = active && isCameraRunning;
  const selectedConstraints = selectedDeviceId
    ? {
        deviceId: { exact: selectedDeviceId },
        width: { ideal: 720 },
        height: { ideal: 720 },
        aspectRatio: { ideal: 1 },
        frameRate: { ideal: 18, max: 24 },
      }
    : {
        facingMode: { ideal: "environment" },
        width: { ideal: 720 },
        height: { ideal: 720 },
        aspectRatio: { ideal: 1 },
        frameRate: { ideal: 18, max: 24 },
      };

  return (
    <div className="space-y-4 rounded-3xl border border-border/70 bg-background/70 p-4">
      <div className="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div className="space-y-1">
          <p className="font-medium">Live camera scanner</p>
          <p className="text-sm leading-6 text-muted-foreground">
            Start the camera only when you are ready to scan. The scanner stops after it resolves a QR code.
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button
            type="button"
            className="rounded-full"
            onClick={() => {
              setCameraError(null);
              setUploadError(null);
              setIsCameraRunning((current) => !current);
            }}
          >
            <Camera className="size-4" />
            {isCameraRunning ? "Stop camera" : "Start camera"}
          </Button>
          <Button
            type="button"
            variant="outline"
            className="rounded-full"
            onClick={() => {
              setCameraError(null);
              setUploadError(null);
              setIsCameraRunning(false);
              setTimeout(() => setIsCameraRunning(true), 80);
            }}
            disabled={!active}
          >
            <RefreshCcw className="size-4" />
            Retry scanner
          </Button>
        </div>
      </div>

      <p className="text-xs leading-6 text-muted-foreground">
        If the member QR is on this same phone, use the screenshot fallback below or open the member pass on another device.
      </p>

      {availableDevices.length > 1 ? (
        <div className="space-y-2">
          <Label>Camera</Label>
          <Select value={selectedDeviceId} onValueChange={setSelectedDeviceId}>
            <SelectTrigger className="w-full sm:max-w-sm">
              <SelectValue placeholder="Select camera" />
            </SelectTrigger>
            <SelectContent>
              {availableDevices.map((device, index) => (
                <SelectItem key={device.deviceId} value={device.deviceId}>
                  {formatCameraLabel(device, index)}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      ) : null}

      {cameraError ? (
        <Alert variant="destructive">
          <ShieldAlert />
          <AlertTitle>Camera scanner unavailable</AlertTitle>
          <AlertDescription>{cameraError}</AlertDescription>
        </Alert>
      ) : null}

      {shouldRenderLiveCamera ? (
        <Scanner
          allowMultiple={false}
          classNames={{
            container:
              "aspect-square w-full overflow-hidden rounded-[1.75rem] border border-border/70 bg-black",
            video: "h-full w-full object-cover",
          }}
          components={{ finder: true }}
          constraints={selectedConstraints}
          formats={["qr_code"]}
          paused={!shouldRenderLiveCamera}
          scanDelay={250}
          onError={(error) => {
            const message = getScannerErrorMessage(error);
            setCameraError(message);
            setIsCameraRunning(false);
            onError?.(message);
          }}
          onScan={(detectedCodes) => {
            const matchingCode = detectedCodes.find((code) => code.rawValue.trim());
            if (!matchingCode?.rawValue) {
              return;
            }

            setCameraError(null);
            setIsCameraRunning(false);
            onDetected(matchingCode.rawValue);
          }}
        />
      ) : (
        <div className="flex aspect-square w-full items-center justify-center rounded-[1.75rem] border border-border/70 bg-muted/30 px-6 text-center">
          <div className="max-w-xs space-y-2">
            <p className="font-medium">
              {active ? "Camera is idle" : "Scanner paused"}
            </p>
            <p className="text-sm leading-6 text-muted-foreground">
              Keep the QR inside the frame and let it fill most of the square for the fastest scan.
            </p>
          </div>
        </div>
      )}

      <div className="space-y-3 rounded-2xl border border-dashed border-border/80 bg-card/70 p-4">
        <div className="space-y-1">
          <p className="font-medium">Screenshot or photo fallback</p>
          <p className="text-sm leading-6 text-muted-foreground">
            Upload a screenshot or a clear photo of the QR code if camera access is blocked or the QR is on this same phone.
          </p>
        </div>
        <div className="space-y-2">
          <Label htmlFor={fileInputId}>QR image</Label>
          <Input
            id={fileInputId}
            type="file"
            accept="image/*"
            onChange={(event) => void handleImageSelection(event)}
          />
        </div>
        {uploadError ? (
          <Alert variant="destructive">
            <Upload />
            <AlertTitle>Image scan failed</AlertTitle>
            <AlertDescription>{uploadError}</AlertDescription>
          </Alert>
        ) : null}
        {isReadingImage ? (
          <p className="text-sm text-muted-foreground">Reading the uploaded image...</p>
        ) : null}
      </div>
    </div>
  );
}
