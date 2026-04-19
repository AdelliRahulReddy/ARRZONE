import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { searchMembershipsByPhone } from "@/lib/server/loyalty-service";
import { getRequestIp } from "@/lib/server/request";

export async function GET(request: Request) {
  try {
    const actor = await requireStaffActor();
    const { searchParams } = new URL(request.url);
    const phone = searchParams.get("phone");
    if (!phone) {
      throw new Error("phone query parameter is required.");
    }

    const memberships = await searchMembershipsByPhone(phone, actor, {
      tenantId: actor.tenantId,
      staffUserId: actor.staffUserId,
      ip: getRequestIp(request),
    });
    return jsonOk(memberships, {
      headers: {
        "Cache-Control": "no-store",
      },
    });
  } catch (error) {
    return jsonError(error);
  }
}
