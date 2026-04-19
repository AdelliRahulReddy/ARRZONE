import { jsonError, jsonOk } from "@/lib/server/api";
import { requirePlatformActor } from "@/lib/server/auth";
import { createPlatformAdminUser } from "@/lib/server/loyalty-service";
import { createPlatformAdminUserSchema } from "@/lib/validation";

export async function POST(request: Request) {
  try {
    await requirePlatformActor();
    const input = createPlatformAdminUserSchema.parse(await request.json());
    const platformAdmin = await createPlatformAdminUser(input);
    return jsonOk(platformAdmin);
  } catch (error) {
    return jsonError(error);
  }
}
