import { releaseGroupServiceListReleaseGroups } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import type { ReleaseGroup } from './types.js';

async function all(): Promise<ReleaseGroup[]> {
  const releaseGroups: ReleaseGroup[] = [];
  let cursor: string | undefined;

  while (true) {
    const { data, error, response } = await releaseGroupServiceListReleaseGroups({
      client: apiClient,
      query: { limit: 50, cursor },
    });

    if (!data) {
      throw new Error(`releaseGroupServiceListReleaseGroups failed: HTTP ${response.status} ${JSON.stringify(error)}`);
    }

    releaseGroups.push(...data.releaseGroups);

    if (!data.nextCursor) {
      break;
    }
    cursor = data.nextCursor;
  }

  return releaseGroups;
}

export const releaseGroupRepository = {
  all,
};
