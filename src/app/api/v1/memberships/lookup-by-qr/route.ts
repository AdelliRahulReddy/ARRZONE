import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { lookupMembershipByPassPayload } from "@/lib/server/loyalty-service";
import { getRequestIp } from "@/lib/server/request";
import { lookupByQrSchema } from "@/lib/validation";

export async function POST(request: Request) {
  try {
    const actor = await requireStaffActor();
    const input = lookupByQrSchema.parse(await request.json());
    const membership = await lookupMembershipByPassPayload(
      input.qrPayload,
      actor,
      {
        tenantId: actor.tenantId,
        staffUserId: actor.staffUserId,
        ip: getRequestIp(request),
      },
    );
    return jsonOk(membership, {
      headers: {
        "Cache-Control": "no-store",
      },
    });
  } catch (error) {
    return jsonError(error);
  }
}
