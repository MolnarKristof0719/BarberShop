export function resolveMediaUrl(path) {
  if (!path) return "";

  const normalizedInput = String(path).replace(/\\/g, "/").trim();
  if (!normalizedInput) return "";
  if (/^https?:\/\//i.test(normalizedInput) || normalizedInput.startsWith("data:")) {
    return normalizedInput;
  }

  const rawApiUrl = import.meta.env.VITE_API_URL || "";
  try {
    const apiUrl = new URL(rawApiUrl, window.location.origin);
    const normalizedPath = normalizedInput.startsWith("/")
      ? normalizedInput
      : `/${normalizedInput}`;
    return `${apiUrl.origin}${normalizedPath}`;
  } catch {
    return normalizedInput;
  }
}

