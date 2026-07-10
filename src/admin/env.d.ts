/// <reference types="vitest/importMeta" />

declare namespace NodeJS {
  interface ProcessEnv {
    PORT?: string;
    ADMIN_SSR_NO_EXTERNAL?: string;
  }
}
