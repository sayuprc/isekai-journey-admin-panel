import { treaty } from '@elysiajs/eden';
import baseClient from 'openapi-fetch';
import type { paths } from '../generated/types.gen';
import type { App } from '../server';

const getCookie = (name: string): string | undefined => {
  // SSR 時は取れないので undefined にする
  if (typeof document === 'undefined') {
    return undefined;
  }

  const value = document.cookie
    .split('; ')
    .find(row => row.startsWith(`${name}=`))
    ?.split('=')[1];

  return value ? decodeURIComponent(value) : undefined;
};

export const client = treaty<App>(import.meta.env.PUBLIC_APP_URL, {
  headers: () => {
    const csrfToken = getCookie('csrf');
    return csrfToken ? { 'x-csrf-token': csrfToken } : {};
  },
});

export const createClient = (request: Request) => {
  return baseClient<paths>({
    baseUrl: import.meta.env.PUBLIC_API_URL,
    headers: {
      cookie: request.headers.get('cookie') ?? '',
    },
  });
};
