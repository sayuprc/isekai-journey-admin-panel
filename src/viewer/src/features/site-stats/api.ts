import { siteStatsServiceGetSiteStats } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import type { SiteStats } from './types.js';

async function get(): Promise<SiteStats> {
  const { data } = await siteStatsServiceGetSiteStats({
    client: apiClient,
  });

  if (!data) {
    throw new Error('siteStatsServiceGetSiteStats returned no data');
  }

  return data;
}

export const siteStatsRepository = {
  get,
};
