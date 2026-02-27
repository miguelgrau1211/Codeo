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

# Instalar serve para servir estáticos
RUN npm install -g serve

# Copiar solo los archivos construidos desde la etapa anterior
COPY --from=build-step /app/dist/Front/browser /app/dist/Front/browser

# Railway inyecta la variable $PORT. Usamos shell para expandirla.
CMD ["sh", "-c", "serve -s dist/Front/browser -l tcp://0.0.0.0:$PORT"]
