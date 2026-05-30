/**
 * SRP IT Stock – Real-time Sync Server
 * Express + WebSocket (ws) + Upstash Redis persistence
 * ข้อมูลไม่หายแม้ Render restart (แก้ปัญหา ephemeral filesystem)
 *
 * Setup:
 *  1. สร้าง Upstash Redis ฟรีที่ https://upstash.com/
 *  2. ตั้ง Environment Variables ใน Render:
 *     UPSTASH_REDIS_REST_URL  = https://xxx.upstash.io
 *     UPSTASH_REDIS_REST_TOKEN = AXxx...
 */

const express   = require('express');
const http      = require('http');
const WebSocket = require('ws');
const fs        = require('fs');
const path      = require('path');

const app    = express();
const server = http.createServer(app);
const wss    = new WebSocket.Server({ server });

const PORT = process.env.PORT || 3000;

// ─── Upstash Redis client (ใช้ fetch — ไม่ต้องติดตั้ง package เพิ่ม) ─────────
const UPSTASH_URL   = process.env.UPSTASH_REDIS_REST_URL;
const UPSTASH_TOKEN = process.env.UPSTASH_REDIS_REST_TOKEN;
const REDIS_KEY     = 'srp_store';

// fallback: ถ้าไม่มี Upstash → ใช้ JSON file เหมือนเดิม
const USE_REDIS  = !!(UPSTASH_URL && UPSTASH_TOKEN);
const DATA_FILE  = path.join(__dirname, 'data', 'srp_data.json');
if (!USE_REDIS && !fs.existsSync(path.join(__dirname, 'data'))) {
  fs.mkdirSync(path.join(__dirname, 'data'), { recursive: true });
}

console.log(`[SRP] Storage backend: ${USE_REDIS ? 'Upstash Redis' : 'JSON file (ephemeral!)'}`);

// ─── in-memory store ─────────────────────────────────────────────────────────
let store = {
  users:          null,
  stock:          null,
  requests:       null,
  repairs:        null,
  devices:        null,
  settings:       null,
  repairSettings: null,
  counters:       { req: 6, repair: 4, device: 4 },
  _savedAt:       null,
};

// ─── Redis helpers ────────────────────────────────────────────────────────────
async function redisGet(key) {
  const res = await fetch(`${UPSTASH_URL}/get/${key}`, {
    headers: { Authorization: `Bearer ${UPSTASH_TOKEN}` },
  });
  const json = await res.json();
  return json.result; // string or null
}

async function redisSet(key, value) {
  await fetch(`${UPSTASH_URL}/set/${key}`, {
    method:  'POST',
    headers: { Authorization: `Bearer ${UPSTASH_TOKEN}`, 'Content-Type': 'application/json' },
    body:    JSON.stringify({ value }),
  });
}

// ─── load persisted data ──────────────────────────────────────────────────────
async function loadStore() {
  try {
    if (USE_REDIS) {
      const raw = await redisGet(REDIS_KEY);
      if (raw) {
        store = Object.assign(store, JSON.parse(raw));
        console.log('[SRP] Loaded from Upstash Redis, savedAt:', store._savedAt);
      } else {
        console.log('[SRP] Upstash Redis: no data yet');
      }
    } else {
      if (fs.existsSync(DATA_FILE)) {
        const raw = fs.readFileSync(DATA_FILE, 'utf8');
        store = Object.assign(store, JSON.parse(raw));
        console.log('[SRP] Loaded from JSON file, savedAt:', store._savedAt);
      }
    }
  } catch (e) {
    console.error('[SRP] Failed to load:', e.message);
  }
}

// ─── save (debounced 2 s) ─────────────────────────────────────────────────────
let _saveTimer = null;
function saveToDisk() {
  if (_saveTimer) clearTimeout(_saveTimer);
  _saveTimer = setTimeout(async () => {
    try {
      store._savedAt = new Date().toISOString();
      const raw = JSON.stringify(store);
      if (USE_REDIS) {
        await redisSet(REDIS_KEY, raw);
        console.log('[SRP] Saved to Upstash Redis');
      } else {
        fs.writeFileSync(DATA_FILE, JSON.stringify(store, null, 2), 'utf8');
      }
    } catch (e) {
      console.error('[SRP] Failed to save:', e.message);
    }
  }, 2000);
}

