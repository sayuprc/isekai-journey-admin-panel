import { describe, expect, it } from 'bun:test';
import { createClient, createConfig } from '../generated/client';
import { releaseServiceUploadJacketArt } from '../generated/sdk.gen';

describe('releaseServiceUploadJacketArt', () => {
  it('bodySerializer を無効化すると raw image bytes を送信する', async () => {
    const bytes = Uint8Array.from([0x89, 0x50, 0x4e, 0x47]);
    let sentBody: Uint8Array | undefined;
    let sentContentType: string | null = null;
    const fetcher = Object.assign(
      async (input: RequestInfo | URL, init?: RequestInit) => {
        const request = input instanceof Request ? input : new Request(input, init);

        sentBody = new Uint8Array(await request.arrayBuffer());
        sentContentType = request.headers.get('content-type');

        return new Response(JSON.stringify({ jacketArtUrl: 'http://assets.local/jacket.png' }), {
          status: 200,
          headers: { 'content-type': 'application/json' },
        });
      },
      {
        preconnect: () => {},
      },
    ) as typeof fetch;

    const client = createClient(
      createConfig({
        baseUrl: 'http://api.local',
        fetch: fetcher,
      }),
    );

    await releaseServiceUploadJacketArt({
      client,
      body: bytes,
      bodySerializer: null,
      headers: {
        'Content-Type': 'image/png',
      },
    });

    expect(String(sentContentType)).toBe('image/png');
    expect(Array.from(sentBody ?? [])).toEqual(Array.from(bytes));
  });
});
