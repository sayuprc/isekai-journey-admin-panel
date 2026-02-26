import { Redis } from '@upstash/redis';
import { Elysia, t } from 'elysia';
import createClient from 'openapi-fetch';
import type { paths, components } from '../generated/schema';

const cc = (credential: Credential) => {
  return createClient<paths>({
    baseUrl: import.meta.env.API_URL,
    headers: {
      Authorization: `Bearer ${credential.accessToken}`,
    },
  });
};

const client = createClient<paths>({
  baseUrl: import.meta.env.API_URL,
});

const redis = new Redis({
  url: import.meta.env.CACHE_URL,
  token: import.meta.env.CACHE_TOKEN,
});

type Credential = {
  accessToken: string;
  refreshToken: string;
  csrfToken: string;
};

const withAuth = (prefix: string) => {
  return new Elysia({ prefix: prefix })
    .guard({
      headers: t.Object({
        // treaty で必ず x-csrf-token を取ってくるようにしているが、CSR なところで明示的に書かないと波線が出るのでいったん optional にしている
        'x-csrf-token': t.Optional(t.String()),
        // CSRF では自動送信されるため、tsx では指定してないが波線が出るのでいったん optional にしている
        'cookie': t.Optional(t.String()),
      }),
    })
    .resolve(async ({ headers, cookie: { session } }) => {
    // TODO 適切なエラーハンドリング
      if (!session?.value) {
        throw new Error('session cookie がない');
      }

      // TODO 適切なエラーハンドリング
      if (!headers['x-csrf-token']) {
        throw new Error('X-CSRF-TOKEN がない');
      }

      const credential = await redis.get<Credential>(`session:${session.value}`);

      // TODO 適切なエラーハンドリング
      if (!credential) {
        throw new Error('Credential がおかしい');
      }

      // TODO 適切なエラーハンドリング
      if (credential?.csrfToken !== headers['x-csrf-token']) {
        throw new Error('Redis とヘッダーの値が合致しない');
      }

      return {
        credential: credential,
      };
    });
};

const adminUsers = withAuth('/admin-users')
  .get('/', async ({ credential }) => {
    const { data } = await cc(credential).GET('/admin-users');

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  });

