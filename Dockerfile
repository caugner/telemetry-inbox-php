FROM php:7.0-alpine

ENV APP_DIR /app

RUN mkdir -p $APP_DIR
COPY . $APP_DIR
WORKDIR $APP_DIR

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "./web"]
