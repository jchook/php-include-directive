# You can use this to build the phpp phar.
#
# docker build -t phpp:builder builder
# docker run -it -v "$(pwd)":/app phpp:builder
#
FROM alpine:3.14

ARG GROUP_ID=1000
ARG USER_ID=1000

RUN apk add --no-cache php8 php8-phar wget \
  && wget -qO /usr/bin/composer https://github.com/composer/composer/releases/download/2.1.5/composer.phar \
  && ln -s /usr/bin/php8 /usr/bin/php \
  && chmod +x /usr/bin/composer \
  && echo 'phar.readonly = 0' > /etc/php8/conf.d/10-phar.ini \
  && addgroup -S -g ${GROUP_ID} app \
  && adduser -S -G app -u ${USER_ID} app

VOLUME /app
WORKDIR /app

USER app

CMD ["bin/build-phar"]
