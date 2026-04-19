import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { createStaffUser } from "@/lib/server/loyalty-service";
import { createStaffUserSchema } from "@/lib/validation";

export async function POST(request: Request) {
  try {
    const actor = await requireStaffActor("MERCHANT_ADMIN");
    const input = createStaffUserSchema.parse(await request.json());
    const staffUser = await createStaffUser(input, actor);
    return jsonOk(staffUser);
  } catch (error) {
    return jsonError(error);
  }
}
