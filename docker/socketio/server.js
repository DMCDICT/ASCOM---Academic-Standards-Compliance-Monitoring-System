/**
 * ASCOM Socket.IO Server
 * Real-time notifications using self-hosted WebSockets
 * Phase 1: Environment Setup & Docker Foundation
 */

const { Server } = require('socket.io');
const { createAdapter } = require('@socket.io/redis-adapter');
const { createClient } = require('redis');

const http = require('http');
const express = require('express');

const app = express();
const server = http.createServer(app);

// Socket.IO configuration
const io = new Server(server, {
  cors: {
    origin: process.env.CORS_ORIGINS || '*',
    methods: ['GET', 'POST'],
    credentials: true
  },
  pingTimeout: 60000,
  pingInterval: 25000,
  path: process.env.SOCKETIO_PATH || '/socket.io',
  transports: ['websocket', 'polling']
});

// Redis adapter for scaling
const redisHost = process.env.REDIS_HOST || 'redis';
const redisPort = process.env.REDIS_PORT || 6379;
const redisPassword = process.env.REDIS_PASSWORD || '';

async function setupRedisAdapter() {
  try {
    const pubClient = createClient({
      url: `redis://${redisPassword ? ':' + redisPassword + '@' : ''}${redisHost}:${redisPort}/3`
    });
    const subClient = pubClient.duplicate();

    await pubClient.connect();
    await subClient.connect();

    io.adapter(createAdapter(pubClient, subClient));
    console.log('Redis adapter connected for Socket.IO');
  } catch (error) {
    console.log('Redis adapter not available, using default:', error.message);
  }
}

// Namespaces
const notifications = io.of('/notifications');
const messages = io.of('/messages');

// Authentication middleware
io.use((socket, next) => {
  const token = socket.handshake.auth.token;
  const userId = socket.handshake.auth.userId;
  
  if (!userId) {
    return next(new Error('Authentication required'));
  }
  
  socket.userId = userId;
  socket.userRole = socket.handshake.auth.userRole || 'user';
  next();
});

// Connection handling
io.on('connection', (socket) => {
  console.log(`User connected: ${socket.userId}`);
  
  // Join user to their personal room for targeted notifications
  socket.join(`user:${socket.userId}`);
  
  // Join role-based room
  socket.join(`role:${socket.userRole}`);
  
  // Handle join room requests
  socket.on('join:room', (room) => {
    socket.join(room);
    console.log(`User ${socket.userId} joined room: ${room}`);
  });
  
  // Handle leave room requests
  socket.on('leave:room', (room) => {
    socket.leave(room);
    console.log(`User ${socket.userId} left room: ${room}`);
  });
  
  // Handle disconnect
  socket.on('disconnect', (reason) => {
    console.log(`User ${socket.userId} disconnected: ${reason}`);
  });
});

// Notifications namespace
notifications.on('connection', (socket) => {
  console.log(`Notification client connected: ${socket.userId}`);
  
  socket.on('subscribe', (channels) => {
    if (Array.isArray(channels)) {
      channels.forEach(channel => socket.join(channel));
    }
  });
  
  socket.on('unsubscribe', (channels) => {
    if (Array.isArray(channels)) {
      channels.forEach(channel => socket.leave(channel));
    }
  });
});

// Messages namespace
messages.on('connection', (socket) => {
  console.log(`Message client connected: ${socket.userId}`);
  
  // Join user's message thread rooms
  socket.on('join:thread', (threadId) => {
    socket.join(`thread:${threadId}`);
  });
});

// Utility functions for sending notifications from Laravel/Backend
function emitNotification(userId, event, data) {
  io.to(`user:${userId}`).emit(event, data);
}

function emitToRole(role, event, data) {
  io.to(`role:${role}`).emit(event, data);
}

function emitToChannel(channel, event, data) {
  notifications.to(channel).emit(event, data);
}

function broadcast(event, data) {
  io.emit(event, data);
}

// Make functions available globally for testing
global.io = io;
global.emitNotification = emitNotification;
global.emitToRole = emitToRole;
global.emitToChannel = emitToChannel;
global.broadcast = broadcast;

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({ status: 'ok', clients: io.engine.clientsCount });
});

// Start server
const PORT = process.env.SOCKETIO_PORT || 3001;

setupRedisAdapter().then(() => {
  server.listen(PORT, '0.0.0.0', () => {
    console.log(`Socket.IO server running on port ${PORT}`);
    console.log(`Redis: ${redisHost}:${redisPort}`);
  });
});

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('Shutting down Socket.IO server...');
  server.close(() => {
    process.exit(0);
  });
});

module.exports = { io, emitNotification, emitToRole, emitToChannel, broadcast };