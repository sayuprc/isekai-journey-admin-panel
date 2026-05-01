import { Elysia, t } from 'elysia';
import {
  creatorServiceListCreators,
  songAttributeServiceListSongAttributes,
  songServiceCreateSong,
  songServiceDeleteSong,
  songServiceGetSong,
  songServiceSearchSongs,
  songServiceUpdateSong,
  songTagServiceListSongTags,
  songTypeServiceListSongTypes,
} from '../../generated';
import type { PerPage, SongAttributeValue, SongSearchSortBy, SongTypeValue, SortOrder } from '../../generated';
import { createAuthClient } from '../client';
import { resolveApiResponse } from '../errors';
import { authGuard } from '../middleware';

const SongTypeValueSchema = t.Union([t.Literal(1), t.Literal(2)]);

const SongAttributeValueSchema = t.Union([t.Literal(1), t.Literal(2), t.Literal(3), t.Literal(4), t.Literal(5)]);

const CreatorRefSchema = t.Array(t.Object({ creatorId: t.String() }));
const SongTagRefSchema = t.Array(t.Object({ songTagId: t.String() }));

export const songs = new Elysia({ prefix: '/songs' })
  .use(authGuard)
  .get('/create-form', async ({ credential }) => {
    const authClient = createAuthClient(credential);
    const [creators, types, attributes, tags] = await Promise.all([
      creatorServiceListCreators({ client: authClient }),
      songTypeServiceListSongTypes({ client: authClient }),
      songAttributeServiceListSongAttributes({ client: authClient }),
      songTagServiceListSongTags({ client: authClient }),
    ]);

    return {
      creators: resolveApiResponse(creators).creators,
      types: resolveApiResponse(types).types,
      attributes: resolveApiResponse(attributes).attributes,
      tags: resolveApiResponse(tags).tags,
    };
  })
  .get(
    '/search',
    async ({ query, credential }) => {
      const authClient = createAuthClient(credential);
      const [searchResult, types, attributes] = await Promise.all([
        songServiceSearchSongs({
          client: authClient,
          query: {
            title: query.title || undefined,
            type: query.type as SongTypeValue | undefined,
            attribute: query.attribute as SongAttributeValue | undefined,
            is_display: query.is_display,
            sort: (query.sort ?? 'order_no') as SongSearchSortBy,
            order: (query.order ?? 'asc') as SortOrder,
            page: query.page ?? 1,
            per_page: (query.per_page ?? 25) as PerPage,
          },
        }),
        songTypeServiceListSongTypes({ client: authClient }),
        songAttributeServiceListSongAttributes({ client: authClient }),
      ]);

      return {
        ...resolveApiResponse(searchResult),
        types: resolveApiResponse(types).types,
        attributes: resolveApiResponse(attributes).attributes,
      };
    },
    {
      query: t.Object({
        title: t.String(),
        type: t.Optional(t.Number()),
        attribute: t.Optional(t.Number()),
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
    async ({ params: { songId }, credential }) => {
      const authClient = createAuthClient(credential);
      const [song, creators, types, attributes, tags] = await Promise.all([
        songServiceGetSong({ client: authClient, path: { songId } }),
        creatorServiceListCreators({ client: authClient }),
        songTypeServiceListSongTypes({ client: authClient }),
        songAttributeServiceListSongAttributes({ client: authClient }),
        songTagServiceListSongTags({ client: authClient }),
      ]);

      return {
        ...resolveApiResponse(song),
        creators: resolveApiResponse(creators).creators,
        types: resolveApiResponse(types).types,
        attributes: resolveApiResponse(attributes).attributes,
        tags: resolveApiResponse(tags).tags,
      };
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
    },
  )
  .get(
    '/:songId',
    async ({ params: { songId }, credential }) => {
      return resolveApiResponse(await songServiceGetSong({ client: createAuthClient(credential), path: { songId } }));
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
    },
  )
  .post(
    '/',
    async ({
      body: { title, description, typeValue, attributeValue, isDisplay, lyricists, composers, arrangers, tags },
      credential,
    }) => {
      return resolveApiResponse(
        await songServiceCreateSong({
          client: createAuthClient(credential),
          body: {
            title,
            description,
            typeValue,
            attributeValue,
            isDisplay,
            lyricists,
            composers,
            arrangers,
            tags,
          },
        }),
      );
    },
    {
      body: t.Object({
        title: t.String(),
        description: t.String(),
        typeValue: SongTypeValueSchema,
        attributeValue: t.Optional(SongAttributeValueSchema),
        isDisplay: t.Boolean(),
        lyricists: CreatorRefSchema,
        composers: CreatorRefSchema,
        arrangers: CreatorRefSchema,
        tags: SongTagRefSchema,
      }),
    },
  )
  .put(
    '/:songId',
    async ({
      params: { songId },
      body: { title, description, typeValue, attributeValue, isDisplay, orderNo, composers, lyricists, arrangers, tags },
      credential,
    }) => {
      return resolveApiResponse(
        await songServiceUpdateSong({
          client: createAuthClient(credential),
          path: { songId },
          body: {
            title,
            description,
            typeValue,
            attributeValue,
            isDisplay,
            orderNo,
            lyricists,
            composers,
            arrangers,
            tags,
          },
        }),
      );
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
      body: t.Object({
        title: t.String(),
        description: t.String(),
        typeValue: SongTypeValueSchema,
        attributeValue: t.Optional(SongAttributeValueSchema),
        isDisplay: t.Boolean(),
        orderNo: t.Number(),
        lyricists: CreatorRefSchema,
        composers: CreatorRefSchema,
        arrangers: CreatorRefSchema,
        tags: SongTagRefSchema,
      }),
    },
  )
  .delete(
    '/:songId',
    async ({ params: { songId }, credential }) => {
      resolveApiResponse(await songServiceDeleteSong({ client: createAuthClient(credential), path: { songId } }));
    },
    {
      params: t.Object({
        songId: t.String(),
      }),
    },
  );
