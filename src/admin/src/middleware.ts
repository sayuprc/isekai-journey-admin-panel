import type { APIContext, MiddlewareHandler, MiddlewareNext } from 'astro';
import { defineMiddleware } from 'astro:middleware';
import { getSessionCredential } from './server/session';
import { createLoginRedirectPath, isAuthRequiredPath } from './utils/auth-redirect';

type AdminMiddlewareProps = Record<string, unknown>;
type AdminMiddlewareParams = Record<string, string | undefined>;
type AdminMiddlewareContext = APIContext<AdminMiddlewareProps, AdminMiddlewareParams>;
type AstroCookies = AdminMiddlewareContext['cookies'];

const AUTH_COOKIE_NAMES = ['session', 'csrf'] as const satisfies readonly string[];

const clearAuthCookies = (cookies: AstroCookies): void => {
  for (const cookieName of AUTH_COOKIE_NAMES) {
    cookies.delete(cookieName, { path: '/' });
  }
};

const requireAuth: MiddlewareHandler = async (
  context: AdminMiddlewareContext,
  next: MiddlewareNext,
): Promise<Response> => {
  const { pathname, search } = context.url;

  if (!isAuthRequiredPath(pathname)) {
    return next();
  }

  const sessionId = context.cookies.get('session')?.value;
  const credential = sessionId ? await getSessionCredential(sessionId) : null;

  if (credential) {
    return next();
  }

  clearAuthCookies(context.cookies);

  return context.redirect(createLoginRedirectPath(`${pathname}${search}`));
};

export const onRequest: MiddlewareHandler = defineMiddleware(requireAuth);
