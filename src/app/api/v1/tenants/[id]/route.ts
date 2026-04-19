import { jsonError, jsonOk } from "@/lib/server/api";
import { requirePlatformActor } from "@/lib/server/auth";
import { updateTenant } from "@/lib/server/loyalty-service";
import { updateTenantSchema } from "@/lib/validation";

export async function PATCH(
  request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  try {
    await requirePlatformActor();
    const { id } = await params;
    const input = updateTenantSchema.parse(await request.json());
    const tenant = await updateTenant(id, input);
    return jsonOk(tenant);
  } catch (error) {
    return jsonError(error);
  }
}
