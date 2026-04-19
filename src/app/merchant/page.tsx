import { redirect } from "next/navigation";
import { BUSINESS_ADMIN_ROUTE } from "@/lib/auth/constants";

export default function MerchantRedirectPage() {
  redirect(BUSINESS_ADMIN_ROUTE);
}
