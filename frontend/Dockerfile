FROM node:20-alpine AS build
WORKDIR /app
COPY package.json ./
RUN npm install
COPY . .
RUN npm run build

FROM nginx:alpine
COPY nginx.conf.template /etc/nginx/nginx.conf.template
COPY 40-envsubst-api-url.sh /docker-entrypoint.d/40-envsubst-api-url.sh
RUN chmod +x /docker-entrypoint.d/40-envsubst-api-url.sh
COPY --from=build /app/dist /usr/share/nginx/html
EXPOSE 80
