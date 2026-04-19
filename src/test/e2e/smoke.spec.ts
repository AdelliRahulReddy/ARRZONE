import { expect, test } from "@playwright/test";

test("overview page renders the loyalty landing surface", async ({ page }) => {
  await page.goto("/");
  await expect(page.getByText("Run a checkout-speed loyalty program")).toBeVisible();
  await expect(page.getByRole("link", { name: "Open Branch Enrollment" })).toBeVisible();
});

test("sign-in route renders the sign-in surface", async ({ page }) => {
  await page.goto("/sign-in");
  await expect(page.getByText(/Sign in|Sign-in is not configured/)).toBeVisible();
});

test("protected staff route redirects to sign-in when unauthenticated", async ({ page }) => {
  await page.goto("/staff");
  await expect(page).toHaveURL(/\/sign-in/);
});
