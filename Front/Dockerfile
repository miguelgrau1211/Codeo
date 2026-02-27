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

# Instalar http-server para servir estáticos de forma más robusta
RUN npm install -g http-server

# Copiar solo los archivos construidos desde la etapa anterior
COPY --from=build-step /app/dist/Front/browser /app/dist/Front/browser

# Railway inyecta la variable $PORT.
# Usamos http-server con soporte para SPA (redirigiendo a index.html)
CMD ["sh", "-c", "http-server dist/Front/browser -p $PORT --proxy http://localhost:$PORT?"]
