import { defineConfig } from '@hey-api/openapi-ts';

export default defineConfig({
  client: '@hey-api/typescript',
  input: '../contracts/generated/oas/IsekaiTerrarium.Admin.v1.yaml',
  output: {
    path: './src/generated',
    format: 'prettier',
  },
  types: {
    enums: 'typescript',
  },
});
