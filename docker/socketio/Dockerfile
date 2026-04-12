# Socket.IO Server Dockerfile
# Phase 1: Environment Setup & Docker Foundation

FROM node:20-alpine

WORKDIR /app

# Copy package files
COPY package.json package-lock.json* ./

# Install dependencies
RUN npm ci --only=production || npm install --only=production

# Copy application
COPY server.js ./

# Expose port
EXPOSE 3001

# Start server
CMD ["node", "server.js"]