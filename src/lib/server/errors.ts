export class AppError extends Error {
  constructor(
    message: string,
    public readonly status: number,
    public readonly code: string,
    public readonly details?: Record<string, unknown>,
  ) {
    super(message);
    this.name = "AppError";
  }
}

export function invariant(
  condition: unknown,
  message: string,
  status = 400,
  code = "BAD_REQUEST",
): asserts condition {
  if (!condition) {
    throw new AppError(message, status, code);
  }
}
