import { jsonError, jsonOk } from "@/lib/server/api";
import { requireStaffActor } from "@/lib/server/auth";
import { getExceptionsReport } from "@/lib/server/loyalty-service";

export async function GET() {
  try {
    const actor = await requireStaffActor("MERCHANT_ADMIN");
    const report = await getExceptionsReport(actor);
    return jsonOk(report);
  } catch (error) {
    return jsonError(error);
  }
}
