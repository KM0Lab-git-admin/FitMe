# Mi plan · seguimiento diario

Aplicación web de una sola página (`index.html`, sin build ni dependencias) para:

- **Hoy** — horario del día (agua, comidas, suplementos, entreno) según sea día de oficina, de casa o de finde, con check-in Sí/No.
- **Progreso** — cumplimiento global, racha, gráfico de los últimos 14 días.
- **Medidas** — punto de partida, objetivos a 6 meses (cintura, % grasa, peso) y registro de medidas corporales con gráficas de evolución.

## Cómo funciona el almacenamiento

Los datos se guardan en el **localStorage** del navegador, en formato JSON. No hay base de datos ni backend: todo vive en el dispositivo donde abras la app. Usa el botón "Exportar copia" (pestaña Progreso) para descargar un `.json` de respaldo de vez en cuando.

> Si más adelante quieres que los datos se sincronicen entre varios dispositivos (móvil, tablet, PC) y sobrevivan a limpiar el navegador, el siguiente paso es añadir una base de datos (por ejemplo Upstash Redis vía Vercel Marketplace) y una función `/api` que lea y escriba ahí. Esta versión no lo incluye todavía.

## Desplegar en Vercel

Dos formas de hacerlo. La primera es más rápida; la segunda te da despliegue automático cada vez que subes un cambio.

### Opción A — Arrastrar y soltar (sin Git, la más rápida)
1. Entra en **https://vercel.com/new**.
2. Arrastra esta carpeta entera (`mi-plan-web`) al recuadro de subida.
3. Pulsa **Deploy**. En unos segundos tendrás una URL tipo `https://mi-plan-web.vercel.app`.
4. Cada vez que quieras actualizar la app, repite el arrastre (o usa la Opción B para que sea automático).

### Opción B — Con GitHub (recomendada: despliegue automático)
1. **Crea un repositorio en GitHub:** entra en https://github.com/new, ponle un nombre (p. ej. `mi-plan-web`), y créalo **vacío** (sin README, sin licencia).
2. **Sube estos archivos desde tu ordenador**, abriendo una terminal dentro de esta carpeta y ejecutando:
   ```bash
   git init
   git add .
   git commit -m "Primera versión de la app"
   git branch -M main
   git remote add origin https://github.com/TU-USUARIO/mi-plan-web.git
   git push -u origin main
   ```
   (Cambia `TU-USUARIO` por tu usuario de GitHub, y el nombre del repo si usaste otro.)
3. **Conecta el repo con Vercel:**
   - Entra en https://vercel.com/new
   - Elige **Import Git Repository**
   - Autoriza a Vercel a acceder a tu cuenta de GitHub si te lo pide
   - Selecciona el repositorio `mi-plan-web`
4. **Configuración del proyecto:** Vercel detecta que es un sitio estático (sin framework). No hace falta tocar nada — deja "Framework Preset" en `Other` y los campos de build vacíos.
5. Pulsa **Deploy**. Tendrás tu URL en menos de un minuto.
6. **A partir de aquí, cada vez que hagas `git push` a `main`, Vercel vuelve a desplegar solo.** Eso es la "sincronización" de la que hablabas: el repo en GitHub es la fuente de verdad, y Vercel simplemente publica lo último que haya en `main`.

## Actualizar la app más adelante
Cuando quieras un cambio (nuevo horario, nueva pestaña, etc.):
1. Sustituye `index.html` por la versión nueva.
2. `git add . && git commit -m "Describe el cambio" && git push`
3. Vercel despliega la nueva versión automáticamente (Opción B), o repite el arrastre (Opción A).

## Usarla desde el móvil
Abre la URL de Vercel en Safari (iPhone) o Chrome (Android) y usa "Añadir a pantalla de inicio" para que se comporte como una app.
