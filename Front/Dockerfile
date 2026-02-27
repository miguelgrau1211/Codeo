# Stage 1: Build 
FROM node:22-bookworm as build-step

WORKDIR /app

# Instalar dependencias
COPY package.json package-lock.json ./
RUN npm install

# Copiar código y construir
COPY . .
RUN npm run build -- --configuration production

# Stage 2: Serve
FROM node:22-slim

WORKDIR /app

# Instalar http-server
RUN npm install -g http-server

# Copiar archivos construidos
COPY --from=build-step /app/dist/Front/browser /app/dist/Front/browser

# AL ARRANCAR: 
# 1. Inyectamos la variable $API_URL real de Railway dentro del index.html
# 2. Arrancamos el servidor
CMD ["sh", "-c", "sed -i \"s|__API_URL_PLACEHOLDER__|${API_URL:-http://localhost/api}|g\" dist/Front/browser/index.html && http-server dist/Front/browser -p ${PORT:-8080} -g --spa -c3600"]
