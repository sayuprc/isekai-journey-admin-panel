interface ImportMetaEnv {
  readonly API_URL: string;
  readonly CACHE_URL: string;
  readonly CACHE_TOKEN: string;
  readonly PUBLIC_APP_URL: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
