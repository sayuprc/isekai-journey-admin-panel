import { CLIENT_IP_HEADER } from './constants';
import { ApiError } from './errors';
import { redis } from './redis';

type RateLimitConfig = {
  limit: number;
  windowSeconds: number;
};

const createRateLimitKey = (scope: string, clientIp: string): string => {
  return `rate-limit:auth:${scope}:${clientIp}`;
};

const resolveClientIp = (request: Request): string => {
  return request.headers.get(CLIENT_IP_HEADER) ?? 'unknown';
};

/**
 * クライアント実 IP 単位の固定ウィンドウレート制限。
 *
 * 上限を超えた場合は 429 を投げる。Redis の INCR + EXPIRE で実装し、
 * 複数インスタンス構成でもカウンタを共有する。
 */
export const enforceAuthRateLimit = async (request: Request, scope: string, config: RateLimitConfig): Promise<void> => {
  const key = createRateLimitKey(scope, resolveClientIp(request));
  const count = await redis.incr(key);

  if (count === 1) {
    await redis.expire(key, config.windowSeconds);
  }

  if (count > config.limit) {
    throw new ApiError(429, {});
  }
};
