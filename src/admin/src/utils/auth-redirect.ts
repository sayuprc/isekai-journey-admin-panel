export const DEFAULT_AUTH_RETURN_TO = '/song-types';

export const resolveAuthReturnTo = (returnTo: string | null | undefined): string => {
  const path = returnTo?.trim();

  if (!path || !path.startsWith('/') || path.startsWith('//')) {
    return DEFAULT_AUTH_RETURN_TO;
  }

  return path;
};
