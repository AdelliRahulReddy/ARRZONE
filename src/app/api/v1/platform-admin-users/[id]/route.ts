import { jsonError, jsonOk } from "@/lib/server/api";
import { requirePlatformActor } from "@/lib/server/auth";
import { updatePlatformAdminUser } from "@/lib/server/loyalty-service";
import { updatePlatformAdminUserSchema } from "@/lib/validation";

export async function PATCH(
  request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  try {
    const actor = await requirePlatformActor();
    const { id } = await params;
    const input = updatePlatformAdminUserSchema.parse(await request.json());
    const platformAdmin = await updatePlatformAdminUser(id, input, actor);
    return jsonOk(platformAdmin);
  } catch (error) {
    return jsonError(error);
  }
}
