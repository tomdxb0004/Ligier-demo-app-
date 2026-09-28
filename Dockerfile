FROM php:8.3-cli-alpine

WORKDIR /app
COPY public ./public
COPY views ./views

RUN adduser -D -H -u 10001 app
USER app

# Railway injects PORT at runtime; 8080 is the local default.
ENV PORT=8080
EXPOSE 8080

# PHP's built-in server is fine for a low-traffic, password-protected demo.
CMD ["sh", "-c", "exec php -S 0.0.0.0:${PORT} -t public public/index.php"]
