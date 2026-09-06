RECETAS KETOWAY · PAQUETE PARA HOSTING
======================================

Objetivo
--------
index.html está preparado para utilizar imágenes LOCALES mediante rutas ./images/<archivo>.
No depende del servidor remoto para mostrar las fotos una vez instaladas.

Instalación recomendada en un hosting con PHP
---------------------------------------------
1. Sube todos los archivos y carpetas de este paquete a una carpeta de tu hosting.
2. Abre en el navegador: https://TU-DOMINIO/RUTA/install-assets.php
3. El instalador descargará las 59 imágenes originales a la carpeta images/.
4. Si el hosting tiene ZipArchive, también generará Recetas_KetoWay_hosting_completo.zip.
5. Comprueba index.html.
6. Elimina install-assets.php cuando todo funcione.

Instalación alternativa desde un ordenador con Python
------------------------------------------------------
1. Ejecuta: python download-assets.py
2. Comprueba que indique 59/59 imágenes.
3. Sube index.html + la carpeta images/ a tu hosting.

Archivos
--------
- index.html: versión final con rutas locales.
- images/: destino de las 59 imágenes.
- assets-manifest.json: lista exacta de imágenes originales y sus URLs de origen.
- install-assets.php: instalador de un solo uso en hosting PHP.
- download-assets.py: alternativa para descargar los recursos localmente.
- index-remote-backup.html: copia de seguridad de la versión que usa las URLs remotas.

Verificación
------------
Tras la descarga deben existir 59 imágenes dentro de images/.
index.html contiene 59 referencias images/... y ninguna referencia al host remoto de imágenes.
