import { createClient } from '../../generated/client/client.gen.js';
import { createConfig } from '../../generated/client/utils.gen.js';

const baseUrl = import.meta.env.API_URL + '/v1';

export const apiClient = createClient(
  createConfig({
    baseUrl,
  }),
);
