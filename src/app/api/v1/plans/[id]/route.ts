import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { updatePlan } from "@/lib/server/loyalty-service";
import { updatePlanSchema } from "@/lib/validation";

export async function PATCH(
  request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  try {
    const actor = await requireStaffActor("MERCHANT_ADMIN");
    const { id } = await params;
    const input = updatePlanSchema.parse(await request.json());
    const plan = await updatePlan(id, input, actor);
    return jsonOk(plan);
  } catch (error) {
    return jsonError(error);
  }
}
