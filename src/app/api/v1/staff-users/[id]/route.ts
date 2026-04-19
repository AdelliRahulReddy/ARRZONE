import { jsonError, jsonOk } from "@/lib/server/api";
import { assertStaffActorRole, requireStaffActor } from "@/lib/server/auth";
import {
  updateStaffUser,
  updateStaffUserStatus,
} from "@/lib/server/loyalty-service";
import { updateStaffUserSchema } from "@/lib/validation";

export async function PATCH(
  request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  try {
    const actor = await requireStaffActor();
    const { id } = await params;
    const input = updateStaffUserSchema.parse(await request.json());
    const nextStatus =
      input.status === "ACTIVE" || input.status === "DISABLED"
        ? input.status
        : undefined;
    const shouldUseStatusOnly =
      nextStatus !== undefined &&
      input.fullName === undefined &&
      input.email === undefined &&
      input.role === undefined &&
      input.primaryBranchId === undefined &&
      input.branchIds === undefined;

    const staffUser = shouldUseStatusOnly
      ? await updateStaffUserStatus(id, { status: nextStatus }, actor)
      : await (() => {
          assertStaffActorRole(actor, "MERCHANT_ADMIN");
          return updateStaffUser(id, input, actor);
        })();
    return jsonOk(staffUser);
  } catch (error) {
    return jsonError(error);
  }
}