// ─── broadcast ────────────────────────────────────────────────────────────────
function broadcast(data, senderWs) {
  const msg = JSON.stringify(data);
  wss.clients.forEach(client => {
    if (client.readyState === WebSocket.OPEN && client !== senderWs) {
      client.send(msg);
    }
  });
}

// ─── merge helper (ไม่ให้ข้อมูลเก่า overwrite ข้อมูลใหม่) ──────────────────
function mergePayload(payload) {
  if (payload.users)          store.users          = payload.users;
  if (payload.stock)          store.stock          = payload.stock;
  if (payload.requests)       store.requests       = payload.requests;
  if (payload.repairs)        store.repairs        = payload.repairs;
  if (payload.devices)        store.devices        = payload.devices;
  if (payload.settings)       store.settings       = payload.settings;
  if (payload.repairSettings) store.repairSettings = payload.repairSettings;
  if (payload.counters) {
    store.counters = store.counters || {};
    // เอาค่ามากสุดเสมอ (ป้องกัน counter ถอยหลัง)
    if (payload.counters.req    !== undefined) store.counters.req    = Math.max(store.counters.req    || 0, payload.counters.req);
    if (payload.counters.repair !== undefined) store.counters.repair = Math.max(store.counters.repair || 0, payload.counters.repair);
    if (payload.counters.device !== undefined) store.counters.device = Math.max(store.counters.device || 0, payload.counters.device);
  }
}

// ─── WebSocket handler ────────────────────────────────────────────────────────
wss.on('connection', (ws, req) => {
  const ip = req.socket.remoteAddress;
  console.log(`[WS] Client connected: ${ip}  total=${wss.clients.size}`);

  // ส่ง snapshot ปัจจุบันให้ client ใหม่ทันที
  ws.send(JSON.stringify({ type: 'init', payload: store }));

  ws.on('message', raw => {
    let msg;
    try { msg = JSON.parse(raw); } catch { return; }
    const { type, payload, clientRole } = msg;

    if (type === 'push') {
      const allowedRoles = ['admin', 'manager', 'warehouse'];
      if (!allowedRoles.includes(clientRole)) return;
      if (!payload || typeof payload !== 'object') return;

      mergePayload(payload);
      console.log(`[WS] push from ${clientRole}@${ip}  keys=[${Object.keys(payload).join(',')}]`);
      saveToDisk();
      broadcast({ type: 'update', payload }, ws);
    }
  });

  ws.on('close', () => console.log(`[WS] disconnected: ${ip}  total=${wss.clients.size}`));
  ws.on('error', err => console.error('[WS] Error:', err.message));
});

// ─── REST endpoints ───────────────────────────────────────────────────────────
app.use(express.static(__dirname));
app.use(express.json({ limit: '10mb' }));

app.get('/health', (_, res) => res.json({
  ok: true, clients: wss.clients.size,
  savedAt: store._savedAt, backend: USE_REDIS ? 'redis' : 'file'
}));

app.get('/api/store', (_, res) => res.json(store));

app.post('/api/push', (req, res) => {
  const { payload, clientRole } = req.body || {};
  const allowedRoles = ['admin', 'manager', 'warehouse'];
  if (!allowedRoles.includes(clientRole)) return res.status(403).json({ error: 'forbidden' });
  if (!payload || typeof payload !== 'object') return res.status(400).json({ error: 'bad payload' });

  mergePayload(payload);
  console.log(`[REST] push from ${clientRole}  keys=[${Object.keys(payload).join(',')}]`);
  saveToDisk();
  broadcast({ type: 'update', payload }, null);
  res.json({ ok: true });
});

// ─── start ────────────────────────────────────────────────────────────────────
loadStore().then(() => {
  server.listen(PORT, () => console.log(`[SRP] Listening on port ${PORT}`));
});
