ARG BUN_VERSION
FROM oven/bun:${BUN_VERSION}

ENV TZ=Asia/Tokyo

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
  && mise install pnpm node

WORKDIR /app/src

COPY src/package.json src/pnpm-lock.yaml src/pnpm-workspace.yaml ./
COPY src/admin/package.json admin/package.json
COPY src/contracts/package.json contracts/package.json
COPY src/viewer/package.json viewer/package.json

RUN mise exec -- pnpm install --frozen-lockfile

COPY src/viewer viewer

COPY infra/staging/docker/viewer/viewer-deploy /usr/local/bin/viewer-deploy

RUN chmod +x /usr/local/bin/viewer-deploy

ENTRYPOINT ["viewer-deploy"]
