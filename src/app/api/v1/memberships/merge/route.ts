import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { mergeMemberships } from "@/lib/server/loyalty-service";
import { requireIdempotencyKey } from "@/lib/server/request";
import { mergeMembershipsSchema } from "@/lib/validation";

export async function POST(request: Request) {
  try {
    const actor = await requireStaffActor();
    const input = mergeMembershipsSchema.parse(await request.json());
    const result = await mergeMemberships(
      {
        ...input,
        idempotencyKey: requireIdempotencyKey(request),
      },
      actor,
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
