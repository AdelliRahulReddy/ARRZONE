import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { updateBranch } from "@/lib/server/loyalty-service";
import { updateBranchSchema } from "@/lib/validation";

export async function PATCH(
  request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  try {
    const actor = await requireStaffActor("MERCHANT_ADMIN");
    const { id } = await params;
    const input = updateBranchSchema.parse(await request.json());
    const branch = await updateBranch(id, input, actor);
    return jsonOk(branch);
  } catch (error) {
    return jsonError(error);
  }
}
