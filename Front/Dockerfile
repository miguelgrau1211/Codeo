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
# Ajustado a la ruta que configuramos en angular.json
COPY --from=build-step /app/dist/Front/browser /app/dist/Front/browser

EXPOSE 8080

# Comando para arrancar el servidor de estáticos
CMD ["serve", "-s", "dist/Front/browser", "-p", "8080"]
