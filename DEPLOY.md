# Runbook de despliegue automatizado
### Para el plugin de Vercel en Cursor (o cualquier agente que ejecute la Vercel CLI)

Este documento describe **exactamente** lo que el plugin debe hacer, en orden, con los comandos literales. No requiere interpretación: si un paso falla, el paso siguiente indica cómo detectarlo.

## Objetivo
Publicar este repositorio en Vercel y dejar `api/data.js` conectada a una base de datos Redis (Upstash), para que la sincronización entre dispositivos de la app funcione sin ningún paso manual en el dashboard.

## 0. Prerrequisitos (verificar, no asumir)
```bash
vercel --version      # si falla: npm i -g vercel
vercel whoami          # si falla: vercel login (requiere navegador, no automatizable sin humano)
```
Nota: existe un **Vercel Plugin oficial para agentes** que expone estos mismos comandos de forma nativa en editores como Cursor:
```bash
npx plugins add vercel/vercel-plugin
```
Si el plugin del usuario ya es ese, puede saltarse la verificación manual de la CLI y usar sus propias herramientas internas para los mismos pasos.

## 1. Enlazar el proyecto con Vercel
Ejecutar desde la raíz de este repo (donde está `vercel.json`):
```bash
vercel link --yes
```
Efecto: crea `.vercel/project.json`. Si el proyecto no existe en la cuenta de Vercel, lo crea; si ya existe, lo vincula.

## 2. Aprovisionar Redis (Upstash) y conectarlo al proyecto
```bash
vercel install upstash --name mi-plan-redis --plan free -e production -e preview -e development
```
Efecto:
- Instala la integración de Upstash en la cuenta/equipo si no estaba instalada.
- Crea una base de datos Redis (plan gratuito).
- La conecta automáticamente al proyecto enlazado en el paso 1.
- Añade las variables de entorno `KV_REST_API_URL` y `KV_REST_API_TOKEN` a los tres entornos (`production`, `preview`, `development`).

Si el comando pide una elección interactiva pese a los flags (puede pasar con la región), elegir el plan gratuito y la región más cercana a Europa occidental.

## 3. (Opcional) Traer las variables al entorno local
Solo si se va a probar con `vercel dev` en local:
```bash
vercel env pull .env.local
```
`.env.local` ya está cubierto por `.gitignore`; el plugin no debe commitearlo.

## 4. Desplegar a producción
```bash
vercel --prod
```
No hace falta build command ni output directory: `vercel.json` ya fija `"framework": null` para que Vercel no intente detectar ningún framework y sirva `index.html` y `api/data.js` tal cual.

El comando devuelve la URL de producción al terminar, tipo `https://mi-plan-web-xxxx.vercel.app`.

## 5. Verificación automática (obligatoria antes de dar el despliegue por bueno)
```bash
curl -s -X POST "https://<URL_DE_PRODUCCION>/api/data" \
  -H "Content-Type: application/json" \
  -d '{"code":"test-deploy-check","data":{"ping":true}}'
```
- **Respuesta esperada:** `{"ok":true}`
- **Si devuelve** `{"error":"Falta configurar la base de datos..."}`: el paso 2 no se completó o falta re-desplegar después de conectar Upstash. Repetir `vercel --prod`.
- **Si devuelve un error 404 o de red:** el despliegue del paso 4 no se completó correctamente; revisar `vercel ls` y los logs (`vercel logs <url>`).

## Resultado esperado al terminar
- [ ] Sitio accesible en la URL de producción.
- [ ] `vercel env ls` muestra `KV_REST_API_URL` y `KV_REST_API_TOKEN` en `production`.
- [ ] La comprobación del paso 5 devuelve `{"ok":true}`.

## Qué NO debe hacer el plugin
- No debe pedir, generar ni guardar el "código personal" de sincronización de la app — eso lo escribe la persona usuaria dentro de la propia app, no forma parte del despliegue.
- No debe añadir dependencias npm a `api/data.js` (por ejemplo `@upstash/redis`). La función usa `fetch` nativo contra la API REST de Upstash a propósito, para no depender de una instalación de paquetes en el build.
- No debe modificar `vercel.json` para forzar un framework (Next.js, etc.): este proyecto es HTML + una función serverless, sin build.

## Referencia rápida
| Acción | Comando |
|---|---|
| Enlazar proyecto | `vercel link --yes` |
| Provisionar Upstash | `vercel install upstash --plan free -e production -e preview -e development` |
| Ver variables de entorno | `vercel env ls` |
| Descargar variables a local | `vercel env pull .env.local` |
| Desplegar a producción | `vercel --prod` |
| Ver despliegues | `vercel ls` |
| Ver logs de la función | `vercel logs <url-de-produccion>` |
