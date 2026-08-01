// api/data.js — Función serverless de Vercel
// Lee y guarda los datos de la app en Upstash Redis.
// Las variables KV_REST_API_URL y KV_REST_API_TOKEN las inyecta Vercel
// automáticamente al conectar la integración de Upstash (Marketplace Storage).

const REST_URL = process.env.KV_REST_API_URL || process.env.UPSTASH_REDIS_REST_URL;
const REST_TOKEN = process.env.KV_REST_API_TOKEN || process.env.UPSTASH_REDIS_REST_TOKEN;

async function redisCmd(cmd) {
  const r = await fetch(REST_URL, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${REST_TOKEN}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(cmd)
  });
  if (!r.ok) throw new Error('Upstash error: ' + r.status);
  const json = await r.json();
  return json.result;
}

// Solo letras, números, guiones y guion bajo; máximo 60 caracteres.
function cleanCode(code) {
  return String(code || '').trim().toLowerCase().replace(/[^a-z0-9_-]/g, '').slice(0, 60);
}

module.exports = async (req, res) => {
  if (!REST_URL || !REST_TOKEN) {
    res.status(500).json({ error: 'Falta configurar la base de datos (variables KV_REST_API_URL / KV_REST_API_TOKEN en Vercel).' });
    return;
  }

  try {
    if (req.method === 'GET') {
      const code = cleanCode(req.query.code);
      if (!code) { res.status(400).json({ error: 'Falta el código.' }); return; }
      const value = await redisCmd(['GET', `plan:${code}`]);
      res.status(200).json({ data: value ? JSON.parse(value) : null });
      return;
    }

    if (req.method === 'POST') {
      let body = req.body;
      if (typeof body === 'string') { try { body = JSON.parse(body); } catch (e) { body = {}; } }
      const code = cleanCode(body && body.code);
      if (!code) { res.status(400).json({ error: 'Falta el código.' }); return; }
      await redisCmd(['SET', `plan:${code}`, JSON.stringify((body && body.data) || {})]);
      res.status(200).json({ ok: true });
      return;
    }

    res.status(405).json({ error: 'Método no permitido.' });
  } catch (e) {
    res.status(500).json({ error: String((e && e.message) || e) });
  }
};
