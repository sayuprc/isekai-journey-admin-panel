import baseClient from 'openapi-fetch';
import type { paths } from '../generated/schema';

export const client = baseClient<paths>({
  baseUrl: import.meta.env.PUBLIC_API_URL,
});

export const createClient = (request: Request) => {
  return baseClient<paths>({
    baseUrl: import.meta.env.PUBLIC_API_URL,
    headers: {
      cookie: request.headers.get('cookie') ?? '',
    },
  });
};
