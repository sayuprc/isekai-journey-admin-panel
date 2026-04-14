import { Redis } from '@upstash/redis';

export const redis = new Redis({
  url: import.meta.env.CACHE_URL,
  token: import.meta.env.CACHE_TOKEN,
});
