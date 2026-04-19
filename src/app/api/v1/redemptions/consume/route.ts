import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { consumeRedeemToken } from "@/lib/server/loyalty-service";
import {
  getRequestIp,
  requireIdempotencyKey,
} from "@/lib/server/request";
import { redeemTokenConsumeSchema } from "@/lib/validation";

export async function POST(request: Request) {
  try {
    const actor = await requireStaffActor();
    const input = redeemTokenConsumeSchema.parse(await request.json());
    const result = await consumeRedeemToken(
      {
        ...input,
        idempotencyKey: requireIdempotencyKey(request),
      },
      actor,
      {
        tenantId: actor.tenantId,
        staffUserId: actor.staffUserId,
        ip: getRequestIp(request),
      },
    );

    return jsonOk(result, {
      headers: {
        "Cache-Control": "no-store",
      },
    });
  } catch (error) {
    return jsonError(error);
  }
}
