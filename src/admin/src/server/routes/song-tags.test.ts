import { beforeEach, describe, expect, it, mock } from 'bun:test';
import Elysia from 'elysia';

const songTagServiceListSongTags = mock();
const createAuthClient = mock();

mock.module('../../generated', () => ({
  songTagServiceListSongTags,
}));

mock.module('../client', () => ({
  createAuthClient,
}));

mock.module('../errors', () => ({
  resolveApiResponse: <T>(result: { data?: T; error?: unknown; response: Response }): T => {
    if (result.response.ok) {
      return result.data as T;
    }

    throw new Error(`API Error: ${result.response.status}`);
  },
}));

mock.module('../middleware', () => ({
  authGuard: new Elysia({ name: 'authGuard' })
    .resolve(() => ({
      credential: {
        accessToken: 'access-token',
        csrfToken: 'csrf-token',
      },
    }))
    .as('scoped'),
}));

describe('songTags route', () => {
  beforeEach(() => {
    songTagServiceListSongTags.mockReset();
    createAuthClient.mockReset();
  });

  it('returns song tags via BFF', async () => {
    const client = { marker: 'client' };
    const payload = {
      tags: [
        {
          songTagId: '2f4ae940-2baa-42ad-ad13-07ed1135e97b',
          name: '先攻',
          orderNo: 10,
        },
      ],
    };

    createAuthClient.mockReturnValue(client);
    songTagServiceListSongTags.mockResolvedValue({
      data: payload,
      response: new Response(null, { status: 200 }),
    });

    const { songTags } = await import('./song-tags');
    const response = await songTags.handle(new Request('http://localhost/song-tags'));

    expect(response.status).toBe(200);
    expect(await response.json()).toEqual(payload);
    expect(createAuthClient).toHaveBeenCalledWith({
      accessToken: 'access-token',
      csrfToken: 'csrf-token',
    });
    expect(songTagServiceListSongTags).toHaveBeenCalledWith({ client });
  });
});
