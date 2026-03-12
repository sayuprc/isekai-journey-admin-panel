import createClient from 'openapi-fetch';
import type { paths } from '../generated/types.gen';
import type { Credential } from './types';

const apiUrl = import.meta.env.API_URL;

export const createAuthClient = (credential: Credential) => {
  return createClient<paths>({
    baseUrl: apiUrl,
    headers: {
      Authorization: `Bearer ${credential.accessToken}`,
    },
  });
};

export const client = createClient<paths>({
  baseUrl: apiUrl,
});
