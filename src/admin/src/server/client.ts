import { API_URL } from 'astro:env/server';
import { createClient, createConfig } from '../generated/client';
import type { Client } from '../generated/client';
import { authenticateServiceRefresh } from '../generated/index';
import { ApiError, resolveApiResponse } from './errors';
import type { AuthSession, Credential } from './types';

const apiUrl = API_URL + '/admin/v1';

export const createAuthClient = (credential: Credential) => {
  return createClient(
    createConfig({
      baseUrl: apiUrl,
      headers: {
        Authorization: `Bearer ${credential.accessToken}`,
      },
    }),
  );
};

export const client = createClient(
  createConfig({
    baseUrl: apiUrl,
  }),
);

type AuthRetryDeps<TClient> = {
  createClient: (credential: Credential) => TClient;
  refreshAccessToken: (
    credential: Credential,
  ) => Promise<Pick<Credential, 'accessToken' | 'refreshTokenId' | 'refreshToken'>>;
};

const refreshAccessToken = async (
  credential: Credential,
): Promise<Pick<Credential, 'accessToken' | 'refreshTokenId' | 'refreshToken'>> => {
  const result = await authenticateServiceRefresh({
    client,
    body: {
      refreshTokenId: credential.refreshTokenId,
      refreshToken: credential.refreshToken,
    },
  });

  const data = resolveApiResponse(result);

  return {
    accessToken: data.accessToken,
    refreshTokenId: data.refreshTokenId,
    refreshToken: data.refreshToken,
  };
};

export const createWithAuthRetry = <TClient>(deps: AuthRetryDeps<TClient>) => {
  return async <T>(authSession: AuthSession, callback: (client: TClient) => Promise<T>): Promise<T> => {
    try {
      return await callback(deps.createClient(authSession.credential));
    } catch (error) {
      if (!(error instanceof ApiError) || error.status !== 401) {
        throw error;
      }
    }

    const refreshLockAcquired = await authSession.acquireRefreshLock();

    if (!refreshLockAcquired) {
      const refreshedCredential = await authSession.waitForCredentialUpdate(authSession.credential.accessToken);

      if (!refreshedCredential) {
        throw new ApiError(401, {});
      }

      return await callback(deps.createClient(refreshedCredential));
    }

    try {
      const nextCredentialTokens = await deps.refreshAccessToken(authSession.credential);
      const refreshedCredential = await authSession.storeCredential({
        ...authSession.credential,
        ...nextCredentialTokens,
      });

      if (!refreshedCredential) {
        throw new ApiError(401, {});
      }

      return await callback(deps.createClient(refreshedCredential));
    } catch (error) {
      if (error instanceof ApiError && error.status === 401) {
        await authSession.clearCredential();
      }

      throw error;
    } finally {
      await authSession.releaseRefreshLock();
    }
  };
};

export const withAuthRetry = createWithAuthRetry<Client>({
  createClient: createAuthClient,
  refreshAccessToken,
});
