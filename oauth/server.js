const WebSocket = require('ws');

const wss = new WebSocket.Server({ port: 8080 });

wss.on('connection', ws => {
    console.log('Admin connected');

    setInterval(() => {
        ws.send(JSON.stringify({
            activeUsers: Math.floor(Math.random() * 100),
            requests: Math.floor(Math.random() * 500),
            time: new Date()
        }));
    }, 2000);
});
