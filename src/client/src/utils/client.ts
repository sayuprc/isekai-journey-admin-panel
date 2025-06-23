import createClient from 'openapi-fetch';
import type { paths } from '../generated/schema';

export const client = createClient<paths>({ baseUrl: import.meta.env.PUBLIC_API_URL });
