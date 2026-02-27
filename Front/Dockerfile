# Stage 1: Build
FROM node:22-bookworm as build-step

WORKDIR /app

# Copiar archivos de dependencias
COPY package.json package-lock.json ./

# Instalar dependencias
RUN npm install

# Copiar el resto del código
COPY . .

# Generar archivos de entorno y construir
# Railway inyecta las variables aquí durante el build
RUN node set-env.js && npm run build -- --configuration production

# Stage 2: Serve
FROM node:22-slim

WORKDIR /app

# Instalar http-server
RUN npm install -g http-server

# Copiar archivos
COPY --from=build-step /app/dist/Front/browser /app/dist/Front/browser

# Railway usa $PORT. Forzamos a http-server a usarlo con -p
# -g activa compresión Gzip
# -c-1 desactiva caché de archivos (para desarrollo) o pon -c3600 para 1 hora
# --spa redirige todas las rutas a index.html
CMD ["sh", "-c", "http-server dist/Front/browser -p ${PORT:-8080} -g --spa -c3600"]
