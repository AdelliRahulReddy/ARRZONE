import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { addPurchase } from "@/lib/server/loyalty-service";
import { requireIdempotencyKey } from "@/lib/server/request";
import { purchaseAddSchema } from "@/lib/validation";

export async function POST(
  request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  try {
    const actor = await requireStaffActor();
    const { id } = await params;
    const input = purchaseAddSchema.parse(await request.json());
    const result = await addPurchase(
      id,
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
