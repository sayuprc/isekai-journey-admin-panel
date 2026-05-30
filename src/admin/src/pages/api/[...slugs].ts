import type { APIContext } from 'astro';
import { app } from '../../server';
import { CLIENT_IP_HEADER } from '../../server/constants';

const resolveClientAddress = (context: APIContext): string => {
  try {
    return context.clientAddress;
  } catch {
    return context.request.headers.get('x-forwarded-for')?.split(',')[0]?.trim() ?? 'unknown';
  }
};

const handle = (context: APIContext): Response | Promise<Response> => {
  // クライアントが詐称した同名ヘッダーを信頼できる実 IP で上書きしてから Elysia へ渡す
  const headers = new Headers(context.request.headers);
  headers.set(CLIENT_IP_HEADER, resolveClientAddress(context));

  return app.handle(new Request(context.request, { headers }));
};

export const ALL = handle;
