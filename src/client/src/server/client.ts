import createClient from 'openapi-fetch';
import type { paths } from '../generated/schema';
import type { Credential } from './types';

const apiUrl = import.meta.env.API_URL;

/** 認証付き API クライアントを生成する */
export const createAuthClient = (credential: Credential) => {
  return createClient<paths>({
    baseUrl: apiUrl,
    headers: {
      Authorization: `Bearer ${credential.accessToken}`,
    },
  });
};

/** 認証不要の API クライアント */
export const apiClient = createClient<paths>({
  baseUrl: apiUrl,
});
