<?php
/**
 * Recetas KetoWay - instalador de imágenes originales.
 * Sube toda esta carpeta al hosting y abre install-assets.php una sola vez.
 * El script descarga las 59 imágenes originales en ./images y puede crear
 * un ZIP final completamente autónomo.
 */
set_time_limit(0);
ini_set('display_errors', '1');
error_reporting(E_ALL);

$base = __DIR__;
$manifestFile = $base . '/assets-manifest.json';
$imagesDir = $base . '/images';
$finalZip = $base . '/Recetas_KetoWay_hosting_completo.zip';

if (!is_dir($imagesDir)) { mkdir($imagesDir, 0755, true); }
$manifest = json_decode(file_get_contents($manifestFile), true);
if (!is_array($manifest)) { http_response_code(500); die('No se puede leer assets-manifest.json'); }

function fetchBinary($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_USERAGENT => 'Mozilla/5.0 RecetasKetoWayAssetInstaller/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8'],
        ]);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($data === false || $code < 200 || $code >= 300) {
            return [false, null, "HTTP $code $err"];
        }
        return [$data, $type, null];
    }
    $ctx = stream_context_create(['http'=>[
        'timeout'=>90,
        'follow_location'=>1,
        'user_agent'=>'Mozilla/5.0 RecetasKetoWayAssetInstaller/1.0'
    ]]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data === false) return [false, null, 'No hay cURL y file_get_contents remoto ha fallado'];
    return [$data, null, null];
}

$results = [];
$ok = 0; $failed = 0; $skipped = 0;
foreach ($manifest as $item) {
    $filename = basename($item['file']);
    $target = $imagesDir . '/' . $filename;
    if (is_file($target) && filesize($target) > 1000) {
        $skipped++; $ok++;
        $results[] = ['ok'=>true,'file'=>$filename,'status'=>'ya existía'];
        continue;
    }
    [$data,$type,$error] = fetchBinary($item['source']);
    if ($data === false || strlen($data) < 1000) {
        $failed++;
        $results[] = ['ok'=>false,'file'=>$filename,'status'=>$error ?: 'archivo demasiado pequeño'];
        continue;
    }
    // Comprobación simple de cabecera JPEG/PNG/WebP.
    $isJpeg = substr($data,0,3) === "\xFF\xD8\xFF";
    $isPng  = substr($data,0,8) === "\x89PNG\r\n\x1A\n";
    $isWebp = substr($data,0,4) === 'RIFF' && substr($data,8,4) === 'WEBP';
    if (!$isJpeg && !$isPng && !$isWebp) {
        $failed++;
        $results[] = ['ok'=>false,'file'=>$filename,'status'=>'la respuesta no parece una imagen'];
        continue;
    }
    file_put_contents($target, $data);
    $ok++;
    $results[] = ['ok'=>true,'file'=>$filename,'status'=>'descargada · '.number_format(strlen($data)/1024,1).' KB'];
}

$zipCreated = false;
if ($failed === 0 && class_exists('ZipArchive')) {
    @unlink($finalZip);
    $zip = new ZipArchive();
    if ($zip->open($finalZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        $zip->addFile($base.'/index.html', 'index.html');
        $zip->addFile($base.'/README.txt', 'README.txt');
        foreach ($manifest as $item) {
            $filename = basename($item['file']);
            $path = $imagesDir.'/'.$filename;
            if (is_file($path)) $zip->addFile($path, 'images/'.$filename);
        }
        $zip->close();
        $zipCreated = is_file($finalZip);
    }
}
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Instalador · Recetas KetoWay</title>
<style>
body{font:16px/1.5 system-ui,-apple-system,Segoe UI,sans-serif;background:#f6f5f2;color:#202124;margin:0;padding:32px}.box{max-width:960px;margin:auto;background:#fff;border:1px solid #e5e1da;border-radius:18px;padding:28px;box-shadow:0 12px 35px #00000012}h1{margin-top:0}.ok{color:#16854a}.bad{color:#c62828}.summary{font-size:18px;font-weight:700;margin:18px 0;padding:14px;border-radius:12px;background:#f7f7f7}table{width:100%;border-collapse:collapse;font-size:13px}td,th{padding:8px;border-bottom:1px solid #eee;text-align:left}a.btn{display:inline-block;padding:12px 16px;background:#eb3037;color:#fff;text-decoration:none;border-radius:10px;font-weight:700}code{background:#f3f3f3;padding:2px 5px;border-radius:4px}
</style></head><body><div class="box">
<h1>Recetas KetoWay · instalación de imágenes</h1>
<div class="summary"><?= $ok ?> / <?= count($manifest) ?> imágenes correctas · <?= $failed ?> errores · <?= $skipped ?> ya existían</div>
<?php if ($failed===0): ?>
<p class="ok"><strong>Instalación completada.</strong> <code>index.html</code> ya utiliza únicamente imágenes locales.</p>
<p><a class="btn" href="index.html">Abrir Recetas KetoWay</a>
<?php if ($zipCreated): ?> <a class="btn" href="Recetas_KetoWay_hosting_completo.zip">Descargar ZIP final completo</a><?php endif; ?></p>
<p>Por seguridad, elimina <code>install-assets.php</code> del servidor cuando hayas terminado.</p>
<?php else: ?>
<p class="bad"><strong>No se han podido descargar todas las imágenes.</strong> Revisa que tu hosting permita conexiones HTTPS salientes y vuelve a cargar esta página.</p>
<?php endif; ?>
<table><thead><tr><th>Archivo</th><th>Estado</th></tr></thead><tbody>
<?php foreach($results as $r): ?><tr><td><?=htmlspecialchars($r['file'])?></td><td class="<?=$r['ok']?'ok':'bad'?>"><?=htmlspecialchars($r['status'])?></td></tr><?php endforeach; ?>
</tbody></table>
</div></body></html>
