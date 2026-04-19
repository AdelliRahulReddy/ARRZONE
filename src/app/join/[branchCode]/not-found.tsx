import { SetupCallout } from "@/components/setup-callout";

export default function BranchJoinNotFound() {
  return (
    <main className="container-edge flex min-h-screen items-center justify-center py-16">
      <div className="w-full max-w-2xl">
        <SetupCallout
          title="Branch join link unavailable"
          message="This branch link is missing, inactive, or no longer valid. Ask the store for a fresh branch join link and try again."
          actionHref="/join"
          actionLabel="Open branch enrollment"
        />
      </div>
    </main>
  );
}
