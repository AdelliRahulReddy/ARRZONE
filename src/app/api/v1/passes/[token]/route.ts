import { jsonError, jsonOk } from "@/lib/server/api";
import { getPassSnapshot } from "@/lib/server/loyalty-service";

export async function GET(
  _request: Request,
  { params }: { params: Promise<{ token: string }> },
) {
  try {
    const { token } = await params;
    const snapshot = await getPassSnapshot(token);
    return jsonOk(snapshot, {
      headers: {
        "Cache-Control": "no-store",
      },
    });
  } catch (error) {
    return jsonError(error);
  }
}
