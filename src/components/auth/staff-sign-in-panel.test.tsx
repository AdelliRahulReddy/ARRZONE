import { render, screen } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { StaffSignInPanel } from "@/components/auth/staff-sign-in-panel";

const { useRouterMock, usePathnameMock } = vi.hoisted(() => ({
  useRouterMock: vi.fn(),
  usePathnameMock: vi.fn(),
}));

vi.mock("next/navigation", () => ({
  useRouter: useRouterMock,
  usePathname: usePathnameMock,
}));

vi.mock("@/lib/firebase/client", () => ({
  getFirebaseClientAuth: vi.fn(),
  createGoogleAuthProvider: vi.fn(),
}));

describe("staff sign-in panel", () => {
  beforeEach(() => {
    useRouterMock.mockReturnValue({
      replace: vi.fn(),
      refresh: vi.fn(),
    });
    usePathnameMock.mockReturnValue("/platform");
  });

  it("renders the surface-specific sign-in affordances", () => {
    render(
      <StaffSignInPanel
        surface="platform"
        redirectTo="/platform"
      />,
    );

    expect(screen.getByText("Platform Admin Sign-In")).toBeInTheDocument();
    expect(screen.getByText("Platform-Wide Access")).toBeInTheDocument();
    expect(
      screen.getByRole("button", { name: "Continue with Password" }),
    ).toBeInTheDocument();
    expect(
      screen.getByRole("button", { name: "Continue with Google" }),
    ).toBeInTheDocument();
    expect(screen.getByLabelText("Email")).toBeInTheDocument();
    expect(screen.getByLabelText("Password")).toBeInTheDocument();
    expect(screen.getByText("/platform")).toBeInTheDocument();
  });
});
