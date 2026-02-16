# Gestionalo — Scaffold mínimo

Instrucciones rápidas para usar el scaffold mínimo con XAMPP (LAMP en Windows).

- Coloca el proyecto en `htdocs/Gestionalo` (ya está así en este workspace).
- Asegúrate de importar `base_datos/gestionalo.sql` en tu servidor MariaDB/MySQL.
- Ajusta las credenciales en `config.php` si no usas `root` sin contraseña.

Rutas importantes:

- `public/index.php` — página mínima con formulario de ejemplo.
- `api/transactions.php` — API mínima: GET list, POST insert, DELETE remove.
- `api/export.php` — Export CSV o JSON de transacciones por `user_id`.

Ejemplos de uso:

1) Insertar (form HTML o JSON POST):

   - Form: envía desde `public/index.php`.
   - JSON POST:

```bash
curl -X POST http://localhost/Gestionalo/api/transactions.php \
  -H "Content-Type: application/json" \
  -d '{"id_usuario":1,"id_tipo":2,"importe":15.50,"fecha_movimiento":"2026-02-13 12:00:00","concepto":"Café"}'
```

2) Listar transacciones (GET):

```bash
curl http://localhost/Gestionalo/api/transactions.php?user_id=1
```

3) Exportar CSV/JSON:

```bash
curl http://localhost/Gestionalo/api/export.php?user_id=1&format=json
curl http://localhost/Gestionalo/api/export.php?user_id=1&format=csv -o trans.csv
```

Notas:

- Este scaffold es intencionalmente minimal y sin autenticación. Integra autenticación y validaciones adicionales antes de usar en producción.
- No se implementa import (solo export), según la especificación del TFG.
