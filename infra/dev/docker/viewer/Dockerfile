ARG NODE_VERSION=24
FROM node:${NODE_VERSION}-slim

ENV TZ=Asia/Tokyo
ENV PNPM_HOME=/pnpm
ENV PATH=${PNPM_HOME}:${PATH}

WORKDIR /app

RUN apt-get update \
  && apt-get install -y \
    ca-certificates \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

COPY infra/dev/docker/viewer/viewer-deploy /usr/local/bin/viewer-deploy
COPY mise.toml ./

RUN corepack enable \
  && corepack prepare "pnpm@$(sed -n 's/^pnpm = "\(.*\)"$/\1/p' mise.toml)" --activate \
  && chmod +x /usr/local/bin/viewer-deploy

WORKDIR /app/src

COPY src/package.json src/pnpm-lock.yaml src/pnpm-workspace.yaml ./
COPY src/viewer/package.json viewer/package.json

RUN pnpm install --frozen-lockfile --filter viewer --prod \
  && rm -rf /pnpm/store

COPY src/viewer viewer

ENTRYPOINT ["viewer-deploy"]
