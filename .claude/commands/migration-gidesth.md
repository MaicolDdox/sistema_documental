---
name: migration-gidesth
description: Crea migraciones para GIDESTH respetando las tablas existentes, foreign keys, enums y la estructura multi-tenant con training_center_id. Usar al agregar tablas, columnas o índices.
disable-model-invocation: false
---

Crea migraciones para GIDESTH siguiendo los patrones del esquema existente.

## Regla fundamental
**NUNCA modificar migraciones ya ejecutadas.** Siempre nueva migración.

## Tablas clave del proyecto (50+ existentes)

**Catálogo:** `departments`, `cities`, `training_centers`, `training_programs`, `entity_positions`
**Dominio:** `users`, `people`, `research_groups`, `seedlings`, `projects`, `grupo_productos`
**Conocimiento:** `research_lines`, `thematic_areas`, `knowledge_areas`

## Paso 1: Crear la migración
```bash
# Nueva tabla:
php artisan make:migration create_{tabla}_table

# Modificar tabla existente:
php artisan make:migration add_{columna}_to_{tabla}_table
php artisan make:migration modify_{columna}_in_{tabla}_table
```

## Paso 2: Patrones de migración de GIDESTH

### Tabla nueva con training_center (multi-tenant)
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nombre_tabla', function (Blueprint $table) {
            $table->id();

            // ── Multi-tenant: siempre si la tabla está ligada a un centro ──
            $table->foreignId('training_center_id')
                  ->constrained('training_centers')
                  ->cascadeOnDelete();

            // ── Relación con usuarios ──
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // ── Campos de dominio ──
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('valor', 10, 2)->default(0.00);

            // ── Estado con Enum (patrón GIDESTH) ──
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            // ── Índices para columnas de búsqueda frecuente ──
            $table->index(['training_center_id', 'estado']);
            $table->index('nombre');

            $table->timestamps();
            $table->softDeletes(); // si necesita borrado suave
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nombre_tabla');
    }
};
```

### Tabla pivot (relaciones many-to-many)
```php
// Patrón de research_group_users y seedling_members:
Schema::create('tabla_pivot', function (Blueprint $table) {
    $table->foreignId('modelo_a_id')->constrained('tabla_a')->cascadeOnDelete();
    $table->foreignId('modelo_b_id')->constrained('tabla_b')->cascadeOnDelete();

    // Rol dentro de la relación (patrón del proyecto):
    $table->enum('rol', ['lider', 'miembro', 'asesor'])->nullable();

    $table->timestamp('fecha_inicio')->nullable();
    $table->timestamp('fecha_fin')->nullable();

    $table->primary(['modelo_a_id', 'modelo_b_id']);
    $table->timestamps();
});
```

### Agregar columna a tabla existente
```php
public function up(): void
{
    Schema::table('nombre_tabla', function (Blueprint $table) {
        // Agregar después de columna existente:
        $table->string('nueva_columna', 100)
              ->nullable()
              ->after('columna_existente');

        // Si es foreign key:
        $table->foreignId('nueva_relacion_id')
              ->nullable()
              ->after('id')
              ->constrained('tabla_relacionada')
              ->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('nombre_tabla', function (Blueprint $table) {
        // Si es FK, primero dropear la FK:
        $table->dropForeign(['nueva_relacion_id']);
        $table->dropColumn('nueva_relacion_id');

        // Si es columna simple:
        $table->dropColumn('nueva_columna');
    });
}
```

## Tipos de columna para el dominio de GIDESTH

| Dato | Tipo | Ejemplo en GIDESTH |
|------|------|--------------------|
| Estado activo/inactivo | `enum('estado', ['activo','inactivo'])` | users, research_groups |
| Tipo de documento | `enum('tipo_documento', [...])` | people |
| Nombre de persona | `string('primer_nombre', 80)` | people |
| Descripción larga | `text('descripcion')->nullable()` | projects, seedlings |
| Fecha de inicio | `date('fecha_inicio')->nullable()` | proyectos, semilleros |
| Porcentaje | `decimal('porcentaje', 5, 2)` | participaciones |
| Código | `string('codigo', 20)->unique()` | research_groups |
| Año | `year('anio')` | convocatorias |
| Archivo/ruta | `string('ruta_archivo')->nullable()` | documentos |

## Después de crear la migración
```bash
# Revisar que no haya conflictos:
php artisan migrate:status

# Ejecutar:
php artisan migrate

# Si algo falló en desarrollo:
php artisan migrate:rollback --step=1
```

## Notas críticas GIDESTH
- Las tablas ligadas a un rol DEBEN tener `training_center_id` cuando corresponda
- Siempre `->constrained()` para foreign keys — no claves foráneas "sueltas"
- Los enums del dominio deben coincidir con los valores de `app/Enums/`
- El campo `estado` usa siempre los valores `'activo'/'inactivo'` (EstadoEnum)
- Verificar integridad: si agregas FK a tabla con datos, usar `->nullable()` primero
