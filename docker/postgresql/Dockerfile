FROM postgres:16

RUN apt-get update \
  && apt-get install -y locales \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && sed -i -E 's/# (ja_JP.UTF-8)/\1/' /etc/locale.gen \
  && locale-gen \
  && update-locale LANG=ja_JP.UTF-8
