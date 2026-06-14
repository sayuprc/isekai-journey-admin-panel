ARG BUN_VERSION
FROM oven/bun:${BUN_VERSION} AS build

WORKDIR /app

RUN apt-get update \
  && apt-get install -y \
    ca-certificates \
    curl \
    libatomic1 \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && curl https://mise.run | sh

ENV PATH=/root/.local/bin:${PATH}

COPY mise.toml ./

RUN mise trust \
  && mise install pnpm

WORKDIR /app/src

COPY src/package.json src/pnpm-lock.yaml src/pnpm-workspace.yaml ./
COPY src/admin/package.json admin/package.json
COPY src/contracts/package.json contracts/package.json
COPY src/viewer/package.json viewer/package.json

RUN mise exec -- pnpm install --frozen-lockfile

COPY src/admin admin

ARG PUBLIC_APP_URL
ENV PUBLIC_APP_URL=${PUBLIC_APP_URL}

RUN mise exec -- bun --filter admin build

ARG BUN_VERSION
FROM oven/bun:${BUN_VERSION}-slim AS runtime

ENV TZ=Asia/Tokyo

WORKDIR /app

COPY --from=build /app/src/admin/dist /app/dist

EXPOSE 8080

CMD ["bun", "/app/dist/server/entry.mjs"]
