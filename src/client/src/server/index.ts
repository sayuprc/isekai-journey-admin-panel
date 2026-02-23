import { Elysia } from 'elysia';

export const app = new Elysia({ prefix: '/api' });

export type App = typeof app;
