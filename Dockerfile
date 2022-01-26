FROM node:16-bullseye AS builder
RUN npm i
RUN npm run build

FROM php:7.2-apache
# Copy built app into webserver
COPY dist/ /var/www/html/
# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
