import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { createBranch } from "@/lib/server/loyalty-service";
import { createBranchSchema } from "@/lib/validation";

export async function POST(request: Request) {
  try {
    const actor = await requireStaffActor("MERCHANT_ADMIN");
    const input = createBranchSchema.parse(await request.json());
    const branch = await createBranch(input, actor);
    return jsonOk(branch);
  } catch (error) {
    return jsonError(error);
  }
}
