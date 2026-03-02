export function resolveMediaUrl(path) {
  if (!path) return "";
  if (/^https?:\/\//i.test(path) || path.startsWith("data:")) return path;

  const rawApiUrl = import.meta.env.VITE_API_URL || "";
  try {
    const apiUrl = new URL(rawApiUrl, window.location.origin);
    const normalizedPath = path.startsWith("/") ? path : `/${path}`;
    return `${apiUrl.origin}${normalizedPath}`;
  } catch {
    return path;
  }
}

