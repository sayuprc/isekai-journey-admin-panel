import { treaty } from '@elysiajs/eden';
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
