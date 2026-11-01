import express from 'express';
import { createServer } from 'http';
import { Server } from 'socket.io';

const app = express();
const server = createServer(app);
const io = new Server(server, {
    cors: { origin: "*" }
});

app.use(express.json());

// Endpoint receiver HTTP dari Laravel Controller
app.post('/emit-sensor', (req, res) => {
    const telemetryData = req.body;
    
    // Broadcast data instan ke seluruh browser client via Socket.io
    io.emit('telemetry-update', telemetryData);
    
    res.json({ success: true, message: 'Broadcast Socket.io berhasil' });
});

io.on('connection', (socket) => {
    console.log('⚡ Client Dashboard terhubung via WebSocket:', socket.id);
});

const PORT = 6001;
server.listen(PORT, () => {
    console.log(`🚀 TahuGuard WebSocket Microservice running on port ${PORT}`);
});