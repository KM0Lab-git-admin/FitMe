# Mi plan · seguimiento diario

Aplicación web (`index.html` + una función de servidor en `api/data.js`) para:

- **Hoy** — horario del día (agua, comidas, suplementos, entreno) según sea día de oficina, de casa o de finde, con check-in Sí/No.
- **Progreso** — cumplimiento global, racha, gráfico de los últimos 14 días, y **sincronización entre dispositivos**.
- **Medidas** — punto de partida, objetivos a 6 meses (cintura, % grasa, peso) y registro de medidas corporales con gráficas de evolución.

## Cómo funciona el almacenamiento

Hay dos capas:

1. **localStorage** (siempre): copia rápida en el navegador.
2. **Servidor (Upstash Redis)**: cada clic (hábito, medida, etc.) se guarda solo en la nube. Al abrir la app en otro dispositivo, se cargan los datos del servidor. No hay pantalla de sincronización ni códigos que escribir.

> Es "último que escribe, gana". Para uso personal en 2–3 dispositivos es suficiente.

## Desplegar en Vercel

Esta sección es la explicación para ti. Si vas a usar un plugin o automatismo (por ejemplo el Vercel Plugin de Cursor) para hacerlo por ti, dale directamente **[`DEPLOY.md`](./DEPLOY.md)** — es el mismo proceso pero escrito como una receta de comandos exactos, pensada para que la ejecute un agente sin ambigüedad.

### Paso 1 — Publicar el sitio
1. Crea un repositorio en GitHub y sube esta carpeta:
   ```bash
   git init
   git add .
   git commit -m "Primera versión con backend"
   git branch -M main
   git remote add origin https://github.com/TU-USUARIO/mi-plan-web.git
   git push -u origin main
   ```
2. En https://vercel.com/new, **Import Git Repository** y selecciona el repo.
3. Framework Preset: `Other` (no toques nada más). Pulsa **Deploy**.

En este punto el sitio ya funciona con localStorage. La sincronización en la nube dará error hasta el Paso 2.

### Paso 2 — Conectar la base de datos (Upstash)
1. En el dashboard de tu proyecto en Vercel, ve a la pestaña **Storage**.
2. Pulsa **Create Database** (o **Browse Marketplace**) → elige **Upstash** → **Redis**.
3. Sigue el asistente: crea la base de datos (el plan gratuito es de sobra) y **conéctala a tu proyecto**.
4. Esto añade automáticamente dos variables de entorno a tu proyecto: `KV_REST_API_URL` y `KV_REST_API_TOKEN`. No hace falta que las copies a mano.
5. **Vuelve a desplegar** el proyecto (Vercel → pestaña Deployments → "..." → Redeploy) para que la función `api/data.js` las reciba.

A partir de aquí, cada cambio en la app se guarda solo en Upstash (y se carga al abrir en otro dispositivo).

### Comprobar que funciona
Marca un hábito en un dispositivo, abre la app en otro y debería verse. Si no, revisa que el paso 2 esté hecho y que hayas vuelto a desplegar después de conectar Upstash.

## Estructura del repositorio
```
mi-plan-web/
├── index.html      → toda la app (interfaz + lógica del cliente)
├── api/
│   └── data.js     → función de servidor: lee/escribe en Upstash Redis
├── vercel.json      → config explícita (sin framework, sin build)
├── DEPLOY.md        → runbook de despliegue para agentes/plugins
├── README.md
└── .gitignore
```

## Actualizar la app más adelante
1. Sustituye los archivos que cambien.
2. `git add . && git commit -m "Describe el cambio" && git push`
3. Vercel despliega la nueva versión automáticamente.

## Usarla desde el móvil
Abre la URL de Vercel en Safari (iPhone) o Chrome (Android) y usa "Añadir a pantalla de inicio" para que se comporte como una app.

## Seguridad — léelo antes de usarlo
La clave de Redis está fijada en el código de la app (uso personal de un solo “espacio” de datos). Quien conozca la URL de la API y esa clave podría leer o sobrescribir el registro. No es un login real; para este caso de uso personal es suficiente.

