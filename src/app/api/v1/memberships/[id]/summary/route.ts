import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { getMembershipSnapshotForStaff } from "@/lib/server/loyalty-service";

export async function GET(
  _request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  try {
    const actor = await requireStaffActor();
    const { id } = await params;
    const membership = await getMembershipSnapshotForStaff(id, actor);
    return jsonOk(membership, {
      headers: {
        "Cache-Control": "no-store",
      },
    });
  } catch (error) {
    return jsonError(error);
  }
}
