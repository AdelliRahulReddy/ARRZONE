import { jsonError, jsonOk } from "@/lib/server/api";
import { createEnrollment } from "@/lib/server/loyalty-service";
import { getRequestIp } from "@/lib/server/request";
import { enrollmentInputSchema } from "@/lib/validation";

export async function POST(request: Request) {
  try {
    const input = enrollmentInputSchema.parse(await request.json());
    const enrollment = await createEnrollment({
      ...input,
      ip: getRequestIp(request),
    });
    return jsonOk(enrollment, {
      headers: {
        "Cache-Control": "no-store",
      },
    });
  } catch (error) {
    return jsonError(error);
  }
}
