import { jsonError, jsonOk } from "@/lib/server/api";
import { requirePlatformActor } from "@/lib/server/auth";
import { updateBusinessAdminStatus } from "@/lib/server/loyalty-service";
import { updateStaffUserStatusSchema } from "@/lib/validation";

export async function PATCH(
  request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  try {
    const actor = await requirePlatformActor();
    const { id } = await params;
    const input = updateStaffUserStatusSchema.parse(await request.json());
    const businessAdmin = await updateBusinessAdminStatus(id, input, actor);
    return jsonOk(businessAdmin);
  } catch (error) {
    return jsonError(error);
  }
}
