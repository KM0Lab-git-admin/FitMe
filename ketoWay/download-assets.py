#!/usr/bin/env python3
"""Descarga las 59 imágenes originales antes de subir el sitio al hosting."""
from pathlib import Path
import json, urllib.request, mimetypes, sys
base=Path(__file__).resolve().parent
images=base/'images'; images.mkdir(exist_ok=True)
manifest=json.loads((base/'assets-manifest.json').read_text(encoding='utf-8'))
ok=0
for i,item in enumerate(manifest,1):
    target=images/item['file']
    if target.exists() and target.stat().st_size>1000:
        print(f'[{i:02}/{len(manifest)}] OK ya existe {target.name}'); ok+=1; continue
    req=urllib.request.Request(item['source'],headers={'User-Agent':'Mozilla/5.0 RecetasKetoWayAssetInstaller/1.0'})
    try:
        with urllib.request.urlopen(req,timeout=90) as r:
            data=r.read()
        if len(data)<1000: raise RuntimeError('respuesta demasiado pequeña')
        target.write_bytes(data); ok+=1
        print(f'[{i:02}/{len(manifest)}] OK {target.name} ({len(data)/1024:.1f} KB)')
    except Exception as e:
        print(f'[{i:02}/{len(manifest)}] ERROR {target.name}: {e}',file=sys.stderr)
print(f'\nResultado: {ok}/{len(manifest)} imágenes.')
sys.exit(0 if ok==len(manifest) else 1)
