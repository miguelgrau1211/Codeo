# Stage 1: Build
FROM node:22-bookworm as build-step

WORKDIR /app

# Copiar archivos de dependencias
COPY package.json package-lock.json ./

# Instalar dependencias
RUN npm install

# Copiar el resto del código
COPY . .

# Construir la aplicación
RUN npm run build

# Stage 2: Serve
FROM node:22-slim

WORKDIR /app

# Instalar http-server
RUN npm install -g http-server

# Copiar archivos
COPY --from=build-step /app/dist/Front/browser /app/dist/Front/browser

# Railway usa $PORT. Forzamos a http-server a usarlo con -p
# --spa redirige todas las rutas a index.html (necesario para Angular)
CMD ["sh", "-c", "http-server dist/Front/browser -p ${PORT:-8080} --spa"]
