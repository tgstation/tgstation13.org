FROM docker.io/node:16-bullseye AS builder
WORKDIR /app
# Copy sources
COPY src src
COPY gulpfile.js gulpfile.js
COPY package.json package.json
COPY package-lock.json package-lock.json
# Build the app
RUN npm install && npm run build && rm -rf node_modules

FROM docker.io/php:7.2-apache
# Copy built app into webserver
COPY --from=builder /app/src/ /var/www/html/
# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
