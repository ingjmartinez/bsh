# Migraciones limpias para BSH

Estas migraciones son el baseline nuevo del proyecto real `bsh`, generado desde la estructura funcional de `crm_v3`.

No usar las migraciones viejas de `database/migrations` para inicializar `bsh`.

Cuando se decida aplicar el baseline:

```bash
php artisan migrate --path=database/migrations_bsh
```

Importante: aplicar este baseline sobre una base con tablas existentes puede dejar una mezcla parcial, porque la migracion usa `CREATE TABLE IF NOT EXISTS`. Para una inicializacion limpia, usar una base `bsh` vacia o respaldar/limpiar la base antes de ejecutar.

Para validar sin aplicar cambios:

```bash
php artisan migrate --path=database/migrations_bsh --pretend
```
