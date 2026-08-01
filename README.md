# Mi plan · seguimiento diario

Aplicación web (`index.html` + una función de servidor en `api/data.js`) para:

- **Hoy** — horario del día (agua, comidas, suplementos, entreno) según sea día de oficina, de casa o de finde, con check-in Sí/No.
- **Progreso** — cumplimiento global, racha, gráfico de los últimos 14 días, y **sincronización entre dispositivos**.
- **Medidas** — punto de partida, objetivos a 6 meses (cintura, % grasa, peso) y registro de medidas corporales con gráficas de evolución.

## Cómo funciona el almacenamiento

Hay dos capas:

1. **localStorage** (siempre activo, sin configurar nada): cada dispositivo guarda su copia local en el navegador, en JSON. Es lo que ya tenías.
2. **Sincronización en la nube** (opcional, requiere el paso de Upstash de más abajo): con un **código personal** que tú eliges, la app sube y baja tus datos a una base de datos Redis (Upstash), para que muevas la información entre el móvil, la tablet y el PC.

**Cómo se usa la sincronización, en la pestaña Progreso:**
- Escribe un **código personal** largo y que no sea obvio (no tu nombre ni tu cumpleaños — cualquiera que lo sepa puede leer y escribir en tu registro, no hay contraseña real detrás).
- **⬆️ Subir mis datos**: guarda lo que tienes en ese dispositivo en la nube.
- **⬇️ Bajar mis datos**: trae lo que haya en la nube y sustituye lo del dispositivo actual (pide confirmación).
- Usa el mismo código en cada dispositivo. Sube desde uno, baja en el otro.

> Es una sincronización manual y sencilla ("último que sube, gana"), no en tiempo real ni con fusión automática de cambios. Para uso personal en 2-3 dispositivos es más que suficiente.

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

En este punto el sitio ya funciona con localStorage. Los botones de sincronización darán un error hasta el Paso 2.

### Paso 2 — Conectar la base de datos (Upstash)
1. En el dashboard de tu proyecto en Vercel, ve a la pestaña **Storage**.
2. Pulsa **Create Database** (o **Browse Marketplace**) → elige **Upstash** → **Redis**.
3. Sigue el asistente: crea la base de datos (el plan gratuito es de sobra) y **conéctala a tu proyecto**.
4. Esto añade automáticamente dos variables de entorno a tu proyecto: `KV_REST_API_URL` y `KV_REST_API_TOKEN`. No hace falta que las copies a mano.
5. **Vuelve a desplegar** el proyecto (Vercel → pestaña Deployments → "..." → Redeploy) para que la función `api/data.js` las reciba.

A partir de aquí, los botones "Subir mis datos" / "Bajar mis datos" de la pestaña Progreso funcionan de verdad.

### Comprobar que funciona
Abre la app, pestaña **Progreso**, escribe un código de prueba, pulsa **Subir**. Debería decir "Subido ✅". Si da error, revisa que el paso 2 esté hecho y que hayas vuelto a desplegar después de conectar Upstash.

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
El "código personal" **no es una contraseña con usuario**: es simplemente la clave bajo la que se guardan tus datos en Redis. Cualquiera que conozca ese código exacto podría leer o sobrescribir tu registro. Para uso personal está bien si el código es largo y no es adivinable (evita tu nombre, fecha de nacimiento, etc.). No es el nivel de seguridad de una app con login real — si eso te preocupa, se podría añadir más adelante, pero para este caso de uso no hace falta.

