import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { createPlan } from "@/lib/server/loyalty-service";
import { createPlanSchema } from "@/lib/validation";

export async function POST(request: Request) {
  try {
    const actor = await requireStaffActor("MERCHANT_ADMIN");
    const input = createPlanSchema.parse(await request.json());
    const plan = await createPlan(input, actor);
    return jsonOk(plan);
  } catch (error) {
    return jsonError(error);
  }
}
