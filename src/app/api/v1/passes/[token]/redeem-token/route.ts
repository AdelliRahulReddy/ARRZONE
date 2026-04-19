import { jsonError, jsonOk } from "@/lib/server/api";
import { issueRedeemToken } from "@/lib/server/loyalty-service";
import { getRequestIp } from "@/lib/server/request";

export async function POST(
  request: Request,
  { params }: { params: Promise<{ token: string }> },
) {
  try {
    const { token } = await params;
    const payload = await issueRedeemToken(token, {
      ip: getRequestIp(request),
    });

    return jsonOk(payload, {
      headers: {
        "Cache-Control": "no-store",
      },
    });
  } catch (error) {
    return jsonError(error);
  }
}
