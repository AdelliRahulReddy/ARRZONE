import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { updateStaffUserStatus } from "@/lib/server/loyalty-service";
import { updateStaffUserStatusSchema } from "@/lib/validation";

export async function PATCH(
  request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  try {
    const actor = await requireStaffActor();
    const { id } = await params;
    const input = updateStaffUserStatusSchema.parse(await request.json());
    const staffUser = await updateStaffUserStatus(id, input, actor);
    return jsonOk(staffUser);
  } catch (error) {
    return jsonError(error);
  }
}
