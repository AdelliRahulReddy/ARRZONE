import QRCode from "qrcode";

export async function toQrCodeDataUrl(value: string) {
  return QRCode.toDataURL(value, {
    errorCorrectionLevel: "M",
    margin: 1,
    scale: 8,
  });
}
