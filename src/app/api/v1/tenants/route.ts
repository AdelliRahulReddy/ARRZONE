import { jsonError, jsonOk } from "@/lib/server/api";
import { requirePlatformActor } from "@/lib/server/auth";
import { createTenant } from "@/lib/server/loyalty-service";
import { createTenantSchema } from "@/lib/validation";

export async function POST(request: Request) {
  try {
    await requirePlatformActor();
    const input = createTenantSchema.parse(await request.json());
    const tenant = await createTenant(input);
    return jsonOk(tenant);
  } catch (error) {
    return jsonError(error);
  }
}