const performers = withAuth('/performers')
  .get('/', async ({ credential }) => {
    const { data } = await cc(credential).GET('/performers');

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  })
  .get('/:performerId', async ({ params: { performerId }, credential }) => {
    const { data } = await cc(credential).GET('/performers/{performerId}', {
      params: {
        path: {
          performerId: performerId,
        },
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  }, {
    params: t.Object({
      performerId: t.String(),
    }),
  })
  .post('/', async ({ body, credential }) => {
    const { data } = await cc(credential).POST('/performers', {
      body: {
        name: body.name,
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  }, {
    body: t.Object({
      name: t.String(),
    }),
  })
  .put('/:performerId', async ({ params: { performerId }, body, credential }) => {
    const { data } = await cc(credential).PUT('/performers/{performerId}', {
      params: {
        path: {
          performerId: performerId,
        },
      },
      body: {
        name: body.name,
        orderNo: body.orderNo,
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  }, {
    params: t.Object({
      performerId: t.String(),
    }),
    body: t.Object({
      name: t.String(),
      orderNo: t.Number(),
    }),
  })
  .delete('/:performerId', async ({ params: { performerId }, credential }) => {
    const { data } = await cc(credential).DELETE('/performers/{performerId}', {
      params: {
        path: {
          performerId: performerId,
        },
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  }, {
    params: t.Object({
      performerId: t.String(),
    }),
  });

const creators = withAuth('/creators')
  .get('/', async ({ credential }) => {
    const { data } = await cc(credential).GET('/creators');

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  })
  .get('/:creatorId', async ({ params: { creatorId }, credential }) => {
    const { data } = await cc(credential).GET('/creators/{creatorId}', {
      params: {
        path: {
          creatorId: creatorId,
        },
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  }, {
    params: t.Object({
      creatorId: t.String(),
    }),
  })
  .post('/', async ({ body: { name }, credential }) => {
    const { data } = await cc(credential).POST('/creators', {
      body: {
        name: name,
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  }, {
    body: t.Object({
      name: t.String(),
    }),
  })
  .put('/:creatorId', async ({ params: { creatorId }, body: { name }, credential }) => {
    const { data } = await cc(credential).PUT('/creators/{creatorId}', {
      params: {
        path: {
          creatorId: creatorId,
        },
      },
      body: {
        name: name,
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  }, {
    params: t.Object({
      creatorId: t.String(),
    }),
    body: t.Object({
      name: t.String(),
    }),
  })
  .delete('/:creatorId', async ({ params: { creatorId }, credential }) => {
    const { data, error } = await cc(credential).DELETE('/creators/{creatorId}', {
      params: {
        path: {
          creatorId: creatorId,
        },
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      console.error(`${creatorId} を削除できなかった`);
      console.error(error?.message);
      throw new Error('エラー');
    }

    return data;
  }, {
    params: t.Object({
      creatorId: t.String(),
    }),
  });

const songs = withAuth('/songs')
  .get('/', async ({ credential }) => {
    const { data } = await cc(credential).GET('/songs');

    // TODO 適切なエラーハンドリング
    if (!data) {
      console.error('全楽曲取得エラー');
      throw new Error('エラー');
    }

    return data;
  })
  .get('/:songId', async ({ params: { songId }, credential }) => {
    const { data } = await cc(credential).GET('/songs/{songId}', {
      params: {
        path: {
          songId: songId,
        },
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  }, {
    params: t.Object({
      songId: t.String(),
    }),
  })
  .post('/', async ({ body: { title, description, songTypeValue, arrangers, composers, lyricists }, credential }) => {
    const { data } = await cc(credential).POST('/songs', {
      body: {
        title: title,
        description: description,
        songTypeValue: songTypeValue as components['schemas']['SongTypeValue'],
        arrangers: arrangers,
        composers: composers,
        lyricists: lyricists,
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  }, {
    body: t.Object({
      title: t.String(),
      description: t.String(),
      songTypeValue: t.Number(),
      arrangers: t.Array(
        t.Object({
          creatorId: t.String(),
        }),
      ),
      composers: t.Array(
        t.Object({
          creatorId: t.String(),
        }),
      ),
      lyricists: t.Array(
        t.Object({
          creatorId: t.String(),
        }),
      ),
    }),
  })
  .put('/:songId', async ({ params: { songId }, body: { title, description, songTypeValue, orderNo, arrangers, composers, lyricists }, credential }) => {
    const { data } = await cc(credential).PUT('/songs/{songId}', {
      params: {
        path: {
          songId: songId,
        },
      },
      body: {
        title: title,
        description: description,
        songTypeValue: songTypeValue as components['schemas']['SongTypeValue'],
        orderNo: orderNo,
        arrangers: arrangers,
        composers: composers,
        lyricists: lyricists,
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    return data;
  }, {
    params: t.Object({
      songId: t.String(),
    }),
    body: t.Object({
      title: t.String(),
      description: t.String(),
      songTypeValue: t.Number(),
      orderNo: t.Number(),
      arrangers: t.Array(
        t.Object({
          creatorId: t.String(),
        }),
      ),
      composers: t.Array(
        t.Object({
          creatorId: t.String(),
        }),
      ),
      lyricists: t.Array(
        t.Object({
          creatorId: t.String(),
        }),
      ),
    }),
  })
  .delete('/:songId', async ({ params: { songId }, credential }) => {
    const { data, error } = await cc(credential).DELETE('/songs/{songId}', {
      params: {
        path: {
          songId: songId,
        },
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      console.error(`${songId} を削除できなかった`);
      console.error(error?.message);
      throw new Error('エラー');
    }

    return data;
  }, {
    params: t.Object({
      songId: t.String(),
    }),
  });

const songTypes = withAuth('/song-types')
  .get('/', async ({ credential }) => {
    const { data } = await cc(credential).GET('/song-types');

    // TODO 適切なエラーハンドリング
    if (!data) {
      console.error('API エラー');
      throw new Error('エラー');
    }

    return data;
  });

const auth = new Elysia({ prefix: '/auth' })
  .post('/login', async ({ body, cookie: { session, csrf } }) => {
    const { data } = await client.POST('/auth/login', {
      body: {
        email: body.email,
        password: body.password,
      },
    });

    // TODO 適切なエラーハンドリング
    if (!data) {
      throw new Error('エラー');
    }

    const sessionId = crypto.randomUUID();

    const csrfToken = crypto.randomUUID();

    await redis.set(
      `session:${sessionId}`,
      { ...data, csrfToken: csrfToken },
      // TODO 調整する
      { ex: 60 * 60 * 24 },
    );

    await session?.set({
      value: sessionId,
      httpOnly: true,
      secure: true,
      sameSite: 'strict',
      path: '/',
      // TODO 調整
      maxAge: 60 * 60 * 24,
    });

    await csrf?.set({
      value: csrfToken,
      httpOnly: false,
      secure: true,
      sameSite: 'strict',
      path: '/',
      // TODO 調整
      maxAge: 60 * 60 * 24,
    });
  }, {
    body: t.Object({
      email: t.String(),
      password: t.String(),
    }),
  });

export const app = new Elysia({ prefix: '/api' })
  .use(auth)
  .use(adminUsers)
  .use(creators)
  .use(performers)
  .use(songTypes)
  .use(songs);

export type App = typeof app;
