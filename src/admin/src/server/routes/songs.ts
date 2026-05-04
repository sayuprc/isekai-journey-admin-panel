import { Elysia, t } from 'elysia';
import {
  personServiceListPersons,
  songServiceCreateSong,
  songServiceDeleteSong,
  songServiceGetSong,
  songServiceSearchSongs,
  songServiceUpdateSong,
  songTagServiceListSongTags,
  songTypeServiceListSongTypes,
} from '../../generated';
import type { PerPage, SongSearchSortBy, SongTypeValue, SortOrder } from '../../generated';
import { withAuthRetry } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const SongTypeValueSchema = t.Union([t.Literal(1), t.Literal(2)]);
const NullableStringSchema = t.Union([t.String(), t.Null()]);

const SongPersonRefSchema = t.Array(t.Object({ personId: t.String(), role: t.Union([t.Literal(1), t.Literal(2), t.Literal(3)]), orderNo: t.Number() }));
const SongTagRefSchema = t.Array(t.Object({ songTagId: t.String() }));

export const songs = new Elysia({ prefix: '/songs' })
  .use(authGuard)
  .get('/create-form', async ({ authSession }) => {
    return withAuthRetry(authSession, async (client) => {
      const [persons, types, tags] = await Promise.all([
        personServiceListPersons({ client }),
        songTypeServiceListSongTypes({ client }),
        songTagServiceListSongTags({ client }),
      ]);

      return {
        persons: resolveApiResponse(persons).persons,
        types: resolveApiResponse(types).types,
        tags: resolveApiResponse(tags).tags,
      };
    });
  })
  .get(
    '/search',
    async ({ query, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        const [searchResult, types] = await Promise.all([
          songServiceSearchSongs({
            client,
            query: {
              title: query.title || undefined,
              type: query.type as SongTypeValue | undefined,
              is_display: query.is_display,
              sort: (query.sort ?? 'order_no') as SongSearchSortBy,
              order: (query.order ?? 'asc') as SortOrder,
              page: query.page ?? 1,
              per_page: (query.per_page ?? 25) as PerPage,
            },
          }),
          songTypeServiceListSongTypes({ client }),
        ]);

        return {
          ...resolveApiResponse(searchResult),
          types: resolveApiResponse(types).types,
        };
      });
    },
    {
      query: t.Object({
        title: t.String(),
        type: t.Optional(t.Number()),
        is_display: t.Optional(t.Boolean()),
        sort: t.Optional(t.Union([t.Literal('title'), t.Literal('order_no')])),
        order: t.Optional(t.Union([t.Literal('asc'), t.Literal('desc')])),
        page: t.Optional(t.Number()),
        per_page: t.Optional(t.Number()),
      }),
    },
  )
  .get(
    '/:songId/edit-form',
    async ({ params: { songId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        const [song, persons, types, tags] = await Promise.all([
          songServiceGetSong({ client, path: { songId } }),
          personServiceListPersons({ client }),
          songTypeServiceListSongTypes({ client }),
          songTagServiceListSongTags({ client }),
        ]);

        return {
          ...resolveApiResponse(song),
          persons: resolveApiResponse(persons).persons,
          types: resolveApiResponse(types).types,
          tags: resolveApiResponse(tags).tags,
        };
      });
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
    },
  )
  .get(
    '/:songId',
    async ({ params: { songId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(await songServiceGetSong({ client, path: { songId } }));
      });
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
    },
  )
  .post(
    '/',
    async ({ body: { title, description, lyricsLink, typeValue, isDisplay, persons, tags }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await songServiceCreateSong({
            client,
            body: {
              title,
              description,
              lyricsLink,
              typeValue,
              isDisplay,
              persons,
              tags,
            },
          }),
        );
      });
    },
    {
      body: t.Object({
        title: t.String(),
        description: t.String(),
        lyricsLink: NullableStringSchema,
        typeValue: SongTypeValueSchema,
        isDisplay: t.Boolean(),
        persons: SongPersonRefSchema,
        tags: SongTagRefSchema,
      }),
    },
  )
  .put(
    '/:songId',
    async ({ params: { songId }, body: { title, description, lyricsLink, typeValue, isDisplay, orderNo, persons, tags }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        return resolveApiResponse(
          await songServiceUpdateSong({
            client,
            path: { songId },
            body: {
              title,
              description,
              lyricsLink,
              typeValue,
              isDisplay,
              orderNo,
              persons,
              tags,
            },
          }),
        );
      });
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
      body: t.Object({
        title: t.String(),
        description: t.String(),
        lyricsLink: NullableStringSchema,
        typeValue: SongTypeValueSchema,
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
        persons: SongPersonRefSchema,
        tags: SongTagRefSchema,
      }),
    },
  )
  .delete(
    '/:songId',
    async ({ params: { songId }, authSession }) => {
      return withAuthRetry(authSession, async (client) => {
        resolveApiResponse(await songServiceDeleteSong({ client, path: { songId } }));
      });
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
    },
  );
