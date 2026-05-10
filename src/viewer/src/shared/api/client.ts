import { createClient } from '../../generated/client/client.gen.js';
import { createConfig } from '../../generated/client/utils.gen.js';

export const apiClient = createClient(
  createConfig({
    baseUrl: import.meta.env.API_URL,
  }),
);
