import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { getStaffActivityReport } from "@/lib/server/loyalty-service";

export async function GET() {
  try {
    const actor = await requireStaffActor("MERCHANT_ADMIN");
    const report = await getStaffActivityReport(actor);
    return jsonOk(report);
  } catch (error) {
    return jsonError(error);
  }
}
