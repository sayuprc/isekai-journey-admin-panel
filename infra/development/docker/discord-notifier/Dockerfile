FROM debian:bookworm-slim AS builder

ARG MOONBIT_VERSION

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    bash \
    build-essential \
    ca-certificates \
    curl \
    git \
  && rm -rf /var/lib/apt/lists/*

ENV PATH="/root/.moon/bin:${PATH}"

RUN curl -fsSL https://cli.moonbitlang.com/install/unix.sh \
  | bash -s -- "${MOONBIT_VERSION}"

WORKDIR /src

COPY src/discord-notifier/moon.mod src/discord-notifier/moon.pkg ./
COPY src/discord-notifier/*.mbt ./
COPY src/discord-notifier/cmd ./cmd

RUN moon update \
  && moon build --target native --release

FROM debian:bookworm-slim

ENV TZ=Asia/Tokyo \
  PORT=8080

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    ca-certificates \
    tzdata \
  && rm -rf /var/lib/apt/lists/* \
  && groupadd --system app \
  && useradd --system --gid app --home-dir /home/app --create-home \
    --shell /usr/sbin/nologin app

COPY --from=builder /src/_build/native/release/build/cmd/main/main.exe /usr/local/bin/discord-notifier

USER app

EXPOSE 8080

CMD ["discord-notifier"]
