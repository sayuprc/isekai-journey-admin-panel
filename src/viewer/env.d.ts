interface ImportMetaEnv {
  readonly API_URL: string;
  readonly SITE_NOINDEX?: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
