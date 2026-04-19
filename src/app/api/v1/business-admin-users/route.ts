import { jsonError, jsonOk } from "@/lib/server/api";
import { requirePlatformActor } from "@/lib/server/auth";
import { createBusinessAdminUser } from "@/lib/server/loyalty-service";
import { createBusinessAdminUserSchema } from "@/lib/validation";

export async function POST(request: Request) {
  try {
    const actor = await requirePlatformActor();
    const input = createBusinessAdminUserSchema.parse(await request.json());
    const businessAdmin = await createBusinessAdminUser(input, actor);
    return jsonOk(businessAdmin);
  } catch (error) {
    return jsonError(error);
  }
}
