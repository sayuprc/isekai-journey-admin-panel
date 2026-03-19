import { createClient, createConfig } from '../generated/client';
import type { Credential } from './types';

const apiUrl = import.meta.env.API_URL;

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
